<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'hr_employees';

    protected $fillable = [
        'user_id',
        'department_id',
        'designation_id',
        'shift_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'is_docs_submitted',
        'date_of_birth',
        'joining_date',
        'basic_salary',
        'status',
        'custom_start_time',
        'custom_end_time',
        'face_encoding',
        'face_photo',
    ];

    protected $casts = [
        'face_encoding' => 'array',
    ];

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function getDocument($type)
    {
        return $this->documents()->where('type', $type)->first()->file_path ?? null;
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function salaryStructure()
    {
        return $this->hasOne(SalaryStructure::class);
    }
    
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the effective start time (custom or shift)
     */
    public function getStartTime()
    {
        if ($this->custom_start_time) {
            return $this->custom_start_time;
        }
        return $this->shift ? $this->shift->start_time : '09:00:00';
    }

    /**
     * Get the effective end time (custom or shift)
     */
    public function getEndTime()
    {
        if ($this->custom_end_time) {
            return $this->custom_end_time;
        }
        return $this->shift ? $this->shift->end_time : '18:00:00';
    }

    /**
     * Get grace minutes from shift or default
     */
    public function getGraceMinutes()
    {
        return $this->shift ? $this->shift->grace_minutes : 15;
    }

    /**
     * Check if employee has registered face
     */
    public function hasFaceRegistered()
    {
        return !empty($this->face_encoding);
    }
}
