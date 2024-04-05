<div class="container">
    <div class="row">
        <div class="col-md-12">
            <ol>
                <li>{!! \Orchid\Screen\Actions\Link::make('Click here to download import form')->href(asset('storage/templates/students_template.xlsx'))->target('blank') !!}</li>

                <li>The excel file above contains an example record. Follow the same format while uploading.</li>
                <li>After filling in the Excel document, validate for errors and fix them</li>
                <li>Import Excel document to add records to system</li>
            </ol>
        </div>
    </div>
</div>
