$(function () {
    const multiselect = function () {
        let $elms = $(".multiselect");

        // Function to handle selecting all checkboxes in the table
        const selectAll = function () {
            const isChecked = $(this).prop("checked");
            $elms.prop("checked", isChecked);
        };

        // Function to handle individual checkbox selection
        const handleCheckbox = function () {
            const $headerCheckbox = $(".multiselect-header");
            const $checkboxes = $(".multiselect");

            const allChecked =
                $checkboxes.length === $checkboxes.filter(":checked").length;
            $headerCheckbox.prop("checked", allChecked);
        };

        $elms.each(function () {
            const $checkbox = $(this);
            const id = $checkbox.data("id");

            // Event listener for individual checkbox
            $checkbox.on("change", handleCheckbox);
        });

        // Event listener for header checkbox
        $(".multiselect-header").on("change", selectAll);
    };

    document.addEventListener("turbo:load", multiselect);
    multiselect();
});
