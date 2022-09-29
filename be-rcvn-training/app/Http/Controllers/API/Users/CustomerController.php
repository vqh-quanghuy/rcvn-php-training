<?php

namespace App\Http\Controllers\API\Users;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CustomersImport;
use App\Exports\CustomersExport;

class CustomerController extends Controller
{
    
    private function searchParams() {
        // Get Params from search 
        $customerName = \Request::get('name') ?: null;
        $customerEmail = \Request::get('email') ?: null;
        $customerStatus = \Request::get('customer_status');
        if(!is_numeric($customerStatus) && $customerStatus === '') $customerStatus = null;
        $customerAddress = \Request::get('address') ?: null;

        $customerName = preg_replace('/[^a-z0-9 _]+/i', '', $customerName);
        $customerEmail = preg_replace('/[^a-z0-9 _]+/i', '', $customerEmail);
        $customerAddress = preg_replace('/[^a-z0-9 _]+/i', '', $customerAddress);

        $validator = Validator::make([
            'customer_name' => $customerName, 
            'customer_email' => $customerEmail,
            'customer_status' => $customerStatus,
            'customer_address' => $customerAddress,
        ], [
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|string',
            'customer_status' => 'nullable|integer',
            'customer_address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Inputs',
                'error' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $per_page = intval(\Request::get('per_page')) ?: 10;

        return [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'customerStatus' => $customerStatus,
            'customerAddress' => $customerAddress,
            'perPage' => $per_page,
        ];
    }

    public function index()
    {
        $params = $this->searchParams();
        $customers = Customer::orderBy('created_at', 'desc');
        if(!empty($params['customerName'])) $customers = $customers->where('customer_name', 'like', "%{$params['customerName']}%");
        if(!empty($params['customerEmail'])) $customers = $customers->where('email', 'like', "%{$params['customerEmail']}%");
        if(!is_null($params['customerStatus'])) $customers = $customers->where('is_active', $params['customerStatus']);
        if(!empty($params['customerAddress'])) $customers = $customers->where('address', 'like', "%{$params['customerAddress']}%");
        
        $customers = $customers->paginate($params['perPage']);

        return response()->json([
            'status' => true,
            'data' => $customers,
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255|min:5',
            'tel_num' => 'required|string|max:14',
            'email' => 'required|string|email|max:255|unique:customers',
            'address' => 'required|string|max:255',
            'password' => 'required|string|confirmed|min:8',
            'is_active' => 'required|integer|between:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Inputs',
                'error' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $item = new Customer();
        $item->customer_name = $request->customer_name;
        $item->address = $request->address;
        $item->tel_num = $request->tel_num;
        $item->email = $request->email;
        $item->is_active = $request->is_active;
        $item->password = Hash::make($request->password);
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Created',
            'user' => $item,
        ], Response::HTTP_CREATED);
    }

    public function detail(Customer $customer)
    {
        return response()->json([
            'status' => true,
            'data' => $customer,
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255|min:5',
            'tel_num' => 'required|string|max:14',
            'email' => 'required|string|email|max:255|unique:customers,email,'.$customer->customer_id.',customer_id',
            'address' => 'required|string|max:255',
            'is_active' => 'required|integer|between:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Inputs',
                'error' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }
        $inputs = $request->post();
        // $inputs['password'] = Hash::make($request->post('password'));
        $customer->fill($inputs)->save();   

        return response()->json([
            'status' => true,
            'message' => 'Updated',
        ], Response::HTTP_ACCEPTED);
    }

    public function uploadCSV(Request $request)
    {
        if(!is_null($request->file('import_customers'))) {
            // dd("here");
            try {
                Excel::queueImport(new CustomersImport, $request->file('import_customers'));
            } catch(\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
                $errors = [];
                foreach ($failures as $failure) {
                    if(count($errors) > 0) {
                        if(array_key_exists($failure->attribute(), $errors)) {
                            array_push($errors[$failure->attribute()], $failure->errors());
                        } else {
                            $errors[$failure->attribute()] = $failure->errors();
                        }
                    } else {
                        $errors = [
                            'row' => $failure->row(),
                            $failure->attribute() => $failure->errors(),
                        ];
                    }
                }
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Inputs',
                    'error' => $errors,
                ], Response::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'status' => true,
                'message' => 'Import successful',
            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Inputs',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportCSV()
    {
        $params = $this->searchParams();

        return (new CustomersExport($params));
    }
}
