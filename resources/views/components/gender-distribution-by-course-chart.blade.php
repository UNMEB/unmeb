<div class="mt-2 p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
    <div id="gender_distribution_by_course-container"></div>
</div>

@push('scripts')



<script>
    var data = @json($gender_distribution_by_course)

    Highcharts.chart('gender_distribution_by_course-container', {
        chart: {
                type: 'column'
            },
            title: {
                text: 'Gender Distribution by Course',
                align: 'left'
            },
            xAxis: {
                categories: {!! json_encode($gender_distribution_by_course->pluck('course_name')) !!}
            },
            yAxis: {
                type: 'logarithmic',
                title: {
                    text: 'Count'
                }
            },
            series: [
                {
                    name: 'Male',
                    data: {!! json_encode($gender_distribution_by_course->where('gender', 'Male')->pluck('gender_count')) !!}
                },
                {
                    name: 'Female',
                    data: {!! json_encode($gender_distribution_by_course->where('gender', 'Female')->pluck('gender_count')) !!}
                }
            ],
            exporting: {
                enabled: true, // Enable export options
                buttons: {
                    contextButton: {
                        menuItems: ['downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG']
                    }
                }
            }
        });
</script>
@endpush