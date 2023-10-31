<div class="pivot-container bg-white rounded shadow-sm mb-3">
    <div id="pivotContainer" style="width: 100%;"></div>
</div>

@push('stylesheets')
    <style>
        .pivot-container {
            width: 100%;
            /* Set the desired width for your container */
            overflow-x: auto;
            /* Enable horizontal scrolling when content overflows */
            white-space: nowrap;
            /* Prevent content from wrapping to the next line */
        }
    </style>
@endpush

@push('scripts')
    <script>
        jQuery(document).ready(function() {
            jQuery("#pivotContainer").pivotUI(<?php echo json_encode($report); ?>, {
                rows: ["Institution", "Center"],
                cols: ["Course", "Paper"],
                aggregator: null,
                rendererName: "Heatmap",
                showUI: false
            });

            // Wrap the sortable initialization in a callback function
            jQuery(".ui-sortable").sortable({
                disabled: true
            });

            // On download click use sheetjs to convert html to csv
            jQuery('#download').on('click', function(e) {
                e.preventDefault();

                // Get the HTML table element
                jQuery('table').tableExport({
                    type: 'excel',
                    htmlContent: true,
                    mso: {
                        fileFormat: 'xlshtml',
                        styles: ['border-collapse', 'border', 'border-width', 'border-style',
                            'border-color', 'font-weight',
                            'border-bottom', 'border-top', 'border-left', 'border-right',
                            'background-color',
                            'font'
                        ],
                    },
                });
            });
        })
    </script>
@endpush
</body>

</html>
