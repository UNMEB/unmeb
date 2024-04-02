<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Course;
use App\Models\District;
use App\Models\Institution;
use App\Models\Student;
use Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class StudentImport implements ToModel, WithHeadingRow, WithValidation
{

    public function __construct()
    {
        HeadingRowFormatter::default('none');
    }

    public function prepareForValidation(array $row)
    {
        dd($row);

        return $row;
    }


    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|null
     */
    public function model(array $row)
    {



        // Retrieve related models based on codes provided in the Excel sheet
        $institution = Institution::firstWhere('id', auth()->user()->institution_id);
        $program = Course::firstWhere('course_code', $row['program_code']);
        $country = Country::firstWhere('name', $row['country']);
        $district = District::firstWhere('district_name', $row['district']);

        // Convert date of birth to 'YYYY-MM-DD' format
        $dob = Carbon::createFromFormat('d/m/Y', $row['dob'])->format('d/m/Y');

        dd($dob);

        // Create and return a new Student instance
        return new Student([
            'surname' => $row['surname'],
            'othername' => $row['othername'],
            'firstname' => $row['firstname'],
            'nin' => $row['nin'],
            'lin' => $row['lin'],
            'passport_number' => $row['passport_number'],
            'institution_id' => $institution->id,
            'applied_program' => $program->id,
            'date_time' => now(),
            'country_id' => $country->id,
            'district_id' => $district->id,
            'email' => $row['email'],
            'gender' => $row['gender'],
            'dob' => $dob,
            'location' => $row['home_address'],
            'telephone' => $row['phone'],
            'passport' => asset('placeholder/avatar.png')
        ]);
    }


    /**
     * @return array
     */
    public function rules(): array
    {
        $user = Auth::user();

        return [
            'surname' => 'required',
            'firstname' => 'required',
            'program_code' => 'required|exists:courses,course_code',
            'country' => 'required|exists:countries,name',
            'district' => 'required|exists:districts,district_name',
            'email' => 'required|email',
            'gender' => 'required',
            '*.dob' => 'required|date_format:d/m/Y|before_or_equal:' . now()->subYears(18)->format('d/m/Y') . '|after_or_equal:' . now()->subYears(65)->format('d/m/Y'),
            'home_address' => 'required',
            'phone' => 'required',
            'nin' => 'required_without_all:lin,passport_number',
            'lin' => 'required_without_all:nin,passport_number',
            'passport_number' => 'required_without_all:nin,lin',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'surname.required' => 'The surname field is required.',
            'firstname.required' => 'The firstname field is required.',
            'program_code.required' => 'The program code field is required.',
            'program_code.exists' => 'The selected program code is invalid.',
            'country.required' => 'The country field is required.',
            'country.exists' => 'The selected country is invalid.',
            'district.required' => 'The district field is required.',
            'district.exists' => 'The selected district is invalid.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'gender.required' => 'The gender field is required.',
            '*.dob.required' => 'The date of birth field is required.',
            '*.dob.date_format' => 'The date of birth must be in the format dd/mm/yyyy.',
            '*.dob.before_or_equal' => 'You must be at least 18 years old.',
            '*.dob.after_or_equal' => 'You must be at most 65 years old.',
            'home_address.required' => 'The home address field is required.',
            'phone.required' => 'The phone field is required.',
            'nin.required_without_all' => 'At least one of NIN, LIN, or Passport number is required.',
            'lin.required_without_all' => 'At least one of NIN, LIN, or Passport number is required.',
            'passport_number.required_without_all' => 'At least one of NIN, LIN, or Passport number is required.',
        ];
    }
}
