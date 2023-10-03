<style>
    .paper-code {
        font-weight: bold;
        text-align: center;
    }
</style>

<div class="bg-white rounded shadow-sm mb-3">
    <div class="card-body">
        <table id="myTable" class="table table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th colspan="2" class="left-columns"></th>
                    <th colspan="3" class="paper-code">Course 1</th>
                    <th colspan="3" class="paper-code">Course 2</th>
                    <th colspan="3" class="paper-code">Course 3</th>
                </tr>
                <tr>
                    <th>Code</th>
                    <th>Center</th>
                    <th>P1</th>
                    <th>P2</th>
                    <th>P3</th>
                    <th>P1</th>
                    <th>P2</th>
                    <th>P3</th>
                    <th>P1</th>
                    <th>P2</th>
                    <th>P3</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item['Code'] }}</td>
                    <td>{{ $item['Center'] }}</td>
                    <td>{{ $item['Course1']['P1'] }}</td>
                    <td>{{ $item['Course1']['P2'] }}</td>
                    <td>{{ $item['Course1']['P3'] }}</td>
                    <td>{{ $item['Course2']['P1'] }}</td>
                    <td>{{ $item['Course2']['P2'] }}</td>
                    <td>{{ $item['Course2']['P3'] }}</td>
                    <td>{{ $item['Course3']['P1'] }}</td>
                    <td>{{ $item['Course3']['P2'] }}</td>
                    <td>{{ $item['Course3']['P3'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>


    </div>
</div>
<footer>
    <ul class="pagination">
        {{ $posts->links() }}
    </ul>
</footer>