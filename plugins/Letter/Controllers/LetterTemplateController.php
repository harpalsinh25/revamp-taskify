<?php

namespace Plugins\Letter\Controllers;

use Exception;
use App\Models\Workspace;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Plugins\Letter\Helper\LetterHelper;
use Plugins\Letter\Models\LetterTemplate;
use Illuminate\Validation\ValidationException;

class LetterTemplateController extends Controller
{
    protected $workspace;
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $templates = LetterTemplate::where('workspace_id', $this->workspace->id)->count();
        $categories = LetterHelper::getLetterCategories();

        return view('letters::templates.index', [
            'templates' => $templates,
            'categories' => $categories
        ]);
    }

    public function create()
    {
        $categories = LetterHelper::getLetterCategories();
        $variables = LetterHelper::getAvailableVariables();

        return view('letters::templates.create', [
            'categories' => $categories,
            'variables' => $variables
        ]);
    }

    public function store(Request $request)
    {
        try {
            $isApi = $request->boolean('isApi', false);

            $request->merge([
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            $formFields = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'content' => 'required|string',
                'is_active' => 'required|in:0,1',
            ]);

            $existingTemplate = LetterTemplate::where('workspace_id', $this->workspace->id)
                ->where('name', $formFields['name'])
                ->first();

            if ($existingTemplate) {
                if ($isApi) {
                    return formatApiResponse(true, 'Template with this name already exists');
                }
                return response()->json([
                    'error' => true,
                    'message' => 'Template with this name already exists.'
                ]);
            }

            if (empty($formFields['content']) && !empty($formFields['category'])) {
                $formFields['content'] = LetterHelper::getSampleContent($formFields['category']);
            }

            $formFields['workspace_id'] = $this->workspace->id;
            $formFields['created_by'] = $this->user->id;

            $template = LetterTemplate::create($formFields);

            if ($isApi) {
                return formatApiResponse(false, 'Letter template created successfully.', [
                    'id' => $template->id,
                    'data' => $this->formatTemplateData($template)
                ]);
            }

            return response()->json([
                'error' => false,
                'message' => 'Letter template created successfully.',
                'id' => $template->id,
                'redirect_url' => route('letter-templates.index')
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            return $this->handleException($e, $isApi, 'create');
        }
    }

    public function list(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'DESC');
        $categories = $request->get('categories', []);
        $is_active = $request->get('is_active');
        $limit = $request->get('limit', 10);

        $query = LetterTemplate::select(
            'letter_templates.*',
            'users.first_name',
            'users.last_name'
        )
            ->leftJoin('users', 'letter_templates.created_by', '=', 'users.id')
            ->where('letter_templates.workspace_id', $this->workspace->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('letter_templates.name', 'like', '%' . $search . '%')
                    ->orWhere('letter_templates.category', 'like', '%' . $search . '%')
                    ->orWhere('letter_templates.description', 'like', '%' . $search . '%');
            });
        }

        if (!empty($categories)) {
            $query->whereIn('letter_templates.category', $categories);
        }

        if ($is_active !== null) {
            $query->where('letter_templates.is_active', $is_active);
        }

        $canEdit = checkPermission('edit_letter_templates');
        $canDelete = checkPermission('delete_letter_templates');
        $canDuplicate = checkPermission('create_letter_templates');
        $canView = checkPermission('view_letter_templates');

        $total = $query->count();

        $templates = $query->orderBy($sort, $order)
            ->paginate($limit)
            ->through(function ($template) use ($canEdit, $canDelete, $canDuplicate, $canView) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'category' => ucfirst(str_replace('_', ' ', $template->category)),
                    'description' => $template->description ?: '-',
                    'is_active' => $template->is_active ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-danger">Inactive</span>',
                    'created_by' => trim($template->first_name . ' ' . $template->last_name),
                    'created_at' => format_date($template->created_at, true),
                    'updated_at' => format_date($template->updated_at, true),
                    'actions' => $this->generateActionButtons($template->id, $canView, $canEdit, $canDelete, $canDuplicate),
                ];
            });

        return response()->json([
            "rows" => $templates->items(),
            "total" => $total,
        ]);
    }

    public function show($id)
    {
        $template = LetterTemplate::with('creator')->findOrFail($id);
        $variables = LetterHelper::getAvailableVariables();

        return view('letters::templates.show', [
            'template' => $template,
            'variables' => $variables
        ]);
    }

    public function get($id)
    {
        $template = LetterTemplate::findOrFail($id);
        return response()->json(['template' => $template]);
    }

    public function edit($id)
    {
        $template = LetterTemplate::findOrFail($id);
        $categories = LetterHelper::getLetterCategories();
        $variables = LetterHelper::getAvailableVariables();

        return view('letters::templates.edit', [
            'template' => $template,
            'categories' => $categories,
            'variables' => $variables
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $isApi = $request->get('isApi', false);

            $request->merge([
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);
            $formFields = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'content' => 'required|string',
                'is_active' => 'required|in:0,1'
            ]);

            $template = LetterTemplate::findOrFail($id);

            $existingTemplate = LetterTemplate::where('workspace_id', $this->workspace->id)
                ->where('name', $formFields['name'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingTemplate) {
                if ($isApi) {
                    return formatApiResponse(true, 'Template with this name already exists');
                }
                return response()->json([
                    'error' => true,
                    'message' => 'Template with this name already exists.'
                ]);
            }

            $formFields['is_active'] = $request->has('is_active') ? true : false;
            $template->update($formFields);

            if ($isApi) {
                return formatApiResponse(false, 'Letter template updated successfully', [
                    'id' => $template->id,
                    'data' => $this->formatTemplateData($template)
                ]);
            }

            return response()->json([
                'error' => false,
                'message' => 'Letter template updated successfully.',
                'id' => $template->id,
                'redirect_url' => route('letter-templates.index')
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            return $this->handleException($e, $isApi, 'update');
        }
    }

    public function destroy($id)
    {
        try {
            $template = LetterTemplate::findOrFail($id);
            $template->delete();

            return response()->json([
                'error' => false,
                'message' => 'Letter template deleted successfully.',
                'id' => $id
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Letter template couldn\'t be deleted.'
            ]);
        }
    }

    public function duplicate(Request $request, $id)
    {
        try {
            $originalTemplate = LetterTemplate::findOrFail($id);

            $baseName = $originalTemplate->name . ' (Copy)';
            $name = $baseName;
            $counter = 1;

            while (LetterTemplate::where('workspace_id', $this->workspace->id)
                ->where('name', $name)
                ->exists()
            ) {
                $name = $baseName . ' ' . $counter;
                $counter++;
            }

            $duplicatedTemplate = $originalTemplate->replicate();
            $duplicatedTemplate->name = $name;
            $duplicatedTemplate->created_by = $this->user->id;
            $duplicatedTemplate->save();

            return response()->json([
                'error' => false,
                'message' => 'Letter template duplicated successfully.',
                'id' => $duplicatedTemplate->id,
                'template' => $duplicatedTemplate
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Letter template couldn\'t be duplicated.'
            ]);
        }
    }

    public function getVariables()
    {
        $variables = LetterHelper::getAvailableVariables();
        return response()->json(['variables' => $variables]);
    }

    public function preview(Request $request, $id = null)
    {
        try {
            $content = $request->input('content');

            if ($id) {
                $template = LetterTemplate::findOrFail($id);
                $content = $content ?: $template->content;
            }

            $processedContent = LetterHelper::processContent($content);

            return response()->json([
                'error' => false,
                'preview' => [
                    'content' => $processedContent,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Could not generate preview.'
            ]);
        }
    }

    public function getSampleContent(Request $request)
    {
        $category = $request->input('category');
        $sampleContent = LetterHelper::getSampleContent($category);

        return response()->json([
            'error' => false,
            'content' => $sampleContent
        ]);
    }

    public function toggleStatus($id)
    {
        try {
            $template = LetterTemplate::findOrFail($id);
            $template->is_active = !$template->is_active;
            $template->save();

            return response()->json([
                'error' => false,
                'message' => 'Template status updated successfully.',
                'is_active' => $template->is_active
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Could not update template status.'
            ]);
        }
    }

    public function exportPdf($id)
    {
        try {
            $template = LetterTemplate::findOrFail($id);

            $processedContent = LetterHelper::processContent($template->content);

            $html = view('letters::templates.pdf', [
                'template' => $template,
                'content' => $processedContent,
            ])->render();

            $pdf = Pdf::loadHTML($html);
            return $pdf->download($template->name . '.pdf');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Could not export template as PDF.');
        }
    }

    public function apiList(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'DESC');
        $category = $request->get('category');
        $is_active = $request->get('is_active', true);
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 0);
        $id = $request->get('id');

        $query = LetterTemplate::where('workspace_id', $this->workspace->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($is_active !== null) {
            $query->where('is_active', $is_active);
        }

        $total = $query->count();

        if ($id) {
            $template = $query->find($id);
            if (!$template) {
                return formatApiResponse(true, 'Letter template not found', ['total' => 0, 'data' => []]);
            }

            return formatApiResponse(false, 'Letter template retrieved successfully', [
                'total' => 1,
                'data' => $this->formatTemplateData($template, true)
            ]);
        }

        $templates = $query->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        if ($templates->isEmpty()) {
            return formatApiResponse(false, 'Letter templates not found', ['total' => 0, 'data' => []]);
        }

        $data = $templates->map(function ($template) {
            return $this->formatTemplateData($template);
        });

        return formatApiResponse(false, 'Letter templates retrieved successfully', [
            'total' => $total,
            'data' => $data
        ]);
    }

    private function formatTemplateData($template, $includeContent = false)
    {
        $data = [
            'id' => $template->id,
            'name' => $template->name,
            'category' => $template->category,
            'description' => $template->description,
            'is_active' => $template->is_active,
            'created_at' => format_date($template->created_at, true, to_format: 'Y-m-d'),
            'updated_at' => format_date($template->updated_at, true, to_format: 'Y-m-d')
        ];

        if ($includeContent) {
            $data['content'] = $template->content;
        }

        return $data;
    }

    private function generateActionButtons($id, $canView, $canEdit, $canDelete, $canDuplicate)
    {
        $actions = '';

        if ($canView) {
            $actions .= '<a href="' . route('letter-templates.show', $id) . '" class="btn btn-sm btn-outline-info" title="View">' .
                '<i class="bx bx-show"></i></a> ';
        }

        if ($canEdit) {
            $actions .= '<a href="' . route('letter-templates.edit', $id) . '" class="btn btn-sm btn-outline-primary" title="Edit">' .
                '<i class="bx bx-edit"></i></a> ';
        }

        if ($canDuplicate) {
            $actions .= '<button class="btn btn-sm btn-outline-secondary duplicate-template" data-id="' . $id . '" title="Duplicate">' .
                '<i class="bx bx-copy"></i></button> ';
        }

        if ($canDelete) {
            $actions .= '<button class="btn btn-sm btn-outline-danger delete-template" data-id="' . $id . '" title="Delete">' .
                '<i class="bx bx-trash"></i></button>';
        }

        return $actions ?: '-';
    }

    private function handleException($e, $isApi, $action)
    {
        $message = "Letter template couldn't be {$action}d.";

        if ($isApi) {
            return formatApiResponse(true, $message, []);
        }

        return response()->json(['error' => true, 'message' => $message]);
    }
}
