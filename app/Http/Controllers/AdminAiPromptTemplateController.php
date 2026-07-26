<?php

namespace App\Http\Controllers;

use App\Models\AiPromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminAiPromptTemplateController extends Controller
{
    public function index(Request $request)
    {
        $scope = (string) $request->get('scope', 'translation');

        // Template dịch dùng chung toàn hệ thống; không lọc theo locale / loại trang.
        $rows = AiPromptTemplate::query()
            ->where('scope', $scope)
            ->where('is_active', 1)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'template_content', 'part_before', 'part_after', 'default_model', 'is_default']);

        $rows = $rows->map(function ($row) {
            if (empty($row->template_content)) {
                $row->template_content = trim((string) ($row->part_before ?? ''))
                    . "\n\n[source]\n\n"
                    . trim((string) ($row->part_after ?? ''));
            }
            return $row;
        })->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:160'],
            'scope' => ['nullable', 'string', 'max:40'],
            'seo_type' => ['nullable', 'string', 'max:80'],
            'locale' => ['nullable', 'string', 'max:20'],
            'template_content' => ['nullable', 'string'],
            'part_before' => ['nullable', 'string'],
            'part_after' => ['nullable', 'string'],
            'default_model' => ['nullable', 'string', 'max:160'],
            'is_default' => ['nullable', 'boolean'],
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $id = (int) $request->input('id', 0);
        $scope = (string) $request->input('scope', 'translation');
        $data = [
            'name' => (string) $request->input('name'),
            'scope' => $scope,
            'seo_type' => null,
            'locale' => null,
            'template_content' => (string) $request->input('template_content', ''),
            'part_before' => (string) $request->input('part_before', ''),
            'part_after' => (string) $request->input('part_after', ''),
            'default_model' => $request->filled('default_model') ? (string) $request->input('default_model') : null,
            'is_default' => (bool) $request->input('is_default', false),
            'is_active' => true,
        ];

        if ($id > 0) {
            $model = AiPromptTemplate::find($id);
            if (!$model) return response()->json(['success' => false, 'message' => 'Template not found'], 404);
            $model->fill($data);
            $model->save();
        } else {
            $data['created_by'] = Auth::id();
            $model = AiPromptTemplate::create($data);
        }

        if ($model->is_default) {
            AiPromptTemplate::query()
                ->where('scope', $scope)
                ->where('id', '!=', $model->id)
                ->update(['is_default' => false]);
        }

        return response()->json(['success' => true, 'data' => $model]);
    }

    public function delete(Request $request)
    {
        $id = (int) $request->input('id', 0);
        if ($id <= 0) return response()->json(['success' => false, 'message' => 'Invalid template id'], 422);
        $model = AiPromptTemplate::find($id);
        if (!$model) return response()->json(['success' => false, 'message' => 'Template not found'], 404);
        $model->is_active = false;
        $model->save();
        return response()->json(['success' => true]);
    }
}
