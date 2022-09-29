<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Hash;

class CustomersImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue, WithValidation
{
    public function prepareForValidation($data, $index)
    {
        $data['tel_num'] = strval($data['tel_num']);
        
        return $data;
    }
    public function rules(): array
    {
        return [
            'customer_name' => [ 'required', 'string', 'max:255', 'min:5', ],
            'tel_num' => [ 'required', 'string', 'max:14', ],
            'address' => [ 'required', 'string', 'max:255', ],
            'email' => [ 'required', 'string', 'email', 'max:255', 'unique:customers' ],
        ];
    }
    public function customValidationMessages()
    {
        return [
            // Name Error Messages
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'customer_name.string' => 'Tên khách hàng phải là chuỗi ký tự.',
            'customer_name.max' => 'Tên khách hàng phải ít hơn 255 ký tự.',
            'customer_name.min' => 'Tên khách hàng phải lớn hơn 5 ký tự.',
            // Telephone Number Error Messages
            'tel_num.required' => 'Điện thoại không được để trống.',
            'tel_num.string' => 'Điện thoại phải là chuỗi ký tự.',
            'tel_num.max' => 'Số điện thoại phải ít hơn 14 số.',
            // Address Number Error Messages
            'address.required' => 'Địa chỉ không được để trống.',
            'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
            'address.max' => 'Địa chỉ phải ít hơn 255 ký tự.',
            // Email Error Messages
            'email.required' => 'Email không được để trống.',
            'email.string' => 'Điện thoại phải là chuỗi ký tự.',
            'email.max' => 'Email phải ít hơn 255 ký tự.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã được đăng ký.',
        ];
    }

    public function model(array $row)
    {
        return new Customer([
            'customer_name' => $row['customer_name'],
            'email' => $row['email'],
            'address' => $row['address'],
            'tel_num' => $row['tel_num'],
            'is_active' => 1,
            'password' => Hash::make('password'),
        ]);
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
