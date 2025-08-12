<?php

namespace Plugins\Letter\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\DeletionService;
use Plugins\Letter\Models\Letter;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Plugins\Letter\Helper\LetterHelper;
use Illuminate\Validation\ValidationException;
use Plugins\Letter\Models\LetterTemplate;

class LetterController extends Controller
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

    /**
     * Show letter list page.
     */
    public function index(Request $request)
    {
        // dd('here');
        // dd($this->workspace , getWorkspaceId());
        $letters = Letter::where(['workspace_id' => $this->workspace->id])->get()->toArray();
        return view('letters::letters.index', [
            'categories' => LetterHelper::getLetterCategories(),
            'letters' => $letters
        ]);
    }

    public function create (Request $request){
        $users = $this->workspace->users ?? [];
        $templates = LetterTemplate::where(['workspace_id' => $this->workspace->id])->get()->toArray();
        return view('letters::letters.create',compact('users','templates'));
    }
    /**
     * List letters via AJAX.
     */
    public function list(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');

        $letters = Letter::with(['template', 'user'])
            ->where('workspace_id', $this->workspace->id);

        if ($search) {
            $letters->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    });
            });
        }

        $total = $letters->count();
        $letters = $letters->orderBy($sort, $order)
            ->paginate($request->input('limit', 10))
            ->through(function ($letter) {
                return [
                    'id' => $letter->id,
                    'title' => $letter->title,
                    'user' => $letter->user ? $letter->user->name : '-',
                    'template' => $letter->template ? $letter->template->name : '-',
                    'letter_date' => format_date($letter->letter_date),
                    'created_at' => format_date($letter->created_at, true),
                    'actions' => view('letters::letters.partials.actions', compact('letter'))->render(),
                ];
            });

        return response()->json([
            'rows' => $letters->items(),
            'total' => $total,
        ]);
    }
    /**
     * Generate live preview with parsed variables.
     */
    public function preview(Request $request)
    {

        $content = $request->content ?? '';
        $parsedContent = LetterHelper::processContent($content, getAuthenticatedUser());

        return response()->json([
            'preview' => $parsedContent
        ]);
    }

    /**
     * Store a new letter.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'user_id' => 'required|exists:users,id',
                'template_id' => 'nullable|exists:letter_templates,id',
                'content' => 'required',
                'letter_date' => 'required|date',
            ]);

            $validated['workspace_id'] = $this->workspace->id;
            $validated['created_by'] = $this->user->id;
            $validated['metadata'] = json_encode([]);

            $letter = Letter::create($validated);

            return response()->json([
                'error' => false,
                'message' => 'Letter created successfully.',
                'id' => $letter->id,
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError(false, $e->errors());
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => 'An error occurred while creating the letter.']);
        }
    }

    /**
     * Get a letter for editing.
     */
    public function get($id)
    {
        $letter = Letter::with('template', 'user')->where('workspace_id', $this->workspace->id)->findOrFail($id);
        return response()->json(['letter' => $letter]);
    }

    /**
     * Update a letter.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'user_id' => 'required|exists:users,id',
                'template_id' => 'nullable|exists:letter_templates,id',
                'content' => 'required',
                'letter_date' => 'required|date',
            ]);

            $letter = Letter::where('workspace_id', $this->workspace->id)->findOrFail($id);
            $letter->update($validated);

            return response()->json([
                'error' => false,
                'message' => 'Letter updated successfully.',
                'id' => $letter->id,
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError(false, $e->errors());
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => 'An error occurred while updating the letter.']);
        }
    }

    /**
     * Delete a letter.
     */
    public function destroy($id)
    {
        return DeletionService::delete(Letter::class, $id, 'Letter');
    }

    /**
     * Download letter as PDF.
     */
    public function download($id)
    {
        $letter = Letter::where('workspace_id', $this->workspace->id)->findOrFail($id);

        $parsedContent = LetterHelper::processContent($letter->content, $letter->user);
        $pdf = Pdf::loadHTML($parsedContent);

        return $pdf->download(str_replace(' ', '_', $letter->title) . '.pdf');
    }

    /**
     * Send letter via email.
     */
    public function sendEmail(Request $request, $id)
    {
        $letter = Letter::where('workspace_id', $this->workspace->id)->findOrFail($id);
        $user = User::findOrFail($letter->user_id);

        $parsedContent = LetterHelper::processContent($letter->content, $user);
        $pdf = Pdf::loadHTML($parsedContent);

        LetterHelper::sendLetterEmail(
            $user->email,
            $letter->title,
            $parsedContent,
            $pdf,
            $letter
        );

        return response()->json([
            'error' => false,
            'message' => 'Letter sent to user email successfully.',
        ]);
    }
}
