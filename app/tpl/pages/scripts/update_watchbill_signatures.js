$(document).ready(function() {
    // On page load, set the initial state based on data attributes
    $('.toggleable').each(function() {
        var $this = $(this);
        var bawSigned = $this.data('baw_signed');
        var brwSigned = $this.data('brw_signed');

        if (bawSigned === 1 || brwSigned === 1) {
            $this.addClass('green-bg').removeClass('default-bg');
            $this.text('~~~~~~~');
        } else {
            $this.addClass('default-bg').removeClass('green-bg');
            $this.text('');
        }
    });

    // Toggle signed/unsigned state on click
    $(document).on('click', '.toggleable', function() {
        var $this = $(this);
        var $row = $this.closest('tr');
        var timeSlot = $row.find('td.editable.baw').data('timeslot'); // Correctly select the timeslot from the BAW column
        var timeRange = $row.find('td.editable.baw').data('timerange');
        var bawName = $row.find('td.editable.baw').text().trim();
        var brwName = $row.find('td.editable.brw').text().trim();
        var buildingName = $(this).closest('table').find('th[colspan="5"]').text().trim();

        var isClearingSignature = ($this.data('baw_signed') === 1 && $this.data('brw_signed') === undefined) ||
                                  ($this.data('brw_signed') === 1 && $this.data('baw_signed') === undefined);

        // Check if adding a signature and BAW or BRW name is not set
        if (!isClearingSignature &&
            (($this.data('baw_signed') !== undefined && bawName === '') ||
             ($this.data('brw_signed') !== undefined && brwName === ''))) {
            var $alert = $('<div>', {
                text: "Error: Unable to sign. There is no assigned watchstander for this " + timeRange + " slot at " + buildingName + ".",
                class: 'alert bg-red animated fadeIn',
                css: {
                    'position': 'fixed',
                    'top': '20px',
                    'left': '50%',
                    'transform': 'translateX(-50%)', // Center the alert horizontally
                    'z-index': '9999',
                    'padding': '10px 20px',
                    'color': 'white',
                    'border-radius': '5px'
                }
            }).appendTo('body');

            // Automatically fade out and remove the alert after 3 seconds
            setTimeout(function() {
                $alert.removeClass('fadeIn').addClass('fadeOut');
                setTimeout(function() {
                    $alert.remove();
                }, 1000); // Match the duration of the fadeOut animation
            }, 4000); // Display the alert for 4 seconds before fading out
            return; // Stop further execution if no name is set
        }

        var watchType, signedValue;
        if ($this.data('baw_signed') !== undefined) {
            watchType = 'BAW';
            signedValue = $this.data('baw_signed') ? 0 : 1; // Toggle the signed value
        } else if ($this.data('brw_signed') !== undefined) {
            watchType = 'BRW';
            signedValue = $this.data('brw_signed') ? 0 : 1; // Toggle the signed value
        }

        var location = $this.siblings('.editable[data-type="' + watchType + '"]').data('location');

        // AJAX call to update the signed status in the database
        $.ajax({
            url: '/handlers/update_watchbill_signed.php',
            type: 'POST',
            data: {
                watch_type: watchType,
                time: timeSlot,
                location: location,
                signed: signedValue
            },
            success: function(response) {
                if (response.includes("Success")) {
                    // Toggle class and text
                    if (signedValue === 1) {
                        $this.removeClass('default-bg').addClass('green-bg');
                        $this.text('~~~~~~~');
                        $this.data(watchType.toLowerCase() + '_signed', 1);
                    } else {
                        $this.removeClass('green-bg').addClass('default-bg');
                        $this.text('');
                        $this.data(watchType.toLowerCase() + '_signed', 0);
                    }
                } else {
                    alert("Error updating the database: " + response);
                }
            },
            error: function() {
                alert("AJAX call failed. Please try again.");
            }
        });
    });
});
