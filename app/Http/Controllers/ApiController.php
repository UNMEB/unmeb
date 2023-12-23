<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\RegistrationPeriod;
use App\Models\StudentPaperRegistration;
use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Registration;
use App\Models\BiometricEnrollment;
use App\Models\BiometricAccessLog;

class ApiController extends Controller
{
    /**
     * User login (Normal restful auth)
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken('AuthToken')->plainTextToken;
            return response()->json(['token' => $token, 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    /**
     * Handle Biometric Enrollment for students.
     * Parameters: student_id, institution_id, face_data, fingerprint_data
     */
    public function enroll(Request $request)
    {
        $data = $request->only('student_id', 'institution_id', 'face_data', 'fingerprint_data');

        // Create a new biometric enrollment record
        $biometricEnrollment = BiometricEnrollment::create($data);

        return response()->json(['message' => 'Biometric enrolled successfully', 'enrollment' => $biometricEnrollment], 201);
    }

    /**
     * Verify students.
     * Parameters: institution_id (from auth session), student_id (from post data), registration_id
     */
    public function verify(Request $request)
    {
        $institutionId = auth()->user()->institution_id;
        $studentId = $request->input('student_id');
        $registrationId = $request->input('registration_id');

        // Find the registration for the student in the institution
        $registration = Registration::where('institution_id', $institutionId)
            ->where('student_id', $studentId)
            ->where('id', $registrationId)
            ->first();

        if ($registration) {
            // Perform verification actions here
            // For example, update verification status in the registration record

            return response()->json(['message' => 'Student verified successfully', 'registration' => $registration], 200);
        } else {
            return response()->json(['message' => 'Student verification failed'], 404);
        }
    }

    /**
     * Get Students.
     * 
     */
    public function students(Request $request)
    {
        // Get the active registration period
        $registrationPeriod = RegistrationPeriod::where('flag', 1)
            ->first();

        if ($registrationPeriod == null) {
            return response()->json([
                'status' => 'FAILED',
                'message' => 'Unable to find an active registration period'
            ], 500);
        }

        // Institution Id
        $institutionId = auth()->user()->institution_id;

        $query = Student::query()
            ->select('surname', 'firstname', 'nsin')
            ->join('student_registrations', 'students.id', '=', 'student_registrations.student_id')
            ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('registration_periods', 'registrations.registration_period_id', '=', 'registration_periods.id')
            ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
            ->where('registration_periods.flag', 1)
            ->where('student_registrations.sr_flag', 1)
            ->where('students.old', 0)
            ->limit(10);

        if ($institutionId) {
            $query->where('institutions.id', $institutionId);
        }

        $students = $query->get();

        return response()->json([
            'status' => 'SUCCESS',
            'students' => $students
        ]);
    }
}
