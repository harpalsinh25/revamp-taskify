<?php

namespace Plugins\AssetManagement\Controllers;

use App\Services\DeletionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Plugins\AssetManagement\Models\AssetCategory;

class AssetsCategoryController extends Controller
{
    public function index()
    {
        $category = AssetCategory::all();
        return view('assets::assets.category.index', compact('category'));
    }

    public function store(Request $request)
    {
        $isApi = $request->get('isApi', false);

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required',
        ];

        try {
            $data = $request->validate($rules);
            AssetCategory::create($data);

            return response()->json([
                'error' => false,
                'message' => 'Asset Category created successfully!',
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(
                true,
                config('app.debug') ? $e->getMessage() : 'An error occurred',
                [],
                500
            );
        }
    }

    public function update(Request $request, $id)
    {
        $isApi = $request->get('isApi', false);

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required',
        ];

        try {
            $data = $request->validate($rules);

            $assetCategory = AssetCategory::findOrFail($id);
            $assetCategory->update($data);

            return response()->json([
                'error' => false,
                'message' => 'Asset Category updated successfully!',
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(
                true,
                config('app.debug') ? $e->getMessage() : 'An error occurred',
                [],
                500
            );
        }
    }

    public function destroy($id)
    {
        $assetCategory = AssetCategory::findOrFail($id);
        return DeletionService::delete(AssetCategory::class, $assetCategory->id, 'Asset Category');
    }

    public function destroy_multiple(Request $request)
    {
        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:asset_categories,id',
        ]);

        $deletedIds = [];

        foreach ($validatedData['ids'] as $id) {
            $assetCategory = AssetCategory::findOrFail($id);
            DeletionService::delete(AssetCategory::class, $assetCategory->id, 'Asset Categories');
            $deletedIds[] = $id;
        }

        return response()->json([
            'error' => false,
            'message' => 'Asset Category(ies) deleted successfully.',
            'id' => $deletedIds,
        ]);
    }

    public function list()
    {
        $search = request('search');
        $order = request('order', 'DESC');
        $limit = request('limit', 10);
        $offset = request('offset', 0);
        $sort = request('sort', 'id');

        $query = AssetCategory::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $canEdit = isAdminOrHasAllDataAccess();
        $canDelete = isAdminOrHasAllDataAccess();

        $assetCategories = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($assetCategory) use ($canEdit, $canDelete) {
                $actions = '';

                $hasActions = $canEdit || $canDelete;

                if ($hasActions) {
                    $actions = '<div class="dropdown">';
                    $actions .= '<button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $actions .= '<i class="bx bx-dots-vertical-rounded fs-5"></i>';
                    $actions .= '</button>';
                    $actions .= '<ul class="dropdown-menu dropdown-menu-end">';

                    if ($canEdit) {
                        $actions .= '<li><a href="javascript:void(0);" class="dropdown-item updateCategoryModal d-block" data-bs-toggle="offcanvas" data-bs-target="#updateCategoryOffcanvas" data-asset-category=\'' . htmlspecialchars(json_encode($assetCategory), ENT_QUOTES, 'UTF-8') . '\'>';
                        $actions .= '<i class="bx bx-edit text-primary me-2"></i>' . get_label('update', 'Update') . '</a></li>';
                    }

                    if ($canDelete) {
                        $actions .= '<li><hr class="dropdown-divider"></li>';
                        $actions .= '<li><a href="javascript:void(0);" class="dropdown-item delete text-danger d-block" data-id="' . $assetCategory->id . '" data-type="assets/category">';
                        $actions .= '<i class="bx bx-trash me-2"></i>' . get_label('delete', 'Delete') . '</a></li>';
                    }

                    $actions .= '</ul>';
                    $actions .= '</div>';
                } else {
                    $actions = '-';
                }

                return [
                    'id' => $assetCategory->id,
                    'name' => $assetCategory->name,
                    'color' => '<span class="badge bg-' . $assetCategory->color . '">' . ucfirst($assetCategory->color) . '</span>',
                    'description' => $assetCategory->description,
                    'created_at' => format_date($assetCategory->created_at, false, 'Y-m-d'),
                    'updated_at' => format_date($assetCategory->updated_at, false, 'Y-m-d'),
                    'actions' => $actions,
                ];
            });

        return response()->json([
            'rows' => $assetCategories,
            'total' => $total,
        ]);
    }
}
