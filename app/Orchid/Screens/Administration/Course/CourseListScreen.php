<?php

namespace App\Orchid\Screens\Administration\Course;

use App\Exports\CourseExport;
use App\Imports\CourseImport;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class CourseListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $courses = Course::paginate();
        return [
            'courses' => $courses
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Courses';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Course')
                ->modal('createCourseModal')
                ->method('create')
                ->icon('plus'),
            ModalToggle::make('Import Courses')
                ->modal('uploadCoursesModal')
                ->method('upload')
                ->icon('upload'),
            Button::make('Export Data')
                ->method('download')
                ->rawClick(false)
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('courses', [
                TD::make('id', 'ID')
                    ->width('75'),
                TD::make('code', _('Course Code')),

                TD::make('name', _('Course Name')),

                TD::make('duration', _('Course Duration')),

                TD::make('created_at', __('Created On'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('updated_at', __('Last Updated'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make(__('Actions'))
                    ->width(200)
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (Course $course) {
                        $editButton = ModalToggle::make('Edit Course')
                            ->modal('editCourseModal')
                            ->modalTitle('Edit Course ' . $course->name)
                            ->method('edit') // You can define your edit method here
                            ->asyncParameters([
                                'course' => $course->id,
                            ])
                            ->render();

                        $deleteButton = Button::make('Delete')
                            ->confirm('Are you sure you want to delete this course?')
                            ->method('delete', [
                                'id' => $course->id
                            ])
                            ->render();

                        return "<div style='display: flex; justify-content: space-between;'>$editButton  $deleteButton</div>";
                    })


            ]),
            Layout::modal('createCourseModal', Layout::rows([

                Input::make('course.name')
                    ->title('Course Name')
                    ->placeholder('Enter course name'),

                Input::make('course.code')
                    ->title('Course Code')
                    ->placeholder('Enter course code'),

                Input::make('course.duration')
                    ->type('number')
                    ->title('Course Duration')
                    ->placeholder('Enter course duration'),

            ]))
                ->title('Create Course')
                ->applyButton('Create Course'),

            Layout::modal('editCourseModal', Layout::rows([
                Input::make('course.name')
                    ->title('Course Name')
                    ->placeholder('Enter course name'),

                Input::make('course.code')
                    ->title('Course Code')
                    ->placeholder('Enter course code'),

                Input::make('course.duration')
                    ->type('number')
                    ->title('Course Duration')
                    ->placeholder('Enter course duration'),
            ]))->async('asyncGetCourse'),

            Layout::modal('uploadCoursesModal', Layout::rows([
                Input::make('file')
                    ->type('file')
                    ->title('Import Courses'),
            ]))
                ->title('Upload Courses')
                ->applyButton('Upload Courses'),
        ];
    }

    /**
     * @return array
     */
    public function asyncGetCourse(Course $course): iterable
    {
        return [
            'course' => $course,
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function create(Request $request)
    {
        $request->validate([
            'course.name' => 'required',
            'course.code' => 'required',
            'course.duration' => 'required',
        ]);

        $course = new Course();
        $course->name = $request->input('course.name');
        $course->code = $request->input('course.code');
        $course->duration = $request->input('course.duration');
        $course->save();

        Alert::success("Course was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, Course $course): void
    {
        $request->validate([
            'course.name' => 'required',
            'course.code' => 'required',
            'course.duration' => 'required'
        ]);

        $course->fill($request->input('course'))->save();

        Alert::success(__('Course was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        Course::findOrFail($request->get('id'))->delete();

        Alert::success("Course was deleted.");
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function upload(Request $request)
    {
        // Define custom error messages for validation
        $customMessages = [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be a CSV file.',
            'file.max' => 'The file size must not exceed 64MB.',
        ];

        // Validate the request data using the defined rules and custom messages
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv|max:64000', // 64MB in kilobytes
            // Add any other validation rules you need for other fields
        ], $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve the uploaded file from the request
        $uploadedFile = $request->file('file');

        // Use Laravel Excel to import the data using your custom importer
        try {
            // Get the path of the uploaded file
            $filePath = $uploadedFile->path();

            // Import the data using your custom importer
            Excel::import(new CourseImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Course data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Courses data imported successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during import
            Alert::error($e->getMessage());

            return redirect()->back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function download(Request $request)
    {
        return Excel::download(new CourseExport, 'courses.csv', ExcelExcel::CSV);
    }
}
