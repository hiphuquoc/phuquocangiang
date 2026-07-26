<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HomeFaqRequest;
use App\Models\HomeFaqConfig;
use App\Models\HomeFaqItem;
use App\Services\HtmlCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHomeFaqController extends Controller
{
    public function view(Request $request): View
    {
        $locale = $request->get('locale', app()->getLocale() ?: 'vi');
        $islandName = island_name();

        $config = HomeFaqConfig::query()
            ->where('locale', $locale)
            ->with('items')
            ->first();

        return view('admin.homeFaq.view', compact('config', 'locale', 'islandName'));
    }

    public function update(HomeFaqRequest $request): JsonResponse
    {
        $locale = $request->input('locale', 'vi');

        try {
            DB::beginTransaction();

            $config = HomeFaqConfig::query()->firstOrCreate(
                ['locale' => $locale],
                [
                    'kicker' => 'Hỏi đáp',
                    'title' => 'Câu hỏi thường gặp',
                    'is_active' => true,
                ],
            );

            $config->fill([
                'kicker' => $request->input('kicker'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'help_title' => $request->input('help_title'),
                'help_body' => $request->input('help_body'),
                'is_active' => $request->boolean('is_active', true),
            ]);
            $config->save();

            $this->syncItems($config, $request);

            DB::commit();

            app(HtmlCacheService::class)->clearAll();

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu phần Câu hỏi thường gặp.',
                'redirect_url' => route('admin.homeFaq.view', ['locale' => $locale, 'message' => 'success']),
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
        $item = HomeFaqItem::query()->find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy câu hỏi.'], 404);
        }

        $item->delete();

        app(HtmlCacheService::class)->clearAll();

        return response()->json(['success' => true, 'message' => 'Đã xóa câu hỏi.']);
    }

    private function syncItems(HomeFaqConfig $config, HomeFaqRequest $request): void
    {
        $existing = $request->input('faqs_existing', []);
        $questions = $request->input('faqs_question', []);
        $answers = $request->input('faqs_answer', []);
        $orders = $request->input('faqs_sort', []);
        $openIds = array_map('intval', (array) $request->input('faqs_open', []));

        foreach ($config->items as $item) {
            $id = (int) $item->id;
            $existingIds = array_map('intval', (array) $existing);
            if (!in_array($id, $existingIds, true)) {
                $item->delete();
                continue;
            }

            $question = trim((string) ($questions[(string) $id] ?? $questions[$id] ?? ''));
            $answer = trim((string) ($answers[(string) $id] ?? $answers[$id] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }

            $item->question = $question;
            $item->answer_html = $answer;
            $item->sort_order = (int) ($orders[(string) $id] ?? $orders[$id] ?? $item->sort_order);
            $item->is_open_default = in_array($id, $openIds, true);
            $item->is_active = true;
            $item->save();
        }

        $newQuestions = $request->input('faqs_new_question', []);
        $newAnswers = $request->input('faqs_new_answer', []);
        $newOpen = array_map('intval', (array) $request->input('faqs_new_open', []));

        if (!is_array($newQuestions)) {
            return;
        }

        $startOrder = ((int) $config->items()->max('sort_order')) + 1;
        $orderOffset = 0;

        foreach ($newQuestions as $index => $questionRaw) {
            $question = trim((string) $questionRaw);
            $answer = trim((string) ($newAnswers[$index] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }

            HomeFaqItem::query()->create([
                'faq_config_id' => $config->id,
                'question' => $question,
                'answer_html' => $answer,
                'sort_order' => $startOrder + $orderOffset,
                'is_open_default' => in_array($index, $newOpen, true),
                'is_active' => true,
            ]);

            $orderOffset++;
        }
    }
}
