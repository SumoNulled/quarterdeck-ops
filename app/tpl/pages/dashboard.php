<!DOCTYPE html>
<html>
   <?php
      // Get the database connection
      $conn = new \Database\MySQLi();
      $timeslots = new \WatchBill\TimeSlots($conn);

      $date = new DateTime('2024-09-13');
      $dutyWatchbill = new \WatchBill\DutyWatchbillRenderer($conn, $date);

      $dayOfWeek = $date->format('N');
      $timeSlotCode = ($dayOfWeek >= 6) ? "WE" : "WD";

      // Fetch time slot assignments
      $timeSlotAssignmentsList = $dutyWatchbill->getTimeSlotAssignments($timeSlotCode);

      // Fetch table data for left and right columns
      $tables = $dutyWatchbill->fetchTables();

      $watchbill = new \WatchBill\DutyWatchbill($conn, $date);
      //$watchbill->emptyWatchBill();

      \Loaders\Includes::includeFile('html_head');
      ?>
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Cedarville+Cursive&display=swap" rel="stylesheet">
   <!-- Bootstrap Select Css -->
   <link href="../../plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />
   <!-- Multi Select Css -->
   <link href="../../plugins/multi-select/css/multi-select.css" rel="stylesheet">
   <!-- JQuery DataTable Css -->
   <link href="../../plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
   <style>
      table {
      table-layout: fixed;  /* Fix table layout to respect the width of the table and cells */
      width: 100%;          /* Set the width of the table (or a specific value) */
      font-size: 10px;
      }
      .editWatchbill {
      border: none;
      background-color: none;
      text-align: center;
      width: 100%;
      }
      select {
      -webkit-appearance: none; /* Removes the dropdown arrow in WebKit browsers (Chrome, Safari) */
      -moz-appearance: none; /* Removes the dropdown arrow in Firefox */
      appearance: none; /* Removes the dropdown arrow in other browsers */
      background: transparent; /* Ensures no background is visible */
      border: none; /* Removes default border */
      }
      .default-bg {
      background-color: #fcfbed; /* Default background color */
      text-align: center;
      }
      .green-bg {
      background-color: inherit;
      text-align: center;
      font-family: "Cedarville Cursive", cursive;
      }
   </style>
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
              <!-- Metarial Design Buttons -->
              <div class="row clearfix">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="card">
                          <div class="header">
                              <h2>
                                  MANAGE WATCHBILL
                                  <?php // var_dump($watchbill->getSailorsWithoutWatches()); ?>
                                  <small>Use any of the available button classes to quickly create a styled button</small>
                              </h2>
                              <ul class="header-dropdown m-r--5">
                                  <li class="dropdown">
                                      <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                          <i class="material-icons">more_vert</i>
                                      </a>
                                      <ul class="dropdown-menu pull-right">
                                          <li><a href="javascript:void(0);">Action</a></li>
                                          <li><a href="javascript:void(0);">Another action</a></li>
                                          <li><a href="javascript:void(0);">Something else here</a></li>
                                      </ul>
                                  </li>
                              </ul>
                          </div>
                          <div class="body">
                              <div class="button-demo">
                                <button type="button" class="btn bg-blue waves-effect" id="fillWatchbillBtn">FILL WATCHBILL</button>
                                <button type="button" class="btn bg-red waves-effect" id="emptyWatchbillBtn">EMPTY WATCHBILL</button>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              <!-- #END# Metarial Design Buttons -->

               <div class="row clearfix">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                     <div class="card">
                        <div class="body">
                           <div class="table-responsive">
                              <table class="table table-bordered table-striped table-hover">
                                 <thead>
                                    <tr>
                                       <th colspan=2>
                                          <center>Section</center>
                                       </th>
                                       <th class="sl-column" style="background-color: #faead4">534 SL</th>
                                       <th colspan=2>ITS1 Dougherty</th>
                                       <th class="sl-column" style="background-color: #dfebf7">492 SL</th>
                                       <th>TM1 Floyd</th>
                                       <th class="sl-column">435 SL</th>
                                       <th>EMN1 Todd</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <tr>
                                       <td colspan="2" style="text-align: center; vertical-align: middle;">
                                          <?php echo strtoupper($date->format('dMy')); ?>, FOXTROT<br/><small><?php echo $dayOfWeek . ' Version';?></small>
                                       </td>
                                       <td colspan="7" style="padding: 0; margin: 0; height: 100%;">
                                          <table class="table table-bordered table-striped table-hover" style="margin: 0; padding: 0; height: 100%; width: 100%;">
                                             <tr>
                                                <td style="vertical-align: top; height: 50%; background-color: #edfcf1">
                                                   DUTY SECTION MUSTERS HELD AT 492 / 534 WEEKENDS: 0830 / 1300 / 1900 / 0600
                                                </td>
                                             </tr>
                                             <tr>
                                                <td style="vertical-align: top; height: 50%; background-color: #edfcf1">
                                                   CORRESPONDING COLUMNS DENOTE CORRECTIONS
                                                </td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td colspan="9" style="background-color: #edfcf1">
                                          <center>RETURN WATCHBILL AND RECALL TO DUTY SECTION BINDER UPON COMPLETION OF DUTY DAY TO ENSURE CORRECTIONS ARE ENTERED.</center>
                                       </td>
                                    </tr>
                                    <tr>
                                       <!-- Left column for tables with 'responsible' value 534 -->
                                       <td colspan="4" style="padding: 0;">
                                          <?php $dutyWatchbill->renderTables($tables['leftTables'], $timeSlotAssignmentsList); ?>
                                       </td>
                                       <!-- Right column for tables with 'responsible' value 492 -->
                                       <td colspan="5" style="padding: 0;">
                                          <?php $dutyWatchbill->renderTables($tables['rightTables'], $timeSlotAssignmentsList); ?>
                                       </td>
                                    </tr>
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
      <?php
         // Fetch the sailors from the database
         $sailors = $conn->Rows("SELECT id, last_name, first_name FROM sailors");

         // Encode sailors array to JSON to use it in JavaScript
         $sailors_json = json_encode($sailors);
         $jsDate = $date->format('Y-m-d');
         ?>
      <script>
         var sailorsData = <?php echo json_encode($sailors); ?>; // Assumes $sailors_json was previously generated
         var date = "<?php echo $jsDate; ?>";
      </script>
      <script src="/scripts/update_watchbill.js"></script>
      <script src="/scripts/update_watchbill_signatures.js"></script>
      <script>
        $(document).ready(function() {
          // Fill Watchbill
          $('#fillWatchbillBtn').click(function() {
            $.ajax({
              url: '/handlers/watchbill_actions.php',
              type: 'POST',
              data: { action: 'fill', date: date },
              success: function(response) {
                alert(response); // You can update this to dynamically update the table instead of an alert
                location.reload(); // Reload the page to show the updated watchbill (optional)
              },
              error: function(xhr, status, error) {
                console.error(xhr);
                alert('An error occurred.');
              }
            });
          });

          // Empty Watchbill
          $('#emptyWatchbillBtn').click(function() {
            $.ajax({
              url: '/handlers/watchbill_actions.php',
              type: 'POST',
              data: { action: 'empty' },
              success: function(response) {
                alert(response); // You can update this to dynamically update the table instead of an alert
                location.reload(); // Reload the page to show the updated watchbill (optional)
              },
              error: function(xhr, status, error) {
                console.error(xhr);
                alert('An error occurred.');
              }
            });
          });
        });
      </script>
   </body>
</html>
