<div class="mt-2 p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
    <div id="gender_distribution_by_course-container"></div>
</div>

@push('scripts')
    <script>
        var data = @json($gender_distribution_by_course)

        $(document).ready(function() {
            Highcharts.chart('gender_distribution_by_course-container', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Gender Distribution by Course',
                    align: 'left'
                },
                xAxis: {
                    categories: {!! json_encode($gender_distribution_by_course->pluck('course')) !!}
                },
                yAxis: {
                    type: 'logarithmic',
                    title: {
                        text: 'Count'
                    }
                },
                series: [{
                        name: 'Male',
                        data: {!! json_encode(
                            $gender_distribution_by_course->pluck('male_count')->map(function ($value) {
                                return (int) $value;
                            }),
                        ) !!}
                    },
                    {
                        name: 'Female',
                        data: {!! json_encode(
                            $gender_distribution_by_course->pluck('female_count')->map(function ($value) {
                                return (int) $value;
                            }),
                        ) !!}
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
        });
    </script>
@endpush
