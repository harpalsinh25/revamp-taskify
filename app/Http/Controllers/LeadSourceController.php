<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Workspace;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Validation\ValidationException;

class LeadSourceController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lead_sources = $this->workspace->lead_sources;
        return view('lead_sources.index', compact('lead_sources'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isApi = $request->get('isApi', false);
        try {
            $request->validate([
                'name' => 'required|string'
            ]);
            $lead_source = new LeadSource();
            $lead_source->workspace_id = getWorkspaceId();
            $lead_source->name = $request->name;
            $lead_source->save();
            if ($isApi) {
                return formatApiResponse(
                    false,
                    'Lead Source Created Successfully',
                    [
                        'id' => $lead_source->id,
                        'data' => [
                            'id' => $lead_source->id,
                            'name' => $lead_source->name,
                            'created_at' => format_date($lead_source->created_at, true, to_format: 'Y-m-d'),
                            'updated_at' => format_date($lead_source->updated_at, true, to_format: 'Y-m-d')
                        ]
                    ]
                );
            } else {
                return response()->json(['error' => false, 'message' => 'Lead Source Created Successfully', 'id' => $lead_source->id, 'type' => 'lead_source']);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            if ($isApi) {
                return formatApiResponse(
                    true,
                    'Lead Source Couldn\'t Created',
                );
            } else {
                return response()->json(['error' => true, 'message' => 'Lead Source Couldn\'t Created']);
            }
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function get(string $id)
    {
        $lead_source = LeadSource::findOrFail($id);
        return response()->json(['error' => false,'message'=>'Lead Source Retrived Successfully' , 'lead_source' => $lead_source]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $isApi = $request->get('isApi', false);
        try {
            $request->validate([
                'id' => 'required|exists:lead_sources,id',
                'name' => 'required',
            ]);
            $lead_source = LeadSource::findOrFail($request->id);
            $lead_source->name = $request->name;
            $lead_source->save();
            if ($isApi) {
                return formatApiResponse(
                    false,
                    'Lead Source Updated Successfully.',
                    [
                        'id' => $lead_source->id,
                        'data' => formatLeadSource($lead_source),
                    ]
                );
            } else {
                return response()->json(['error' => false, 'message' => 'Lead Source Updated Successfully', 'id' => $lead_source->id, 'type' => 'lead_source']);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            if ($isApi) {
                return formatApiResponse(
                    true,
                    'Lead Source Couldn\'t Updated.',
                    [
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile()
                    ]
                );
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Lead Source Couldn\'t Updated.',
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
            }
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(LeadSource::class, $id, 'lead_source');
        return $response;
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:lead_sources,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $lead_source = LeadSource::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $lead_source->name;
            DeletionService::delete(LeadSource::class, $id, 'lead_source');
        }

        return response()->json(['error' => false, 'message' => 'LeadSource(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }

    public function list()
    {

        $search = request('search');
        $sort = request('sort', "id");
        $order = request('order', "DESC");
        $limit = request('limit', 10);

        $lead_sources = $this->workspace->lead_sources();
        $lead_sources  = $lead_sources->orderBy($sort, $order);


        if ($search) {
            $lead_sources->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $total = $lead_sources->count();

        $lead_sources = $lead_sources
            ->paginate($limit)
            ->through(
                fn($lead_source) => [
                    'id' => $lead_source->id,
                    'name' => ucwords($lead_source->name),
                    'created_at' => format_date($lead_source->created_at, true),
                    'updated_at' => format_date($lead_source->updated_at, true),
                    'actions' => $this->getActions($lead_source),
                ]
            );

        return response()->json([
            "rows" => $lead_sources->items(),
            "total" => $total,
        ]);
    }
    private function getActions($lead_source)
    {
        $actions = '';
        $canEdit = checkPermission('manage_leads');  // Replace with your actual condition
        $canDelete = checkPermission('manage_leads'); // Replace with your actual condition


        if ($canEdit) {
            $actions .= '<a href="javascript:void(0);" class="edit-lead-source" data-id="' . $lead_source->id . '" title="' . get_label('update', 'Update') . '">' .
                '<i class="bx bx-edit mx-1"></i>' .
                '</a>';
        }

        if ($canDelete) {
            $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $lead_source->id . '" data-type="lead-sources" data-table="table">' .
                '<i class="bx bx-trash text-danger mx-1"></i>' .
                '</button>';
        }



        return $actions ?: '-';
    }
    public function apiList()
    {
        $limit = request('limit', 10);
        $offset = request('offset', 0);
        $id = request('id', null);
        $search = request('search');
        $sort = request('sort', "id");
        $order = request('order', "DESC");
        $limit = request('limit', 10);

        $lead_sources = $this->workspace->lead_sources();
        $lead_sources  = $lead_sources->orderBy($sort, $order);


        if ($search) {
            $lead_sources->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $total = $lead_sources->count();

        if ($id) {
            $lead_source = $lead_sources->find($id);
            if (!$lead_source) {
                return formatApiResponse(
                    false,
                    'Lead Source Not Found.',
                    [
                        'total' => 0,
                        'data' => []
                    ],

                );
            }
            return formatApiResponse(
                false,
                'Lead Source Retrived Successfully.',
                [
                    'total' => 1,
                    'data' => formatLeadSource($lead_source)
                ]
            );
        } else {
            $lead_sources = $lead_sources->orderBy($sort, $order)->skip($offset)->take($limit)->get();
            if ($lead_sources->isEmpty()) {
                return formatApiResponse(
                    false,
                    'Lead Sources Not Found.',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }
            $data = $lead_sources->map(function ($lead_source) {
                return formatLeadSource($lead_source);
            });
            return formatApiResponse(
                false,
                'Lead Sources Retrived Successfully.',
                [
                    'total' => $total,
                    'data' => $data
                ]
            );
        }
    }
}
