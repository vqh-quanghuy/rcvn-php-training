<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Excel;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersExport implements FromCollection, Responsable, WithHeadings
{
    use Exportable;

    protected $params;

    private $fileName = 'customers.xlsx';
    private $writerType = Excel::CSV;
    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    function __construct($params) {
        $this->params = $params;
    }

    public function collection()
    {
        $params = $this->params;

        $customers = Customer::select('customer_name', 'email', 'tel_num', 'address')->orderBy('created_at', 'desc');
        if(!empty($params['customerName'])) $customers = $customers->where('customer_name', 'like', "%{$params['customerName']}%");
        if(!empty($params['customerEmail'])) $customers = $customers->where('email', 'like', "%{$params['customerEmail']}%");
        if(!is_null($params['customerStatus'])) $customers = $customers->where('is_active', $params['customerStatus']);
        if(!empty($params['customerAddress'])) $customers = $customers->where('address', 'like', "%{$params['customerAddress']}%");
        
        $customers = $customers->paginate($params['perPage']);

        return $customers;
    }

    public function headings(): array
    {
        return ["customer_name", "email", "tel_num", "address"];
    }
}
