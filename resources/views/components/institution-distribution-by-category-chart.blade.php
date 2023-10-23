<div class="mt-4 p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
    <div id="institution_distribution_by_category-container"></div>
</div>

@push('scripts')

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://code.highcharts.com/modules/variable-pie.js"></script>

<script>
    
    Highcharts.chart('institution_distribution_by_category-container', {
        chart: {
        type: 'pie',
    },
    title: {
        text: 'Institution Distribution by Type'
    },
    tooltip: {
        headerFormat: '',
        pointFormat: '<span style="color:{point.color}">\u25CF</span> <b> {point.name}</b><br/>' +
            'Count: <b>{point.y}</b><br/>' +
            'Percentage: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %'
            }
        }
    },
    series: [{
        minPointSize: 10,
        innerSize: '20%',
        zMin: 0,
        name: 'Types',
        borderRadius: 5,
        data: [
            @foreach($institution_distribution_by_category as $data)
                {
                    name: '{{$data->category}}',
                    y: {{$data->institution_count}}
                },
            @endforeach
        ],
       
    }]
        });
</script>
@endpush