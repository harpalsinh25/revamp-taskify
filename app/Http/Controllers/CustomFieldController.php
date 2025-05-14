<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customFields = CustomField::all();
        return view('custom_fields.index', compact('customFields'));
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
                'module' => 'required|string|in:project,task',
                'field_label' => 'required|string',
                'field_type' => 'required|string|in:text,number,password,textarea,radio,date,checkbox,select',
                'options' => 'nullable|string|required_if:field_type,radio,checkbox,select',
                'required' => 'nullable|string',
                'visibility' => 'nullable|string',
            ]);
            $customField = new CustomField();
            $customField->module = $request->module;
            $customField->field_type = $request->field_type;
            $customField->field_label = $request->field_label;
            $customField->name = '';
            $customField->options = in_array($request->field_type, ['radio', 'checkbox', 'select'])
                ? json_encode(preg_split('/\r\n|\r|\n/', trim($request->options)))
                : null;

            $customField->required = $request->required;
            $customField->visibility = $request->visibility;
            $customField->save();
            return formatApiResponse(
                false,
                'Custom Field Created Successfully',
                [
                    'id' => $customField->id,
                    'type' => 'custom_field',
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            return formatApiResponse(
                true,
                'Custom Field Couldn\'t Updated.',
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
        $field = CustomField::find($id);

        // Decode JSON options for radio, checkbox, select
        if (in_array($field->field_type, ['radio', 'checkbox', 'select']) && $field->options) {
            $field->options = json_decode($field->options, true);
        }

        return response()->json(['success' => true, 'data' => $field]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $field = CustomField::find($id);


        $rules = [
            'module' => 'required|string|in:project,task',
            'field_label' => 'required|string',
            'field_type' => 'required|string|in:text,number,password,textarea,radio,date,checkbox,select',
            'options' => 'nullable|string|required_if:field_type,radio,checkbox,select',
            'required' => 'nullable|string',
            'visibility' => 'nullable|string',
        ];
        $validator = Validator::make($request->all(), $rules, [
            'regex' => 'This field must not contain special characters.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $field->module = $request->module;
        $field->field_label = $request->field_label;
        $field->field_type = $request->field_type;
        $field->options = in_array($request->field_type, ['radio', 'checkbox', 'select'])
            ? json_encode(preg_split('/\r\n|\r|\n/', trim($request->options)))
            : null;
        $field->required = $request->required;
        $field->visibility = $request->visibility;
        $field->save();

        return response()->json(['success' => 'Custom field updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $field = CustomField::find($id);

        $field->delete();
        return response()->json(['success' => 'Field deleted successfully'], 200);
    }


    public function list()
    {
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $limit = request('limit', 10);
        $offset = request('offset', 0);


        $customFields = CustomField::orderBy($sort, $order);

        if ($search) {
            $customFields = $customFields->where(function ($query) use ($search) {
                $query->where('module', 'like', '%' . $search . '%')
                    ->orWhere('field_label', 'like', '%' . $search . '%')
                    ->orWhere('field_type', 'like', '%' . $search . '%');
            });
        }

        $total = $customFields->count();

        $customFields = $customFields
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(
                fn($field) => [
                    'id' => $field->id,
                    'module' => $field->module,
                    'field_label' => $field->field_label,
                    'field_type' => $field->field_type,
                    'required' => ($field->required == '1') ? 'Yes' : 'No',
                    'visibility' => ($field->visibility == '1') ? 'Yes' : 'No',
                    'actions' => ''
                ]
            );

        return response()->json([
            "rows" => $customFields,
            "total" => $total,
        ]);
    }
}
