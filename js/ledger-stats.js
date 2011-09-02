(function($) {
    // Taken from http://jqueryui.com/demos/datepicker/#date-range
    var dates = $("#date-from, #date-to").datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        onSelect: function(selectedDate) {
            var option = this.id == "date-from" ? "minDate" : "maxDate",
                instance = $(this).data("datepicker"),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings );
            dates.not(this).datepicker("option", option, date);
        }
    }); 

    // Limit the number of items shown by autocomplete
    $.ui.autocomplete.prototype._renderMenu = function(ul, items) {
        var self = this;
        var limit = LedgerStats.accountLimit;
        $.each(items, function(index, item) {
            if (index < limit) {
                self._renderItem(ul, item);
            }
        });
    };

    // Configure account autocompletion - taken from http://jqueryui.com/demos/autocomplete/#multiple
    function split(val) { return val.split(/,\s*/); }
    function extractLast(term) { return split(term).pop(); }

    $("#accounts")
        // Don't navigate away from the field on tab when selecting an item
        .bind("keydown", function(event) {
            if (event.keyCode === $.ui.keyCode.TAB &&
                $(this).data("autocomplete").menu.active) {
                event.preventDefault();
            }
        })
        .autocomplete({
            minLength: 0,
            source: function(request, response) {
                // Delegate back to autocomplete, but extract the last term
                response($.ui.autocomplete.filter(
                    LedgerStats.accounts, extractLast(request.term)));
            },
            focus: function() {
                // Prevent value inserted on focus
                return false;
            },
            select: function(event, ui) {
                var terms = split(this.value);
                // Remove the current input
                terms.pop();
                // Add the selected item
                terms.push( ui.item.value );
                // Add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");
                return false;
            }
        });

    // Prevent errors if no Highcharts theme is set
    if (!Highcharts.theme) {
        Highcharts.theme = {};
    }
})(jQuery);
