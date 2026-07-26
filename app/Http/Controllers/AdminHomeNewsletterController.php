<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HomeNewsletterRequest;
use App\Models\HomeNewsletterConfig;
use App\Services\HtmlCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminHomeNewsletterController extends Controller
{
    public function view(Request $request): View
    {
        $locale = $request->get('locale', app()->getLocale() ?: 'vi');
        $islandName = island_name();

        $config = HomeNewsletterConfig::query()
            ->where('locale', $locale)
            ->first();

        return view('admin.homeNewsletter.view', compact('config', 'locale', 'islandName'));
    }

    public function update(HomeNewsletterRequest $request): JsonResponse
    {
        $locale = $request->input('locale', 'vi');

        try {
            $config = HomeNewsletterConfig::query()->firstOrCreate(
                ['locale' => $locale],
                ['is_active' => true],
            );

            $config->fill([
                'stamp_text' => $request->input('stamp_text'),
                'stamp_year' => $request->input('stamp_year'),
                'kicker' => $request->input('kicker'),
                'title' => $request->input('title'),
                'lead' => $request->input('lead'),
                'field_label' => $request->input('field_label'),
                'email_placeholder' => $request->input('email_placeholder'),
                'submit_text' => $request->input('submit_text'),
                'note' => $request->input('note'),
                'sign_text' => $request->input('sign_text'),
                'is_active' => $request->boolean('is_active', true),
            ]);
            $config->save();

            app(HtmlCacheService::class)->clearAll();

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu nội dung Newsletter.',
                'redirect_url' => route('admin.homeNewsletter.view', ['locale' => $locale, 'message' => 'success']),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
            ], 500);
        }
    }
}
