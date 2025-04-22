<?php
session_start(); // Start the session

// Check if user is logged in, if not, redirect to admin.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: admin.php");
    exit; // Terminate script after redirect
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script>
        function filterOptions() {
            var checkboxes = document.getElementsByName("filterOption");
            var rows = document.getElementsByTagName("tr");
            var showAll = true; // Assume showing all rows by default
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked && checkboxes[i].value !== "All") {
                    showAll = false; // If any option other than "All" is checked, we won't show all rows
                    break;
                }
            }
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var option = cells[5].innerHTML.split(', '); // Split options by comma and space
                var showRow = false;
                for (var j = 0; j < checkboxes.length; j++) {
                    if (checkboxes[j].checked && (checkboxes[j].value === "All" || option.includes(checkboxes[j].value))) {
                        showRow = true;
                        break;
                    }
                }
                if (showAll || showRow) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function saveChanges() {
            var rows = document.getElementsByTagName("tr");
            var saveData = [];
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var saveCheckbox = cells[6].getElementsByTagName("input")[0];
                var rowId = rows[i].getAttribute("data-row-id");
                var saveValue = saveCheckbox.checked ? 'Yes' : 'No'; // Set value based on checkbox state
                saveData.push({ id: rowId, save: saveValue });
            }
            // Send AJAX request to update database
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_save_column.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert("Changes saved successfully!");
                }
            };
            xhr.send(JSON.stringify(saveData));
        }


        function deleteRow(button) {
            var row = button.parentNode.parentNode;
            var rowId = row.getAttribute("data-row-id");

            // Prompt user for PIN
            var pin = prompt("Please enter the 4-digit PIN to confirm deletion:");

            // Check if PIN is correct
            if (pin === "1234") {
                // Send AJAX request to delete the row with the rowId
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_row.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Display popup message
                        alert(xhr.responseText);
                        // Remove the row from the table
                        row.parentNode.removeChild(row);
                    }
                };
                xhr.send("rowId=" + encodeURIComponent(rowId));
            } else {
                // Display "Incorrect PIN" message
                alert("Incorrect PIN. Deletion canceled.");
            }
        }


    </script>
</head>
<body>

<h2>Application Dashboard</h2>

<div class="container">
    <label>Filter by Research Project:</label>
    <br>
    <?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "Frankfurt018";
    $dbname = "csresearch";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query to retrieve distinct values from checkbox_options
    $sql = "SELECT DISTINCT checkbox_options FROM application";
    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
        echo '<br><input type="checkbox" name="filterOption" value="All" onchange="filterOptions()"> All<br>'; // All option
        while ($row = $result->fetch_assoc()) {
            echo '<br><input type="checkbox" name="filterOption" value="' . $row['checkbox_options'] . '" onchange="filterOptions()"> ' . $row['checkbox_options'] . '<br>'; // Checkbox option
        }
    } else {
        echo "No research projects found.";
    }



    $conn->close();
    ?>
    <br>
    <br>
    <button onclick="saveChanges()">Save Changes</button> <!-- Save button -->
    <br>
    <br>
    <!-- Add more checkboxes as needed -->
    <table>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Panther ID</th>
            <th>Email</th>
            <th>Resume</th>
            <th>Project</th>
            <th>Save</th> <!-- New Save column header -->
            <th>Why Hire Text</th>
            <th>Delete</th> <!-- New Delete column header -->
        </tr>

        <?php
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "Frankfurt018";
        $dbname = "csresearch";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // SQL query to retrieve data from the table
        $sql = "SELECT * FROM application";
        $result = $conn->query($sql);

        // Output data of each row
        while($row = $result->fetch_assoc()) {
            // Prepend two zeros if PantherID is not 9 digits
            $pantherid = $row["pantherid"];
            if (strlen($pantherid) != 9) {
                $pantherid = str_pad($pantherid, 9, '0', STR_PAD_LEFT);
            }

            echo "<tr data-row-id='".$row['id']."'>";
            echo "<td>".$row["first_name"]."</td>";
            echo "<td>".$row["last_name"]."</td>";
            echo "<td>".$pantherid."</td>";

            echo "<td><a href='mailto:".$row["email"]."' style='color: black; text-decoration: underline;'>
            <span style='transition: color 0.3s;' onmouseover='this.style.color=\"blue\"' onmouseout='this.style.color=\"black\"'>".$row["email"]."</span></a></td>";

            echo "<td><a href='data:application/pdf;base64,".base64_encode($row['upload_pdf'])."' download='uploaded_file.pdf'>Download Resume</a></td>";
            echo "<td>".$row["checkbox_options"]."</td>";
            echo "<td><input type='checkbox' name='filterSave' onchange='filterOptions()' ".($row["save"] == 'Yes' ? 'checked' : '')."></td>"; // Save checkbox
            echo "<td>".$row["why_hire_text"]."</td>";
            echo "<td><button onclick='deleteRow(this)'>Delete</button></td>"; // Delete button
            echo "</tr>";
        }
        $conn->close();
        ?>

    </table>
    <br>
    <a href="options.php">
      <button>Back</button>
   </a>

</div>
</body>
</html>


