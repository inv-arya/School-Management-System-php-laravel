<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users,email',
            'username'   => 'required|string|unique:users,username',
            'password'   => 'required|string|min:6',
            'phone_number'      => 'required|string',
            'subject_specialization'    => 'required|string',
            'employee_id'=> 'required|string|unique:teachers,employee_id',
            'date_of_joining' => 'required|date',
            'status'     => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
        
        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'teacher',
        ]);

        
        $teacher = Teacher::create([
            'user_id'       => $user->id,
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'           => $request->email,
            'phone_number'  => $request->phone_number,
            'subject_specialization' => $request->subject_specialization,
            'employee_id'   => $request->employee_id,
            'date_of_joining'=> $request->date_of_joining,
            'status'        => $request->status,
        ]);
        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Teacher registered successfully',
            'teacher' => $teacher,
        ], 201);
        
        }
        catch (\Exception $e) {
            DB::rollBack(); 

            return response()->json([
                'status' => 'fail',
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function index()
    {
        $teachers = Teacher::with('user:id,username,email')->get();

        return response()->json([
            'status' => 'success',
            'count' => $teachers->count(),
            'teachers' => $teachers
        ]);
    }
}
