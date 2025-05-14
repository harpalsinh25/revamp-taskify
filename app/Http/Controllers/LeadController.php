<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Workspace;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Models\UserClientPreference;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LeadController extends Controller
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
        $leads = $this->workspace->leads();
        return view('leads.index', compact('leads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lead_sources = LeadSource::where(function ($query) {
            $query->where('workspace_id', $this->workspace->id)
                ->orWhere(function ($q) {
                    $q->whereNull('workspace_id')->where('is_default', true);
                });
        })->get();

        $lead_stages = LeadStage::where(function ($query) {
            $query->where('workspace_id', $this->workspace->id)
                ->orWhere(function ($q) {
                    $q->whereNull('workspace_id')->where('is_default', true);
                });
        })->orderBy('order', 'ASC')->get();
        $users = $this->workspace->users;
        return view('leads.create', compact('lead_sources', 'lead_stages', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isApi = $request->get('isApi', false);

        try {
            $formFields = $request->validate([
                'first_name'        => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'email'             => 'required|email|unique:leads,email',
                'phone'             => 'required|string|max:20',
                'country_code'      => 'required|string|max:5',
                'country_iso_code'  => 'required|string|size:2',
                'source_id'         => 'required|exists:lead_sources,id',
                'stage_id'          => 'required|exists:lead_stages,id',
                'assigned_to'       => 'required|exists:users,id',
                'job_title'         => 'nullable|string|max:255',
                'industry'          => 'nullable|string|max:255',
                'company'           => 'required|string|max:255',
                'website'           => 'nullable|url|max:255',
                'linkedin'          => 'nullable|url|max:255',
                'instagram'         => 'nullable|url|max:255',
                'facebook'          => 'nullable|url|max:255',
                'pinterest'         => 'nullable|url|max:255',
                'city'              => 'nullable|string|max:255',
                'state'             => 'nullable|string|max:255',
                'zip'               => 'nullable|string|max:20',
                'country'           => 'nullable|string|max:255',
            ]);

            $formFields['created_by'] = $this->user->id;
            $formFields['workspace_id'] = $this->workspace->id;

            $lead = Lead::create($formFields);

            if ($isApi) {
                return formatApiResponse(
                    false,
                    'Lead Created Successfully.',
                    [
                        'id' => $lead->id,
                        'data' => formatLead($lead),
                    ]
                );
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Lead Created Successfully.',
                    'id' => $lead->id,
                    'type' => 'lead'
                ]);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            return formatApiResponse(
                true,
                'Lead Couldn\'t Created.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lead = Lead::findOrFail($id);
        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lead = Lead::findOrFail($id);
        return view('leads.edit', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $isApi = $request->get('isApi', false);

        try {
            $lead = Lead::where('workspace_id', $this->workspace->id)
                ->where('id', $id)
                ->firstOrFail();

            $formFields = $request->validate([
                'first_name'        => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'email'             => 'required|email|unique:leads,email,' . $lead->id,
                'phone'             => 'required|string|max:20',
                'country_code'      => 'required|string|max:5',
                'country_iso_code'  => 'required|string|size:2',
                'source_id'         => 'required|exists:lead_sources,id',
                'stage_id'          => 'required|exists:lead_stages,id',
                'assigned_to'       => 'required|exists:users,id',
                'job_title'         => 'nullable|string|max:255',
                'industry'          => 'nullable|string|max:255',
                'company'           => 'required|string|max:255',
                'website'           => 'nullable|url|max:255',
                'linkedin'          => 'nullable|url|max:255',
                'instagram'         => 'nullable|url|max:255',
                'facebook'          => 'nullable|url|max:255',
                'pinterest'         => 'nullable|url|max:255',
                'city'              => 'nullable|string|max:255',
                'state'             => 'nullable|string|max:255',
                'zip'               => 'nullable|string|max:20',
                'country'           => 'nullable|string|max:255',
            ]);


            $lead->update($formFields);

            if ($isApi) {
                return formatApiResponse(
                    false,
                    'Lead Updated Successfully.',
                    [
                        'id' => $lead->id,
                        'data' => formatLead($lead),
                    ]
                );
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Lead Updated Successfully.',
                    'id' => $lead->id,
                    'type' => 'lead'
                ]);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (ModelNotFoundException $e) {
            return formatApiResponse(
                true,
                'Lead not found.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        } catch (Exception $e) {
            return formatApiResponse(
                true,
                'Lead Couldn\'t Updated.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(Lead::class, $id, 'leads');
        return $response;
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:leads,id' // Ensure each ID in 'ids' is an integer and exists in the 'projects' table
        ]);
        $ids = $validatedData['ids'];
        $deletedLeads = [];
        $deletedLeadsTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $lead = Lead::find($id);
            if ($lead) {
                $deletedLeadTitles[] = ucwords($lead->first_name . ' ' . $lead->last_name);

                DeletionService::delete(Lead::class, $id, 'Lead');
                $deletedLeads[] = $id;
            }
        }
        return response()->json(['error' => false, 'message' => 'Lead(s) deleted successfully.', 'id' => $deletedLeads, 'titles' => $deletedLeadsTitles]);
    }
    public function list()
    {
        $search = request('search');
        $sortOptions = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'recently-updated' => ['updated_at', 'desc'],
            'earliest-updated' => ['updated_at', 'asc'],
        ];
        [$sort, $order] = $sortOptions[request()->input('sort')] ?? ['id', 'desc'];
        $source_ids  = request('source_ids', []);
        $stage_ids   = request('stage_ids', []);
        $start_date = request('start_date');
        $end_date = request('end_date');

        $limit = request('limit', 10);

        $leads = isAdminOrHasAllDataAccess()
            ? $this->workspace->leads()
            : $this->user->leads();

        $leads = $leads->with(['source', 'stage', 'assigned_user']); // eager load if needed
        $leads = $leads->orderBy($sort, $order);

        if ($search) {
            $leads->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('company', 'like', "%$search%")
                    ->orWhere('job_title', 'like', "%$search%")
                    ->orWhere('id', 'like', "%$search%");
            });
        }

        if (!empty($source_ids)) {
            $leads->whereIn('source_id', $source_ids);
        }
        if (!empty($stage_ids)) {
            $leads->whereIn('stage_id', $stage_ids);
        }
        if ($start_date && $end_date) {
            $leads->whereBetween('created_at', [$start_date, $end_date]);
        }

        $total = $leads->count();

        $leads = $leads->paginate($limit)->through(function ($lead) {

            $stage = '<span class="badge bg-' . $lead->stage->color . '">' . $lead->stage->name . '</span>';

            return [
                'id' => $lead->id,
                'name' => formatLeadUserHtml($lead),
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company,
                'website' => $lead->website,
                'job_title' => $lead->job_title,
                'stage' => $stage,
                'source' => optional($lead->source)->name,
                'assigned_to' => formatUserHtml($lead->assigned_user),
                'created_at' => format_date($lead->created_at, true),
                'updated_at' => format_date($lead->updated_at, true),
                'actions' => $this->getActions($lead),
            ];
        });

        return response()->json([
            'rows' => $leads->items(),
            'total' => $total,
        ]);
    }
    private function getActions($lead)
    {
        $actions = '';
        $canEdit = checkPermission('edit_leads');  // Replace with your actual condition
        $canDelete = checkPermission('delete_leads'); // Replace with your actual condition
        $isConverted = $lead->is_converted == 1 ? true : false;


        $actions = '<div class="d-flex align-items-center">';

        $actions .= '<a href="' . route('leads.show', ['id' => $lead->id]) . '"
                class="text-info btn btn-sm p-1 me-1"
                data-id="' . $lead->id . '"
                title="' . get_label('view', 'View') . '">
                <i class="bx bx-show"></i>
            </a>';

        if ($canEdit) {
            $actions .= '<a href="' . route('leads.edit', ['id' => $lead->id]) . '"
                    class="text-primary btn btn-sm  p-1 me-1"
                    data-id="' . $lead->id . '"
                    title="' . get_label('update', 'Update') . '">
                    <i class="bx bx-edit"></i>
                </a>';
        }

        if ($canDelete) {
            $actions .= '<button title="' . get_label('delete', 'Delete') . '"
                    type="button"
                    class="btn btn-sm p-1 delete text-danger"
                    data-id="' . $lead->id . '"
                    data-type="leads"
                    data-table="table">
                    <i class="bx bx-trash"></i>
                </button>';
        }
        if (!$isConverted) {
            $actions .= '<button class="btn btn-sm text-primary convert-to-client" title="' . get_label('convert_to_client', 'Convert To Client') . '"
                             data-id="' . $lead->id . '"><i
                            class="bx bxs-analyse me-1 p-1"></i>
                        </button>';
        }

        $actions .= '</div>';
        return $actions;
    }

    public function kanban(Request $request)
    {
        $sources = (array) $request->input('sources', []);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $sortOptions = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'recently-updated' => ['updated_at', 'desc'],
            'earliest-updated' => ['updated_at', 'asc'],
        ];
        [$sort, $order] = $sortOptions[$request->input('sort')] ?? ['id', 'desc'];

        $leadsQuery = isAdminOrHasAllDataAccess()
            ? $this->workspace->leads()
            : $this->user->leads();
        $leadsQuery = $leadsQuery
            ->with(['source', 'stage', 'assigned_user'])
            ->orderBy($sort, $order);

        if (!empty($sources)) {
            $leadsQuery->whereIn('source_id', $sources);
        }
        if ($start_date && $end_date) {
            $leadsQuery->whereBetween('updated_at', [$start_date, $end_date]);
        }

        $leads = $leadsQuery->get();

        $lead_stages = LeadStage::where(function ($query) {
            $query->where('workspace_id', $this->workspace->id)
                ->orWhere(function ($q) {
                    $q->whereNull('workspace_id')->where('is_default', true);
                });
        })
            ->orderBy('order', 'ASC')
            ->get();


        return view('leads.kanban', compact('leads', 'lead_stages'));
    }

    public function stageChange(Request $request)
    {
        $isApi = $request->get('isApi', false);
        try {
            $request->validate([
                'id' => 'required|exists:leads,id',
                'stage_id' => 'required|exists:lead_stages,id',
            ]);
            $lead = Lead::findOrFail($request->id);
            $lead->stage_id = $request->stage_id;

            $lead->save();

            return response()->json([
                'error' => false,
                'message' => 'Lead Stage Updated Successfully.',
                'id' => $lead->id,
                'type' => 'lead',
                'activity_message' => 'Lead Stage Changed to ' . $lead->stage->name,
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (ModelNotFoundException $e) {
            return formatApiResponse(
                true,
                'Lead not found.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        } catch (Exception $e) {
            return formatApiResponse(
                true,
                'Lead Couldn\'t Updated.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
    }

    public function saveViewPreference(Request $request)
    {
        $view = $request->input('view');
        $prefix = isClient() ? 'c_' : 'u_';
        if (
            UserClientPreference::updateOrCreate(
                ['user_id' => $prefix . $this->user->id, 'table_name' => 'leads'],
                ['default_view' => $view]
            )
        ) {
            return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
        } else {
            return response()->json(['error' => true, 'message' => 'Something Went Wrong.']);
        }
    }

    public function convertToClient(Request $request, Lead $lead)
    {
        if ($lead->is_converted == 1) {
            return formatApiResponse(
                true,
                'Lead is already converted to the client.',
                [
                    'id' => $lead->id
                ]
            );
        }

        // Prepare new request data
        $clientData = [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'company' => $lead->company,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'country_code' => $lead->country_code,
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'country' => $lead->country,
            'zip' => $lead->zip,
            'internal_purpose' => 'on', // so password is optional
        ];

        // Use ClientController directly to reuse store logic
        $clientRequest = new Request($clientData);
        $clientController = new \App\Http\Controllers\ClientController();
        $response = $clientController->store($clientRequest);

        // Decode the response body (assuming JSON response)
        $responseBody = json_decode($response->getContent(), true);

        // Check if the response contains an error flag or status
        if (isset($responseBody['error']) && $responseBody['error'] === true) {
            // Handle the error
            return formatApiValidationError(
                true,
                $responseBody['errors'] ?? []
            );
        }

        // Check if response status code is not 200 (error scenario)
        if ($response->getStatusCode() != 200) {
            // Handle the error response (non-200 status code)
            return formatApiResponse(
                true,
                'Something went wrong while converting the lead.',
                []
            );
        }

        // If successful, update the lead and return the successful response
        $lead->update(['is_converted' => 1, 'converted_at' => now()]);

        return $response; // Return the success response
    }
}
