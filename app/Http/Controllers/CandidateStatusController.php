<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CandidateStatus;
use App\Services\DeletionService;

class CandidateStatusController extends Controller
{
    public function index()
    {
        $candidate_statuses = CandidateStatus::all();
        return view('candidate.candidate_status.index', compact('candidate_statuses'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required'
        ]);

        $order = CandidateStatus::max('order') + 1;

        $candidate_status = CandidateStatus::create([
            'name' => $request->name,
            'order' => $order,
            'color' => $request->color
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Status Created Successfully!',
            'candidate_statuses' => $candidate_status
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $candidate_status = CandidateStatus::findOrFail($id);

        $candidate_status->update([
            'name' => $request->name,
            'color' => $request->color
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Status updated Successfully!',
            'candidate_status' => $candidate_status
        ]);
    }

    public function destroy($id)
    {

        $candidate_status = CandidateStatus::findOrFail($id);

        $candidateCount = $candidate_status->candidates->count();

        if ($candidateCount > 0) {
            return response()->json([
                'error' => 'false',
                'message' => ' Cannot delete . This status is assigned to one or more candidates . '
            ]);
        }

        $response = DeletionService::delete(CandidateStatus::class, $candidate_status->id, 'Candidate Status');

        return $response;
    }

    public function destroy_multiple(Request $request)
    {

        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:candidate_statuses,id'
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $notDeleted = [];

        foreach ($ids as $id) {
            $candidate_status = CandidateStatus::findOrFail($id);

            // If status is linked to candidates, skip deletion
            if ($candidate_status->candidates()->count() > 0) {
                $notDeleted[] = $id;
                continue;
            }


            DeletionService::delete(CandidateStatus::class, $candidate_status->id, 'Candidate Status');
            $deletedIds[] = $id;
        }

        return response()->json([
            'error' => count($notDeleted) > 0,
            'message' => count($notDeleted) ? 'Some statuses could not be deleted because they are assigned to candidates.' : 'Candidate Status(es) Deleted Successfully!',
            'id' => $deletedIds,
        ]);
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $item) {
            CandidateStatus::where('id', $item['id'])->update([
                'order' => $item['position']
            ]);
        }
        return response()->json([
            'error' => false,
            'message' => 'Order updated successfully!'
        ]);
    }


    public function list()
    {

        $search = request('search');
        $limit = request('limit', 10);
        $offset = request('offset', 0);
        $order = request('order', 'DESC');
        $sort = request('sort', 'id');

        $query = CandidateStatus::orderBy('order');


        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $total = $query->count();

        $canEdit = checkPermission('edit_candidate_status');
        $canDelete = checkPermission('delete_candidate_status');

        $statuses = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($status) use ($canDelete, $canEdit) {

            $actions = '';

            if ($canEdit) {
                $actions .= '<a href="javascript:void(0);" class="edit-candidate-status-btn"
                                        data-candidate-status=\'' . htmlspecialchars(json_encode($status), ENT_QUOTES, 'UTF-8') . '\'
                                        title="' . get_label('update', 'Update') . '">
                                        <i class="bx bx-edit mx-1"></i>
                                        </a>';
            }


            if ($canDelete) {
                $actions .= '<button type="button"
                                        class="btn delete"
                                        data-id="' . $status->id . '"
                                        data-type="candidate_status"
                                        title="' . get_label('delete', 'Delete') . '">
                                        <i class="bx bx-trash text-danger mx-1"></i>
                                        </button>';
            }

            return [
                'id' => $status->id,
                'order' => $status->order,
                'name' => ucwords($status->name),
                'created_at' => format_date($status->created_at),
                'color' => '<span class="badge bg-' . $status->color . '">' . ucfirst($status->color) . '</span>',
                'updated_at' => format_date($status->updated_at),
                'actions' => $actions ?: '-'
            ];
            });

        return response()->json([
            'rows' => $statuses,
            'total' => $total,
        ]);
    }
}
