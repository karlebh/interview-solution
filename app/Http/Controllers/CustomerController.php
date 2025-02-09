<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::paginate(20);
        return response()->json(['customers' => $customers], 200);
    }

    public function show(int $id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return response()->json(['message' => "Could not retrieve customer with id: $id"], 400);
        }

        return response()->json([
            'message' => 'Customer retrieved succesfully',
            'customer' => $customer
        ], 200);
    }

    public function store(Request $request)
    {
        $requestData = $request->validate([
            'name'         => ['required', 'string', 'min:3', 'max:50'],
            'email'        => ['required', 'email', 'unique:customers,email'],
            'phone_number' => ['required', 'string', 'max:20'],
            'cv_file'      => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480']
        ]);

        if ($request->hasFile('cv_file')) {

            $cv = $request->file('cv_file');

            $fileName = time() . '.' . $cv->extension();
            $cv->storeAs('cv_uploads', $fileName, 'public');

            $requestData =  Arr::except($requestData, ['cv_file']);
            $requestData = array_merge($requestData, ['cv_file' => $fileName]);
        }

        $customer = Customer::create(
            array_merge(
                $requestData,
                ['user_id' => $request->user()->id]
            )
        );

        if (! $customer) {
            return response()->json(['message' => 'Could not create customer'], 500);
        }

        return response()->json([
            'message' => 'Created customer successfully',
            'customer' => $customer,
        ], 201);
    }

    public function update(int $id, Request $request)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return response()->json(["message" => "Customer with id {$id} does not exist"], 404);
        }

        $requestData = $request->validate([
            'name'         => ['nullable', 'string', 'min:3', 'max:50'],
            'email'        => ['nullable', 'email', 'unique:customers,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'cv_file'      => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480']
        ]);

        if ($request->hasFile('cv_file')) {

            $cv = $request->file('cv_file');

            $fileName = time() . '.' . $cv->extension();
            $cv->storeAs('cv_uploads', $fileName, 'public');

            $requestData =  Arr::except($requestData, ['cv_file']);
            $requestData = array_merge($requestData, ['cv_file' => $fileName]);

            //delete previous file
            $previousFilePath = storage_path('app/public/cv_uploads/' . $customer->cv_file);

            if (File::exists($previousFilePath)) {
                File::delete($previousFilePath);
            }
        }

        if (empty(array_filter($requestData))) {
            return response()->json(['message' => 'No fields were provided'], 400);
        }

        $customer->update($requestData);

        if (! $customer->wasChanged()) {
            return response()->json(['message' => 'No updates were made to customer'], 500);
        }

        return response()->json([
            'message' => 'Created updated successfully',
            'customer' => $customer->fresh(),
        ], 201);
    }

    public function destroy(int $id)
    {
        $customer = Customer::find($id)->delete();

        if (! $customer) {
            return response()->json(['message' => 'Customer delete failed.'], 500);
        }

        return response()->json(["message" => "Customer with id: {$id} deleted successfully."], 200);
    }

    public function downloadCV(int $id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return response()->json(['message' => 'Could not find customer.'], 500);
        }
        $filePath = storage_path('app/public/cv_uploads/' . $customer->cv_file);

        if (! $filePath) {
            return response()->json(['message' => 'CV not found'], 404);
        }

        $extension = pathinfo($customer->cv_file, PATHINFO_EXTENSION);

        return response()->download($filePath, $customer->name . " - CV" . $extension);
    }
}
