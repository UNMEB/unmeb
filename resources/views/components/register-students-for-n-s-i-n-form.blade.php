<style>
    /* Reduce padding between table columns */
    .table th,
    .table td {
        padding: 0.5rem !important;
        /* Adjust the value to your preference */
        width: 1%;
        /* Set to minimum width */
        white-space: nowrap;
        /* Prevent wrapping of content */
    }
</style>

<div class="mt-2 p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">

    <form method="POST" action="{{ route('platform.registration.nsin.applications.new', ['method' => 'submitNSINs']) }}">

        <!-- Card Element for Account Balance -->
        <div class="card">
            <div class="card-header">
                Account Balance
            </div>
            <div class="card-body">
                <!-- Replace 'Institution Name' with the actual name of the institution -->
                <h5 class="card-title">Institution Name</h5>
                <!-- Replace 'Balance' with the actual balance for the institution -->
                <p class="card-text">Balance: {{ $balance }}</p>
            </div>
        </div>

        <table class="table table-condensed table-bordered">
            <thead>
                <tr>
                    <th scope="col" class="text-capitalize">
                        Student Name
                    </th>
                    <th scope="col" class="text-capitalize">
                        Date Of Birth
                    </th>
                    <th scope="col" class="text-capitalize">
                        NIN
                    </th>
                    <th scope="col" class="text-capitalize">
                        Phone Number
                    </th>
                    <th scope="col" class="text-capitalize">
                        Location
                    </th>
                    <th scope="col">
                        <label>

                            <input type="checkbox" class="multiselect-header" name="select_all">
                            Select All
                        </label>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $student->full_name }}</td>
                        <td>{{ $student->dob }}</td>
                        <td>{{ $student->nin }}</td>
                        <td>{{ $student->phone }}</td>
                        <td>{{ $student->location }}</td>
                        <td>
                            <label>
                                <input type="checkbox" class="multiselect" name="students[{{ $student->id }}]">
                            </label>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {!! \Orchid\Screen\Actions\Button::make(__('Submit NSINs'))->method('submitNSINs', [])->class('btn btn-success') !!}
    </form>

</div>
