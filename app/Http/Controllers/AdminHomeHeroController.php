<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HomeHeroRequest;
use App\Models\HomeHeroBackground;
use App\Models\HomeHeroConfig;
use App\Models\HomeHeroRouteSlot;
use App\Models\ShipLocation;
use App\Services\HomeHero\HomeHeroStorageService;
use App\Services\HtmlCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHomeHeroController extends Controller
{
    public function __construct(
        private readonly HomeHeroStorageService $storage,
    ) {}

    public function view(Request $request): View
    {
        $locale = $request->get('locale', app()->getLocale() ?: 'vi');
        $config = HomeHeroConfig::query()
            ->where('locale', $locale)
            ->with(['backgrounds', 'routeSlots'])
            ->first();

        $shipLocations = ShipLocation::query()
            ->with('seo')
            ->orderBy('name')
            ->get(['id', 'name', 'display_name', 'seo_id']);

        return view('admin.homeHero.view', compact('config', 'locale', 'shipLocations'));
    }

    public function update(HomeHeroRequest $request): JsonResponse
    {
        $locale = $request->input('locale', 'vi');

        try {
            DB::beginTransaction();

            $config = HomeHeroConfig::query()->firstOrCreate(
                ['locale' => $locale],
                ['title' => 'Khám phá Côn Đảo', 'is_active' => true]
            );

            $config->fill([
                'title' => $request->input('title'),
                'title_accent' => $request->input('title_accent'),
                'tagline' => $request->input('tagline'),
                'btn_primary_label' => $request->input('btn_primary_label'),
                'btn_primary_url' => $request->input('btn_primary_url'),
                'btn_primary_enabled' => $request->boolean('btn_primary_enabled'),
                'btn_secondary_label' => $request->input('btn_secondary_label'),
                'btn_secondary_url' => $request->input('btn_secondary_url'),
                'btn_secondary_enabled' => $request->boolean('btn_secondary_enabled'),
                'is_active' => $request->boolean('is_active', true),
            ]);
            $config->save();

            $this->syncBackgrounds($config, $request);
            $this->syncRouteSlots($config, $request);

            DB::commit();

            app(HtmlCacheService::class)->clearAll();

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu cấu hình Hero trang chủ.',
                'redirect_url' => route('admin.homeHero.view', ['locale' => $locale, 'message' => 'success']),
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

    public function deleteBackground(Request $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $background = HomeHeroBackground::query()->find($id);

        if (!$background) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy ảnh.'], 404);
        }

        $this->storage->deleteBackground($background->gcs_path);
        $background->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa ảnh nền.']);
    }

    private function syncBackgrounds(HomeHeroConfig $config, HomeHeroRequest $request): void
    {
        $existing = $request->input('backgrounds_existing', []);
        $alts = $request->input('backgrounds_alt', []);
        $orders = $request->input('backgrounds_sort', []);

        foreach ($config->backgrounds as $background) {
            $id = (string) $background->id;
            if (!in_array($id, $existing, true)) {
                $this->storage->deleteBackground($background->gcs_path);
                $background->delete();
                continue;
            }

            $background->alt_text = $alts[$id] ?? $background->alt_text;
            $background->sort_order = (int) ($orders[$id] ?? $background->sort_order);
            $background->is_active = true;
            $background->save();
        }

        if (!$request->hasFile('backgrounds_new')) {
            return;
        }

        $startOrder = (int) $config->backgrounds()->max('sort_order') + 1;
        foreach ($request->file('backgrounds_new') as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $uploaded = $this->storage->uploadBackground($file, 'hero-' . $config->locale);
            HomeHeroBackground::query()->create([
                'hero_config_id' => $config->id,
                'gcs_path' => $uploaded['gcs_path'],
                'public_url' => $uploaded['public_url'],
                'alt_text' => $request->input('backgrounds_new_alt.' . $index),
                'sort_order' => $startOrder + $index,
                'is_active' => true,
            ]);
        }
    }

    private function syncRouteSlots(HomeHeroConfig $config, HomeHeroRequest $request): void
    {
        $config->routeSlots()->delete();

        $slots = $request->input('routes', []);
        foreach ($slots as $index => $slot) {
            if (empty($slot['ship_location_id'])) {
                continue;
            }

            HomeHeroRouteSlot::query()->create([
                'hero_config_id' => $config->id,
                'ship_location_id' => (int) $slot['ship_location_id'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
