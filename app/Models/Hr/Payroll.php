<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'hr_payrolls';

    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'deductions',
        'bonuses',
        'net_salary',
        'status',
        'payment_date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
