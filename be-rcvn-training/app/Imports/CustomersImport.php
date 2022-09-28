<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Http\Response;

class CustomersImport implements ToModel, WithHeadingRow
{
    // public function rules(): array
    // {
    //     return [
    //         'customer_name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             'min:5',
    //         ],
    //     ];
    // }

    public function model(array $row)
    {
        // dd($row);
        // Validator::make($row, [
        //     'customer_name' => 'required|string|max:255|min:5',
        //     'tel_num' => 'required|string|max:14',
        //     'email' => 'required|string|email|max:255|unique:customers',
        //     'address' => 'required|string|max:255',
        // ])->validate();
        // dd($row);
        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Invalid Inputs',
        //         'error' => $validator->errors()
        //     ], Response::HTTP_BAD_REQUEST);
        // }
        // dd($row);
        return new Customer([
            'customer_name' => $row['customer_name'],
            'email' => $row['email'],
            'address' => $row['address'],
            'tel_num' => $row['tel_num'],
            'is_active' => 1,
            'password' => Hash::make('password'),
        ]);
    }
}
