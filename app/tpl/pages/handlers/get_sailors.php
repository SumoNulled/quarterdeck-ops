<?php

try {
    // Create an instance of your database connection object
    $conn = new Database\MySQLi(); // Adjust this line according to your connection instantiation method

    // Query to fetch all sailors from the database
    $query = "SELECT * FROM sailors";
    $result = $conn->Query($query);

    // Check if the query was successful
    if ($result) {
        // Initialize an empty string to hold the HTML output
        $output = '';

        // Loop through each row returned by the query
        while ($row = $result->fetch_assoc()) {
            // Append a new table row to the output string for each sailor
            $output .= "<tr>";
            $output .= "<td>" . htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['middle_name'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['muster'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['schoolhouse'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['beq'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['room_number'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['phone_number'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['basic'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['secure'], ENT_QUOTES, 'UTF-8') . "</td>";
            $output .= "</tr>";
        }

        // Output the HTML content
        echo $output;

    } else {
        // Output an error message if the query failed
        echo "Error: Could not retrieve data.";
    }

} catch (Exception $e) {
    // Handle any exceptions
    echo "Error: " . $e->getMessage();
}
?>
