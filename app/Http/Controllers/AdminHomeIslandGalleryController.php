<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HomeIslandGalleryRequest;
use App\Models\HomeIslandGalleryConfig;
use App\Models\HomeIslandGalleryPhoto;
use App\Services\HomeIslandGallery\HomeIslandGalleryStorageService;
use App\Services\HtmlCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHomeIslandGalleryController extends Controller
{
    public function __construct(
        private readonly HomeIslandGalleryStorageService $storage,
    ) {}

    public function view(Request $request): View
    {
        $locale = $request->get('locale', app()->getLocale() ?: 'vi');
        $islandName = island_name();

        $config = HomeIslandGalleryConfig::query()
            ->where('locale', $locale)
            ->with('photos')
            ->first();

        return view('admin.homeIslandGallery.view', compact('config', 'locale', 'islandName'));
    }

    public function update(HomeIslandGalleryRequest $request): JsonResponse
    {
        $locale = $request->input('locale', 'vi');

        try {
            DB::beginTransaction();

            $config = HomeIslandGalleryConfig::query()->firstOrCreate(
                ['locale' => $locale],
                [
                    'eyebrow' => 'Trải nghiệm đảo',
                    'title' => ':name qua từng khoảnh khắc đẹp',
                    'is_active' => true,
                ],
            );

            $config->fill([
                'eyebrow' => $request->input('eyebrow'),
                'title' => $request->input('title'),
                'lead' => $request->input('lead'),
                'meta_caption' => $request->input('meta_caption'),
                'is_active' => $request->boolean('is_active', true),
            ]);
            $config->save();

            $this->syncPhotos($config, $request);

            DB::commit();

            app(HtmlCacheService::class)->clearAll();

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu gallery Trải nghiệm đảo.',
                'redirect_url' => route('admin.homeIslandGallery.view', ['locale' => $locale, 'message' => 'success']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
            ], 500);
        }
    }

    public function deletePhoto(Request $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $photo = HomeIslandGalleryPhoto::query()->find($id);

        if (!$photo) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy ảnh.'], 404);
        }

        $this->storage->deletePhoto($photo->gcs_path);
        $photo->delete();

        app(HtmlCacheService::class)->clearAll();

        return response()->json(['success' => true, 'message' => 'Đã xóa ảnh.']);
    }

    private function syncPhotos(HomeIslandGalleryConfig $config, HomeIslandGalleryRequest $request): void
    {
        $existing = $request->input('photos_existing', []);
        $alts = $request->input('photos_alt', []);
        $titles = $request->input('photos_title', []);
        $tags = $request->input('photos_tag', []);
        $positions = $request->input('photos_pos', []);
        $orders = $request->input('photos_sort', []);

        foreach ($config->photos as $photo) {
            $id = (int) $photo->id;
            $existingIds = array_map('intval', (array) $existing);
            if (!in_array($id, $existingIds, true)) {
                $this->storage->deletePhoto($photo->gcs_path);
                $photo->delete();
                continue;
            }

            $photo->alt_text = trim((string) ($alts[(string) $id] ?? $alts[$id] ?? $photo->alt_text));
            $photo->title = trim((string) ($titles[(string) $id] ?? $titles[$id] ?? $photo->title)) ?: null;
            $photo->tag = trim((string) ($tags[(string) $id] ?? $tags[$id] ?? $photo->tag)) ?: null;
            $photo->object_position = trim((string) ($positions[(string) $id] ?? $positions[$id] ?? $photo->object_position)) ?: 'center center';
            $photo->sort_order = (int) ($orders[(string) $id] ?? $orders[$id] ?? $photo->sort_order);
            $photo->is_active = true;
            $photo->save();
        }

        if (!$request->hasFile('photos_new')) {
            return;
        }

        $startOrder = ((int) $config->photos()->max('sort_order')) + 1;
        $newAlts = $request->input('photos_new_alt', []);

        foreach ($request->file('photos_new') as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $alt = trim((string) ($newAlts[$index] ?? ''));
            if ($alt === '') {
                continue;
            }

            $uploaded = $this->storage->uploadPhoto($file, 'gallery');
            HomeIslandGalleryPhoto::query()->create([
                'gallery_config_id' => $config->id,
                'gcs_path' => $uploaded['original'],
                'alt_text' => $alt,
                'sort_order' => $startOrder + $index,
                'is_active' => true,
            ]);
        }
    }
}

