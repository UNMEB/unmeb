<style>
.bootstrap-tagsinput[disabled], .bootstrap-tagsinput[readonly], .chosen-choices[disabled], .chosen-choices[readonly], .chosen-single[disabled], .chosen-single[readonly], .form-control[disabled], .form-control[readonly], fieldset[disabled] .bootstrap-tagsinput, fieldset[disabled] .chosen-choices, fieldset[disabled] .chosen-single, fieldset[disabled] .form-control {
    background: #ffffff;
    color: rgba(73,80,87,0.9);
}
</style>

<div class="mt-2 p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">

    <form method="POST" action="{{ route('platform.assessment.marks', ['method' => 'submitMarks']) }}">

        @csrf

        @if ($paper_type == 'Theory')
        <table class="matrix table table-bordered border-right-0">
            <thead>
                <tr>
                    <th scope="col" class="text-capitalize">Student Name</th>
                    <th scope="col" class="text-capitalize">Assignment 1 (Out of 20%)</th>
                    <th scope="col" class="text-capitalize">Assignment 2 (Out of 20%)</th>
                    <th scope="col" class="text-capitalize">Test 1 (Out of 20%)</th>
                    <th scope="col" class="text-capitalize">Test 2 (Out of 20%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                <tr>
                  <td>{{ $student->name }}</td>
                  <td>{!! \Orchid\Screen\Fields\Input::make('marks[' . $student->id . '][first_assignment_marks]')->type('number')->value($student->first_assignment_marks)->required()
                    ->disabled($student->first_assignment_marks != null)
                    ->placeholder(' - ')->max(20) !!}</td>
                  <td>{!! \Orchid\Screen\Fields\Input::make('marks[' . $student->id . '][second_assignment_marks]')->type('number')->value($student->second_assignment_marks)->required()
                    ->disabled($student->first_assignment_marks != null)
                    ->placeholder(' - ')->max(20) !!}</td>
                  <td>{!! \Orchid\Screen\Fields\Input::make('marks[' . $student->id . '][first_test_marks]')->type('number')->value($student->first_test_marks)->disabled($student->first_test_marks != null)->required()->placeholder(' - ')->max(20) !!}</td>
                  <td>{!! \Orchid\Screen\Fields\Input::make('marks[' . $student->id . '][second_test_marks]')->type('number')->value($student->second_test_marks)->disabled($student->second_test_marks != null)->required()->placeholder(' - ')->max(20) !!}</td>
              </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <table class="matrix table table-bordered border-right-0">
            <thead>
                <tr>
                    <th scope="col" class="text-capitalize">Student Name</th>
                    <th scope="col" class="text-capitalize">Logbook Assessment (Out of 20%)</th>
                    <th scope="col" class="text-capitalize">Clinical Assessment (Out of 10%)</th>
                    <th scope="col" class="text-capitalize">Practical Assessment (Out of 10%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{!! \Orchid\Screen\Fields\Input::make('marks[' . $student->id . '][logbook_assessment]')->type('number')->required()->placeholder(' - ')->max(20) !!}</td>
                    <td>{!! \Orchid\Screen\Fields\Input::make('marks[' . $student->id . '][clinical_assessment]')->type('number')->required()->placeholder(' - ')->max(10) !!}</td>
                    <td>{!! \Orchid\Screen\Fields\Input::make('marks[' . $student->id . '][practical_assessment]')->type('number')->required()->placeholder(' - ')->max(10) !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {!!
        \Orchid\Screen\Actions\Button::make(__('Submit Marks'))
        ->method('submitMarks', [
          'institution_id' => request()->get('institution_id'),
                        'year_of_study' => request()->get('year_of_study'),
                        'course_id' => request()->get('course_id'),
                        'paper_id' => request()->get('paper_id'),
                        'paper_type' => request()->get('paper_type'),
                        'exam_registration_period_id' => request()->get('exam_registration_period_id'),
          ])
        ->class('btn btn-primary')
        !!}
    </form>

</div>