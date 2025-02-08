<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::paginate(20);
        return response()->json(['customers' => $customers], 200);
    }

    public function getById(int $id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return response()->json(['message' => "Could not retrieve customer with id: $id"], 400);
        }

        return response()->json(['message' => 'Customer retrieved succesfully'], 200);
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
            $requestData = array_merge($requestData, ['cv_filepath' => $fileName]);
        }

        $customer = Customer::create($requestData);

        if (! $customer) {
            return response()->json(['message' => 'Could not create customer'], 500);
        }

        return response()->json(['message' => 'Created customer successfully'], 201);
    }

    public function update(int $id, Request $request)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return response()->json(["message" => "Customer with id {$id} does not exist"], 400);
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
            $requestData = array_merge($requestData, ['cv_filepath' => $fileName]);
        }
        //delete previous file
        $customer->update($requestData);

        if (! $customer->wasChanged()) {
            return response()->json(['message' => 'Could not update customer'], 500);
        }

        return response()->json(['message' => 'Created updated successfully'], 201);
    }

    public function destroy(int $id)
    {
        $customer = Customer::find($id)->delete();

        if ($customer->exists) {
            return response()->json(['message' => 'Customer delete failed.'], 500);
        }

        return response()->json(['message' => 'Customer deleted successfully.'], 200);
    }
}
