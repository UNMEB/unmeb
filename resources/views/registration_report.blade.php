<div class="bg-white rounded shadow-sm mb-3" data-controller="table">
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th></th> 
                    <th></th>
                    @foreach ($courses as $data)
                    <th class="text-center" colspan="{{ $data->count}}">{{ $data->course->course_code }}</th>
                    @endforeach
                </tr>
                <tr>
                    <th>Code</th>
                    <th>Center</th>
                    @foreach ($courses as $data)
                    @foreach ($data->papers as $paper)
                    <th>{{ $paper->abbrev }}</th>
                    @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($institutions as $institution)
                    <tr>
                        <td>{{ $institution->code }}</td>
                        <td>{{ $institution->center }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>