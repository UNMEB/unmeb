<div class="bg-white rounded shadow-sm d-flex flex-column p-4" style="margin-top: 50px;">
    <div id="student_registration_by_institution-container"></div>
</div>

@push('scripts')

<script>
    var data = @json($student_registration_by_institution)

    Highcharts.chart('student_registration_by_institution-container', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Student Registrations By institution',
                align: 'left'
            },
            xAxis: {
                categories: data.map(item => item.institution),
                title: {
                    text: 'institutions'
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
</script>
@endpush