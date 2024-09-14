<!DOCTYPE html>
<html>

<?php
// Get the database connection
$conn = new \Database\MySQLi();

\Loaders\Includes::includeFile('html_head');
?>
<!-- Bootstrap Select Css -->
<link href="../../plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<body class="theme-black">
  <?php
    \Loaders\Includes::includeFile('page_loader');
    \Loaders\Includes::includeFile('overlay_for_sidebars');
    \Loaders\Includes::includeFile('search_bar');
    \Loaders\Includes::includeFile('top_bar');
    \Loaders\Includes::includeFile('sidebars');
  ?>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
              <!-- Input -->
              <div class="row clearfix">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="card">
                          <div class="body">
                            <div class="row clearfix">
                              <form id="addSailorForm" name="addSailor" action="/handlers/view.php" method="POST">
                              <?php
                              // Usage Example
                              $formElement = new \HTML\FormElement();
                              // Define the options array
                              $schoolhouse_options = $conn->getSetValues('schoolhouse');
                              // Render inputs for each field
                              $formElement->renderInput("last_name", "Last Name");
                              $formElement->renderInput("first_name", "First Name");
                              $formElement->renderInput("middle_name", "Middle Initial");
                              $formElement->renderInput("muster", "Muster");
                              $formElement->renderSelect('schoolhouse', $schoolhouse_options, '20', 'Schoolhouse');
                              $formElement->renderInput("beq", "BEQ");
                              $formElement->renderInput("room_number", "ROOM");
                              $formElement->renderInput("phone_number", "Phone");
                              $formElement->renderInput("basic_qualified", "Basic");
                              $formElement->renderInput("secure_qualified", "Secure");
                               ?>
                               <input class="form-control" style="display: none;" type="submit" />
                             </form>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              <!-- #END# Input -->
              <div class="row clearfix">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="card">
                          <div class="body">
                            <div class="table-responsive">
                              <table class="table table-bordered table-striped table-hover dataTable js-exportable">
                                  <thead>
                                      <tr>
                                          <th>Last Name</th>
                                          <th>First Name</th>
                                          <th>Middle Initial</th>
                                          <th>Muster</th>
                                          <th>School/hours</th>
                                          <th>BEQ</th>
                                          <th>ROOM</th>
                                          <th>Phone</th>
                                          <th>Basic</th>
                                          <th>Secure</th>
                                      </tr>
                                  </thead>
                                  <tbody id="sailorTableBody">
                                      <?php
                                      try
                                      {
                                      foreach ($conn->Rows("SELECT * FROM sailors") as $row)
                                      {
                                          echo "<tr>";
                                          echo "<td>{$row['last_name']}</td>";
                                          echo "<td>{$row['first_name']}</td>";
                                          echo "<td>{$row['middle_name']}</td>";
                                          echo "<td>{$row['muster']}</td>";
                                          echo "<td>{$row['schoolhouse']}</td>";
                                          echo "<td>{$row['beq']}</td>";
                                          echo "<td>{$row['room_number']}</td>";
                                          echo "<td>{$row['phone_number']}</td>";
                                          echo "<td>{$row['basic_qualified']}</td>";
                                          echo "<td>{$row['secure_qualified']}</td>";
                                          echo "</tr>";
                                      }
                                      } catch (Exception $e) {
                                        // Handle any exception that might occur
                                        echo "Error: " . $e->getMessage(); // Display error message
                                      }
                                      ?>
                                  </tbody>
                              </table>
                          </div>

                          </div>
                      </div>
                  </div>
              </div>

            </div>
        </div>
    </section>

<?php \Loaders\Includes::includeFile('base_scripts'); ?>

<script>
$(document).ready(function () {
    $('#addSailorForm').on('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        $.ajax({
            type: 'POST',
            url: '/handlers/view.php', // Server-side script to handle form submission
            data: $(this).serialize(), // Serialize the form data
            success: function (response) {
                // Process the response (e.g., show success message)
                console.log('Form submitted successfully:', response);

                // Refresh the table to show the updated data
                refreshTable();

                // Clear the form fields
                $('#addSailorForm')[0].reset();
            },
            error: function (xhr, status, error) {
                // Handle any errors that occur during the AJAX request
                console.error('Error submitting form:', error);
            }
        });
    });

    function refreshTable() {
        $.ajax({
            type: 'GET',
            url: '/handlers/get_sailors.php', // Server-side script to fetch updated table data
            success: function (data) {
                $('#sailorTableBody').html(data); // Replace the table body with new data
            },
            error: function (xhr, status, error) {
                console.error('Error fetching table data:', error);
            }
        });
    }
});
</script>


<!-- Select Plugin Js -->
<script src="../../plugins/bootstrap-select/js/bootstrap-select.js"></script>

</body>

</html>
