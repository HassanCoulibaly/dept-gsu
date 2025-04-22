<!DOCTYPE html>
<html>
<head>
    <title>Application Form</title>
    <link rel="stylesheet" href="application.css">
</head>
<body>

<div class="form-container"> <!-- Added form container -->

<form action="submit_application.php" method="post" enctype="multipart/form-data">
    <h2>Application Form</h2>
    <label for="first_name">First Name:</label><br><br>
    <input type="text" id="first_name" name="first_name" required><br><br>

    <label for="last_name">Last Name:</label><br><br>
    <input type="text" id="last_name" name="last_name" required><br><br>

    <label for="email">Student Email:</label><br><br>
    <input type="text" id="email" name="email" required><br><br>

    <label for="pantherid">PantherID:</label><br><br>
    <input type="text" id="pantherid" name="pantherid" required><br><br>

    <label for="upload_pdf">Upload Resume:</label><br><br>
    <input type="file" id="upload_pdf" name="upload_pdf" accept=".pdf" required><br><br>

    <label for="checkbox_options">Which project you want to apply for?:</label><br><br>

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

    // Fetch options from research table
    $sql = "SELECT title FROM research";
    $result = $conn->query($sql);

    // Output radio buttons
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<input type="radio" id="' . $row['title'] . '" name="checkbox_options" value="' . $row['title'] . '">';
            echo '<label for="' . $row['title'] . '">' . $row['title'] . '</label><br>';
        }
    } else {
        echo "No projects found.";
    }

    $conn->close();
    ?>

    <br>
    <br>
    <label for="why_hire_text">Why Should We Hire You? (max 255 characters):</label><br><br>
    <textarea id="why_hire_text" name="why_hire_text" rows="4" cols="50" maxlength="255" required></textarea>
    <br>
    <span id="charCount">0/255</span><br><br>

    <input type="submit" value="Submit Application">
</form>

</div> <!-- Closing form container -->



<script>
var emailAlertShown = false; // Flag to track if email alert has been shown

// JavaScript to limit PantherID to 9 numbers
document.getElementById("pantherid").addEventListener("input", function() {
    this.value = this.value.slice(0, 9); // Limit to 9 characters
});

document.getElementById("email").addEventListener("blur", function() {
    var email = this.value;
    var validDomains = ["@gsu.edu", "@student.gsu.edu"];
    var valid = false;
    for (var i = 0; i < validDomains.length; i++) {
        if (email.endsWith(validDomains[i])) {
            valid = true;
            break;
        }
    }
    if (!valid) {
        if (!emailAlertShown) { // Only show alert if it hasn't been shown before
            alert("Email not valid. Only @gsu.edu or @student.gsu.edu emails are allowed.");
            emailAlertShown = true; // Set flag to true
        }
        this.value = ""; // Clear the input field
        this.focus(); // Focus back on the input field
    }
});

// JavaScript to count characters in the textarea
document.getElementById("why_hire_text").addEventListener("input", function() {
    var text = this.value;
    var maxLength = 255;
    var currentLength = text.length;
    document.getElementById("charCount").textContent = currentLength + "/" + maxLength;
});

// JavaScript to limit PantherID to only 9 digits
document.getElementById("pantherid").addEventListener("input", function() {
    var pantherID = this.value;
    var maxLength = 9;
    if (pantherID.length > maxLength) {
        this.value = pantherID.slice(0, maxLength);
    }
});
</script>




</body>
</html>




