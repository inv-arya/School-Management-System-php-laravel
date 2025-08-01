<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class StudentController extends Controller
{
   public function register(Request $request)
    {
        $user = JWTAuth::user();

        
        if (!in_array($user->role, ['admin', 'teacher'])) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized',
            ], 403);
        }

        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users,email',
            'username'   => 'required|string|unique:users,username',
            'password'   => 'required|string|min:6',
            'phone_number' => 'required|string',
            'roll_number' => 'required|string|unique:students,roll_number',
            'grade'       => 'required|string',
            'date_of_birth' => 'required|date',
            'admission_date' => 'required|date',
            'status'     => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        
        try {
            
            $newUser = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
            ]);

            
            $assignedTeacherId = null;
            if ($user->role === 'teacher') {
                $teacher = Teacher::where('user_id', $user->id)->first();
                if (!$teacher) {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Teacher profile not found.',
                    ], 404);
                }
                $assignedTeacherId = $teacher->id;
            }
            elseif ($user->role === 'admin') {
                if ($request->has('assigned_teacher_id')) {
                    $assignedTeacherId = $request->assigned_teacher_id;
                    if (!Teacher::where('id', $assignedTeacherId)->exists()) {
                        return response()->json([
                            'status' => 'fail',
                            'message' => 'Invalid assigned_teacher_id',
                        ], 422);
                        }
                }
            }

            // Create Student
            $student = Student::create([
                'user_id' => $newUser->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'roll_number' => $request->roll_number,
                'grade' => $request->grade,
                'date_of_birth' => $request->date_of_birth,
                'admission_date' => $request->admission_date,
                'status' => $request->status,
                'assigned_teacher_id' => $assignedTeacherId,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Student registered successfully',
                'student' => $student,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function index()
    {
        $user = JWTAuth::user();

        if ($user->role === 'admin') {
            $students = Student::with(['user:id,username,email', 'assignedTeacher:id,first_name,last_name'])->get();
        } elseif ($user->role === 'teacher') {
            
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Teacher profile not found',
                ], 404);
            }

            
            $students = Student::with(['user:id,username,email', 'assignedTeacher:id,first_name,last_name'])
                ->where('assigned_teacher_id', $teacher->id)
                ->get();
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'count' => $students->count(),
            'students' => $students
        ]);
    }
}
