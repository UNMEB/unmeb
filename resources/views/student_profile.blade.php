@php
$studentData = json_decode($student);
@endphp

@if ($studentData)
@php
$studentRegistration = DB::table('nsin_student_registrations')
->join('students', 'nsin_student_registrations.student_id', '=', 'students.id')
->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
->join('years', 'nsin_registrations.year_id', '=', 'years.id')
->select('institutions.institution_name', 'courses.course_name', 'nsin_registrations.month', 'years.year')
->where('students.id', $student->id)
->get();
@endphp

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                {!! $student->avatar !!}
            </div>

            <div class="col-md-9">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Student ID</td>
                            <td>#{{ $student->id }}</td>
                        </tr>

                        <tr>
                            <td>Student Name</td>
                            <td>{{ $student->fullName }}</td>
                        </tr>

                        <tr>
                            <td>Gender</td>
                            <td>{{ $student->gender }}</td>
                        </tr>

                        <tr>
                            <td>Date Of Birth</td>
                            <td>{{ $student->dob }}</td>
                        </tr>

                        <tr>
                            <td>District of Origin</td>
                            <td>{{ $student->district->district_name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-12">
                <h4 class="mt-4">Education Information</h4>

                @foreach ($studentRegistration as $registration)
                <table class="table table-striped table-bordered table-sm">
                    <tbody>

                        <tr>
                            <td>Institution Name</td>
                            <td>{{ $registration->institution_name }} </td>
                        </tr>
                        <tr>
                            <td>Course Registered For</td>
                            <td>{{ $registration->course_name }} </td>
                        </tr>
                        <tr>
                            <td>Period of Study</td>
                            <td>{{ $registration->month }} {{ $registration->year }}</td>
                        </tr>

                    </tbody>
                </table>
                @endforeach
            </div>
        </div>
    </div>
</div>
@else
<!-- Handle the case when $student is null -->
No student data available.
@endif