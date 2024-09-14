$(document).ready(function() {
    // Make the cell editable on click
    $(document).on('click', '.editable', function() {
        var $this = $(this);

        // Check if there's already an input in this cell
        if ($this.find('select').length > 0) {
            $this.find('select').focus();
            return;
        }

        var originalID = $this.data('id'); // Assuming the original ID is stored in a data attribute
        var originalText = $this.text().trim(); // Store the original text
        var type = $this.data('type');
        var time = $this.data('timeslot');
        var location = $this.data('location');

        // Get the current width of the td
        var tdWidth = $this.width();

        // Create a select element
        var input = $('<select>', {
            css: {
                'width': '100%',
                'box-sizing': 'border-box',
                'text-align': 'center',
                'padding': '0',
                'border': 'none',
                'text-shadow': '0px 0px 2px gold',
                'outline': 'none',
                'background-color': 'transparent'
            },
            'data-live-search': 'true',
            blur: function() {
            var newID = $(this).val();
            if (newID === "") {
                newID = null; // Set to null if empty
            }
            if (newID === null) {
                $this.html("<center>&nbsp;</center>");
            } else {
                $this.html("<center>" + $(this).find('option:selected').text() + "</center>");
            }
            },
            keyup: function(e) {
                if (e.key === 'Enter') {
                    var newID = $(this).val();
                    if (newID !== originalID) {
                        updateWatchstander($this, type, time, location, newID, originalID);
                    } else {
                        $this.html("<center>" + $(this).find('option:selected').text() + "</center>");
                    }
                }
            }
        });

        input.on('change', function() {
            var newID = $(this).val();
            if (newID !== originalID) {
                updateWatchstander($this, type, time, location, newID, originalID);
            } else {
                $this.html("<center>" + $(this).find('option:selected').text() + "</center>");
            }
        });

        // Populate the select with sailor names (IDs as values)
        populateSelect(input);

        // Set the selected option based on the original ID
        input.val(originalID); // Make sure the original ID is selected
        if (input.val() === "") {
            input.val('').prop('selected', true); // Select the blank option if originalID is empty
        }

        $this.html(input);
        input.focus();
    });

    function updateWatchstander(cell, type, time, location, newID, originalID) {
        var data = {
            watch_type: type,
            time: time,
            date: date,
            location: location,
            new_id: newID || null // Set to null if newID is empty
        };

        // Log the data object to the console
        console.log('AJAX Data:', data);

        $.ajax({
            url: '/handlers/update_watchbill.php',
            type: 'POST',
            data: data,
            success: function(response) {
                if (response === "Success") {
                    if (newID === "") {
                        cell.html("<center>&nbsp;</center>"); // Blank out the cell if no ID is selected
                    } else {
                        // Find the selected option's text based on newID
                        var newName = cell.find('select option[value="' + newID + '"]').text();
                        cell.html("<center>" + newName.split(',')[0] + "</center>"); // Show only the last name
                    }
                    // Reset the signed status accordingly
                    resetSignedStatus(cell.closest('tr'), type, time);
                } else {
                    alert("Error updating the database.");
                }
            },
            error: function() {
                alert('Error updating the database');
                cell.html("<center>" + originalID + "</center>");
            }
        });
    }

    function populateSelect(input) {
        // Add a blank option at the top
        input.append($('<option>', {
            text: '',
            value: ''
        }));

        sailorsData.forEach(function(sailor) {
            input.append($('<option>', {
                text: sailor.last_name + ', ' + sailor.first_name,
                value: sailor.id // Assume sailor.id is the watch stander ID
            }));
        });
    }

    function resetSignedStatus(row, type, time) {
        var cellId = type === 'BAW' ? '#baw_int' : '#brw_int';
        var $toggleCell = row.find(cellId);

        $toggleCell.removeClass('green-bg').addClass('default-bg');
        $toggleCell.text('');

        if (type === 'BAW') {
            $toggleCell.data('baw_signed', 0);
        } else {
            $toggleCell.data('brw_signed', 0);
        }

        $.ajax({
            url: '/handlers/update_watchbill_signed.php',
            type: 'POST',
            data: {
                watch_type: type,
                time: time,
                location: row.find('.editable[data-type="' + type + '"]').data('location'),
                signed: 0
            },
            success: function(response) {
                if (response.includes("Success")) {
                    showAlert('The watchstander has been updated. Ensure that this watch is filled and/or signed for by a replacement.');
                } else {
                    alert("Error updating the database: " + response);
                }
            },
            error: function() {
                alert("AJAX call failed. Please try again.");
            }
        });
    }

    function showAlert(message) {
        var $alert = $('<div>', {
            text: message,
            class: 'alert bg-black animated fadeIn',
            css: {
                'position': 'fixed',
                'top': '20px',
                'left': '50%',
                'transform': 'translateX(-50%)',
                'z-index': '9999',
                'padding': '10px 20px',
                'color': 'white',
                'border-radius': '5px'
            }
        }).appendTo('body');

        setTimeout(function() {
            $alert.removeClass('fadeIn').addClass('fadeOut');
            setTimeout(function() {
                $alert.remove();
            }, 1000);
        }, 5000);
    }
});
