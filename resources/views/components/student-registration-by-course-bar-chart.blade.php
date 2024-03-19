<div class="mt-2 p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
    <div id="student_registration_by_course-container"></div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            var data = @json($student_registration_by_course)

            Highcharts.chart('student_registration_by_course-container', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Student Registrations By Course (Current Period)',
                    align: 'left'
                },
                xAxis: {
                    categories: data.map(item => item.course),
                    title: {
                        text: 'Programs'
                    }
                },
                yAxis: {
                    type: 'logarithmic',
                    title: {
                        text: 'Number of Students'
                    }
                },
                series: [{
                    name: 'Students',
                    data: data.map(item => item.count_of_students)
                }],
                exporting: {
                    enabled: true, // Enable export options
                    buttons: {
                        contextButton: {
                            menuItems: ['downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG']
                        }
                    }
                }
            });
        });
    </script>
@endpush
