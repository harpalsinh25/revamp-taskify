<?php
namespace Plugins\AssetManagement\Controllers;
use Illuminate\Http\Request;
use Plugins\AssetManagement\Models\AssetCategory;
use App\Services\DeletionService;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;
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
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
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
                if ($canEdit) {
                    $actions .= '<a href="javascript:void(0);" class="updateCategoryModal"
                        data-asset-category=\'' . htmlspecialchars(json_encode($assetCategory), ENT_QUOTES, 'UTF-8') . '\'
                        title="' . get_label('update', 'Update') . '">
                        <i class="bx bx-edit mx-1"></i>
                    </a>';
                }
                if ($canDelete) {
                    $actions .= '<button type="button"
                        class="btn delete"
                        data-id="' . $assetCategory->id . '"
                        data-type="assets/category"
                        title="' . get_label('delete', 'Delete') . '">
                        <i class="bx bx-trash text-danger mx-1"></i>
                    </button>';
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
