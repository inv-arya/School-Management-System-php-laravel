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
        $teachers = Teacher::with('user:id,username,email')
        ->where('status', 'active')
        ->paginate(2); 

        return response()->json([
            'status' => 'success',
            'count' => $teachers->total(), 
            'teachers' => $teachers
        ]);
    }

    public function show($id)
    {
        $teacher = Teacher::with('user')->find($id);

        if (!$teacher) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Teacher not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'teacher' => $teacher,
        ]);
    }

    public function update(Request $request, $id)
    {
        
        $teacher = Teacher::with('user')->find($id);

        if (!$teacher) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Teacher not found',
            ], 404);
        }

        
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string',
            'last_name'  => 'sometimes|required|string',
            'email'      => 'sometimes|required|email|unique:users,email,' . $teacher->user_id,
            'username'   => 'sometimes|required|string|unique:users,username,' . $teacher->user_id,
            'phone_number' => 'sometimes|required|string',
            'subject_specialization' => 'sometimes|required|string',
            'employee_id' => 'sometimes|required|string|unique:teachers,employee_id,' . $teacher->id,
            'date_of_joining' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

           
            $teacher->user->username = $request->input('username', $teacher->user->username);
            $teacher->user->email = $request->input('email', $teacher->user->email);

            // if ($request->filled('password')) {
            //     $teacher->user->password = Hash::make($request->password);
            // }

            $teacher->user->save();

            
            $teacher->update([
                'first_name' => $request->input('first_name', $teacher->first_name),
                'last_name' => $request->input('last_name', $teacher->last_name),
                'phone_number' => $request->input('phone_number', $teacher->phone_number),
                'subject_specialization' => $request->input('subject_specialization', $teacher->subject_specialization),
                'employee_id' => $request->input('employee_id', $teacher->employee_id),
                'date_of_joining' => $request->input('date_of_joining', $teacher->date_of_joining),
                'status' => $request->input('status', $teacher->status),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Teacher updated successfully',
                'teacher' => $teacher->load('user'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'fail',
                'message' => 'Update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function destroy($id)
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Teacher not found',
            ], 404);
        }

        $user = $teacher->user;
        $teacher->delete();
        $user->delete(); // delete linked user too

        return response()->json([
            'status' => 'success',
            'message' => 'Teacher and user deleted successfully',
        ]);
    }

}
