<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\LeadFollowUp;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Validation\ValidationException;

class LeadFollowUpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            $formFields =   $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'lead_id' => 'required|exists:leads,id',
                'type' => 'required|in:email,sms,call,meeting,other',
                'status' => 'required|in:pending,completed,rescheduled',
                'follow_up_at' => 'required|date',
                'note' => 'nullable|string|max:255',

            ]);
            if (!empty($formFields['follow_up_at'])) {
                // Step 1: Parse as local (assume app timezone or user timezone)
                $localDate = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $formFields['follow_up_at'], config('app.timezone'));

                // Step 2: Convert to UTC
                $utcDate = $localDate->copy()->setTimezone('UTC');

                // Step 3: Format for storing in DB
                $formFields['follow_up_at'] = $utcDate->format('Y-m-d H:i:s');
            }



            $follow_up = LeadFollowUp::create($formFields);
            return formatApiResponse(
                false,
                'Follow Up Created Successfully',
                [
                    'id' => $follow_up->id,
                    'type' =>'lead_follow_up',
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {

            return formatApiResponse(
                true,
                'Follow Up Couldn\'t Created.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ],
                500
            );
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
    public function edit(string $id)
    {
        $follow_up = LeadFollowUp::findOrFail($id);
        $follow_up->load('assignedTo','lead');
        return response()->json([
            'error' => false,
            'message' => 'Follow Up Retrived Successfully',
            'follow_up' => $follow_up
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $isApi = $request->get('isApi', false);

        try {
            // Find the follow-up record
            $follow_up = LeadFollowUp::findOrFail($request->id);

            // Validate input
            $formFields = $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'type' => 'required|in:email,sms,call,meeting,other',
                'status' => 'required|in:pending,completed,rescheduled',
                'follow_up_at' => 'required|date',
                'note' => 'nullable|string|max:255',
            ]);

            // Handle timezone conversion for follow_up_at
            if (!empty($formFields['follow_up_at'])) {
                $localDate = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $formFields['follow_up_at'], config('app.timezone'));
                $utcDate = $localDate->copy()->setTimezone('UTC');
                $formFields['follow_up_at'] = $utcDate->format('Y-m-d H:i:s');
            }

            // Update the record
            $follow_up->update($formFields);

            return formatApiResponse(
                false,
                'Follow Up Updated Successfully',
                [
                    'id' => $follow_up->id,
                    'type' =>'lead_follow_up',
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            return formatApiResponse(
                true,
                'Follow Up Couldn\'t Be Updated.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ],
                500
            );
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(LeadFollowUp::class, $id, 'Lead Follow Up');
        return $response;
    }
}
