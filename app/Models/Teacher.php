<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'subject_specialization',
        'employee_id',
        'date_of_joining',
        'status',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    
    public function assignedStudents()
    {
        return $this->hasMany(Student::class, 'assigned_teacher_id');
    }
}
