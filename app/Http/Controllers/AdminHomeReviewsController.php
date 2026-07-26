<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HomeReviewsRequest;
use App\Models\HomeReviewItem;
use App\Models\HomeReviewsConfig;
use App\Services\HomeReviews\HomeReviewsStorageService;
use App\Services\HtmlCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminHomeReviewsController extends Controller
{
    public function __construct(
        private readonly HomeReviewsStorageService $storage,
    ) {}

    public function view(Request $request): View
    {
        $locale = $request->get('locale', app()->getLocale() ?: 'vi');
        $islandName = island_name();

        $config = HomeReviewsConfig::query()
            ->where('locale', $locale)
            ->with('items')
            ->first();

        return view('admin.homeReviews.view', compact('config', 'locale', 'islandName'));
    }

    public function update(HomeReviewsRequest $request): JsonResponse
    {
        $locale = $request->input('locale', 'vi');

        try {
            DB::beginTransaction();

            $config = HomeReviewsConfig::query()->firstOrCreate(
                ['locale' => $locale],
                [
                    'kicker' => 'Khách hàng nói gì',
                    'title' => 'Hành trình được tin chọn',
                    'is_active' => true,
                ],
            );

            $scoreStats = [];
            $statValues = $request->input('score_stat_value', []);
            $statLabels = $request->input('score_stat_label', []);
            for ($i = 0; $i < 3; $i++) {
                $value = trim((string) ($statValues[$i] ?? $statValues[(string) $i] ?? ''));
                $label = trim((string) ($statLabels[$i] ?? $statLabels[(string) $i] ?? ''));
                if ($value !== '' || $label !== '') {
                    $scoreStats[] = ['value' => $value, 'label' => $label];
                }
            }

            $partnersRaw = trim((string) $request->input('partners_text', ''));
            $partners = array_values(array_filter(array_map(
                'trim',
                preg_split('/\r\n|\r|\n/', $partnersRaw) ?: [],
            )));

            $config->fill([
                'kicker' => $request->input('kicker'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'score_value' => $request->input('score_value', 4.9),
                'score_stats' => $scoreStats ?: null,
                'partners_label' => $request->input('partners_label'),
                'partners' => $partners ?: null,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $config->save();

            $this->syncItems($config, $request);

            DB::commit();

            app(HtmlCacheService::class)->clearAll();

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu phần Khách hàng nói gì.',
                'redirect_url' => route('admin.homeReviews.view', ['locale' => $locale, 'message' => 'success']),
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

    public function deleteItem(Request $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $item = HomeReviewItem::query()->find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bình luận.'], 404);
        }

        $this->storage->deleteAvatar($item->avatar_path);
        $item->delete();

        app(HtmlCacheService::class)->clearAll();

        return response()->json(['success' => true, 'message' => 'Đã xóa bình luận.']);
    }

    private function syncItems(HomeReviewsConfig $config, HomeReviewsRequest $request): void
    {
        $existing = $request->input('reviews_existing', []);
        $quotes = $request->input('reviews_quote', []);
        $names = $request->input('reviews_name', []);
        $metas = $request->input('reviews_meta', []);
        $tags = $request->input('reviews_tag', []);
        $ratings = $request->input('reviews_rating', []);
        $orders = $request->input('reviews_sort', []);
        $avatarUrls = $request->input('reviews_avatar_url', []);
        $avatarFiles = $request->file('reviews_avatar', []);

        foreach ($config->items as $item) {
            $id = (int) $item->id;
            $existingIds = array_map('intval', (array) $existing);
            if (!in_array($id, $existingIds, true)) {
                $this->storage->deleteAvatar($item->avatar_path);
                $item->delete();
                continue;
            }

            $quote = trim((string) ($quotes[(string) $id] ?? $quotes[$id] ?? ''));
            $name = trim((string) ($names[(string) $id] ?? $names[$id] ?? ''));
            if ($quote === '' || $name === '') {
                continue;
            }

            $item->quote_text = $quote;
            $item->customer_name = $name;
            $item->customer_meta = trim((string) ($metas[(string) $id] ?? $metas[$id] ?? '')) ?: null;
            $item->tag = trim((string) ($tags[(string) $id] ?? $tags[$id] ?? '')) ?: null;
            $item->rating = max(1, min(5, (int) ($ratings[(string) $id] ?? $ratings[$id] ?? 5)));
            $item->sort_order = (int) ($orders[(string) $id] ?? $orders[$id] ?? $item->sort_order);
            $item->is_active = true;

            $url = trim((string) ($avatarUrls[(string) $id] ?? $avatarUrls[$id] ?? ''));
            if (isset($avatarFiles[$id]) && $avatarFiles[$id]?->isValid()) {
                $this->storage->deleteAvatar($item->avatar_path);
                $item->avatar_path = $this->storage->uploadAvatar($avatarFiles[$id], Str::slug($name) ?: 'avatar');
            } elseif ($url !== '') {
                if ($item->avatar_path && !str_starts_with((string) $item->avatar_path, 'http')) {
                    $this->storage->deleteAvatar($item->avatar_path);
                }
                $item->avatar_path = $url;
            }

            $item->save();
        }

        $newQuotes = $request->input('reviews_new_quote', []);
        $newNames = $request->input('reviews_new_name', []);
        $newMetas = $request->input('reviews_new_meta', []);
        $newTags = $request->input('reviews_new_tag', []);
        $newRatings = $request->input('reviews_new_rating', []);
        $newAvatarUrls = $request->input('reviews_new_avatar_url', []);
        $newAvatarFiles = $request->file('reviews_new_avatar', []);

        if (!is_array($newQuotes)) {
            return;
        }

        $startOrder = ((int) $config->items()->max('sort_order')) + 1;
        $orderOffset = 0;

        foreach ($newQuotes as $index => $quoteRaw) {
            $quote = trim((string) $quoteRaw);
            $name = trim((string) ($newNames[$index] ?? ''));
            if ($quote === '' || $name === '') {
                continue;
            }

            $avatarPath = trim((string) ($newAvatarUrls[$index] ?? ''));
            $file = $newAvatarFiles[$index] ?? null;
            if ($file && $file->isValid()) {
                $avatarPath = $this->storage->uploadAvatar($file, Str::slug($name) ?: 'avatar');
            }

            HomeReviewItem::query()->create([
                'reviews_config_id' => $config->id,
                'quote_text' => $quote,
                'customer_name' => $name,
                'customer_meta' => trim((string) ($newMetas[$index] ?? '')) ?: null,
                'tag' => trim((string) ($newTags[$index] ?? '')) ?: null,
                'rating' => max(1, min(5, (int) ($newRatings[$index] ?? 5))),
                'avatar_path' => $avatarPath ?: null,
                'sort_order' => $startOrder + $orderOffset,
                'is_active' => true,
            ]);

            $orderOffset++;
        }
    }
}
