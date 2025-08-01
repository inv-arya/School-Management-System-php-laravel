<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Teacher;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'roll_number',
        'grade',
        'date_of_birth',
        'admission_date',
        'status',
        'assigned_teacher_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(Teacher::class, 'assigned_teacher_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
