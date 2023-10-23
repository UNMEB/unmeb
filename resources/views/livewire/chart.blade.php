<!-- resources/views/livewire/chart.blade.php -->

<div>
    <div id="chart-container"></div>
</div>

@push('scripts')

<script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
         Highcharts.chart('chart-container', {
                chart: {
                    type: 'bar',
                },
                title: {
                    text: 'Random Bar Chart',
                },
                xAxis: {
                    categories: @json($categories),
                },
                yAxis: {
                    title: {
                        text: 'Values',
                    },
                },
                series: @json($series),
            });
    </script>
@endpush
