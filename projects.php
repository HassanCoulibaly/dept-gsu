<?php
session_start();
include 'config.php'; // Include config.php to access $categories array

// Connect to MySQL
$servername = "127.0.0.1:3390";
$username = "root";
$password = "";

$dbname = "cs_research_new";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Fetch Categories for the Category Filter Dropdown ---
// This block should fetch the category names from your 'categories' table
// to properly populate the filter dropdown.
$categories = []; // Initialize an empty array for category names
$sql_fetch_category_names = "SELECT name FROM categories ORDER BY name ASC";
$result_category_names = $conn->query($sql_fetch_category_names);

if ($result_category_names) {
    while ($row_cat_name = $result_category_names->fetch_assoc()) {
        $categories[] = $row_cat_name['name'];
    }
} else {
    // Handle error if categories cannot be fetched
    error_log("Error fetching categories for filter: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Projects</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="projects.css" type="text/css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="images/gsulogo.png" rel="icon">
  <link href="images/gsulogo.png" rel="apple-touch-icon">
</head>
<body>

<header>
  <div class="navbar">
    <div class="logo"><a href="index.php"><img style="height: 50px; width: 50px" src="images/headerlogo.png" alt=""></a></div>
    <ul class="links">
      <li><a href="index.php">Home</a></li>
      <li><a href="success.php">Success Stories</a></li>
      <li><a href="projects.php">Projects</a></li>
      <li><a href="https://forms.gle/oLbgRWchwJJgsayS8">Survey</a></li>
      <li><a href="admin.php">Admin</a></li>
    </ul>
    <div class="toggle_btn">
    <img style="width: 50px; height: 40px" src="images/icon1.png">
    </div>
  </div>

  <div class="dropdown_menu">
    <li><a href="index.php">Home</a></li>
    <li><a href="success.php">Success Stories</a></li>
    <li><a href="projects.php">Projects</a></li>
    <li><a href="https://forms.gle/oLbgRWchwJJgsayS8">Survey</a></li>
    <li><a href="admin.php">Admin</a></li>
  </div>
</header>

<script>
  const toggleBtn = document.querySelector('.toggle_btn')
  const toggleBtnIcon = document.querySelector('.toggle_btn i')
  const dropDownMenu = document.querySelector('.dropdown_menu')

  toggleBtn.onclick = function () {
      dropDownMenu.classList.toggle('open')
      const isOpen = dropDownMenu.classList.contains('open')

      toggleBtnIcon.classList = isOpen
      ? 'fa-solid fa-xmark'
      : 'fa-solid fa-bars'
  }
</script>

<style>
  .section-title {
    margin-bottom: 20px;
  }
  /* NEW: Container for all search and filter forms */
  .filter-controls {
    display: flex; /* Enables Flexbox for horizontal layout */
    flex-wrap: wrap; /* Allows items to wrap to the next line on smaller screens */
    gap: 15px; /* Adds space between each search/filter group */
    justify-content: center; /* Centers the whole group of filters horizontally */
    align-items: center; /* Vertically aligns items within the row */
    margin-top: 20px; /* Space above the filter row */
    margin-bottom: 30px; /* Space below the filter row */
  }

  /* NEW: Styles for individual form elements within the filter controls.
     Each form also uses flex to keep its input/select and button on one line. */
  .filter-controls form {
    display: flex;
    gap: 10px; /* Space between input/select and button within each form */
    align-items: center;
    margin: 0; /* Remove default form margins to prevent extra spacing */
  }

  /* MODIFIED/ADDED: These rules add some styling for better visual appearance */
  input[type="text"] {
    /* Keep your existing input[type="text"] styles, but ensure these are present */
    padding: 10px;
    font-size: 16px;
    width: auto;
    border: 1px solid #ccc; /* Added for better visual */
    border-radius: 5px; /* Added for better visual */
  }

  button[type="submit"] {
    /* Keep your existing button[type="submit"] styles, but ensure these are present */
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
    border-radius: 5px; /* Added for better visual */
  }
  button[type="submit"]:hover {
    background-color: #0056b3;
  }

  /* MODIFIED/ADDED: Original selectors for select elements, now including 'paid' */
  select[name="category"],
  .filter-dropdown select, /* This targets selects directly inside .filter-dropdown */
  select[name="paid"] { /* Explicitly target the paid select */
    padding: 10px 20px;
    font-size: 16px;
    background-color: #FFFFFF;
    color: black;
    border: 1px solid #ccc; /* Added for better visual */
    border-radius: 5px; /* Added for better visual */
  }

  /* MODIFIED: Responsive adjustments for smaller screens */
  @media (max-width: 768px) {
    .filter-controls {
      flex-direction: column; /* Stack filters vertically on small screens */
      align-items: stretch; /* Stretch items to fill width */
    }
    .filter-controls form {
      flex-direction: column; /* Stack input/select and button vertically */
      align-items: stretch; /* Stretch items to fill width */
    }
    input[type="text"],
    button[type="submit"],
    select[name="category"],
    select[name="paid"] {
      width: 100%;
    }
    button[type="submit"] {
      margin-top: 10px;
    }
  }
  select[name="category"], .filter-dropdown select {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #FFFFFF;
    color: black;
  }
  /* Style for the delete button */
  .btn.delete-btn {
      background-color: #dc3545; /* Red color for delete */
      margin-left: 10px; /* Space from edit button */
  }
  .btn.delete-btn:hover {
      background-color: #c82333;
  }
</style>

<section class="services">
  <div class="container">
    <div class="row">
      <div class="section-title">
        <h1>Projects</h1>
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
          <a href="add_new_project.php" class="btn">+ New Project</a>
        <?php endif; ?>
        <div class="filter-controls">
          <form method="GET" action="">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit">Search</button>
          </form>

          <div class="filter-dropdown">
            <form method="GET" action="">
              <select name="paid">
                <option value="">Paid Status</option>
                <option value="yes" <?= (isset($_GET['paid']) && $_GET['paid'] === 'yes') ? 'selected' : '' ?>>Paid</option>
                <option value="no" <?= (isset($_GET['paid']) && $_GET['paid'] === 'no') ? 'selected' : '' ?>>Not Paid</option>
              </select>
              <button type="submit">Apply Filter</button>
            </form>
          </div>

          <div class="filter-dropdown">
            <form method="GET" action="">
              <select name="category_name"> <option value="">All Categories</option>
                <?php
                foreach ($categories as $name) {
                    echo "<option value=\"" . htmlspecialchars($name) . "\"";
                    if (isset($_GET['category_name']) && $_GET['category_name'] === $name) {
                        echo " selected";
                    }
                    echo ">" . htmlspecialchars($name) . "</option>";
                }
                ?>
              </select>
              <button type="submit">Apply Filter</button>
            </form>
          </div>
        </div> </div>
    </div>
    <div class="row">
      <div class="service-items">
        <div class="row">
          <?php
            $search = $_GET['search'] ?? '';
            $category_filter = $_GET['category_name'] ?? ''; // Renamed to avoid conflict with $categories array
            $paid_filter = $_GET['paid'] ?? ''; // Renamed to avoid conflict with $paid variable

            $sql = "SELECT p.*, c.name as category_name FROM projects p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";

            $params = [];
            $param_types = "";

            if ($search) {
                $sql .= " AND (p.title LIKE ? OR p.short_description LIKE ? OR p.long_description LIKE ? OR p.requirements LIKE ? OR p.keywords LIKE ?)";
                $search_param = '%' . $search . '%';
                array_push($params, $search_param, $search_param, $search_param, $search_param, $search_param);
                $param_types .= "sssss";
            }
            if ($category_filter) {
                $sql .= " AND c.name = ?"; // Filter by category name
                array_push($params, $category_filter);
                $param_types .= "s";
            }
            if ($paid_filter !== '') { // Use !== '' to distinguish from NULL or empty string
                $paid_value = ($paid_filter === 'yes') ? 1 : 0;
                $sql .= " AND p.is_paid = ?";
                array_push($params, $paid_value);
                $param_types .= "i"; // 'i' for integer (boolean is often stored as tinyint/int)
            }

            // Prepare and execute the statement
            $stmt = $conn->prepare($sql);

            if ($stmt && !empty($params)) {
                mysqli_stmt_bind_param($stmt, $param_types, ...$params);
            }

            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = null; // Indicate an error if statement preparation failed
                echo "<p>Error preparing statement: " . $conn->error . "</p>";
            }


            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
          ?>
                <div class="item" data-project-id="<?= htmlspecialchars($row['id']) ?>">
                  <div class="item-inner">
                    <img src="./success_image/<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><?= htmlspecialchars($row['short_description']) ?></p>
                    <div class="read-more-cont">
                      <p><strong>Description:</strong> <br> <?= htmlspecialchars($row['long_description']) ?></p>
                      <p><strong>Category:</strong> <br> <?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></p> <p><strong>Requirement:</strong> <br> <?= htmlspecialchars($row['requirements']) ?></p>
                      <p><strong>Keyword:</strong> <br> <?= htmlspecialchars($row['keywords']) ?></p>
                      <p><strong>Paid:</strong> <br> <?= htmlspecialchars(ucfirst($row['is_paid'] ? 'yes' : 'no')) ?></p>
                    </div>
                    <button class="btn read-more-btn" type="button">Read More</button>

                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                      <div class="action-buttons">
                        <a href="edit_project.php?edit=<?= htmlspecialchars($row['id']) ?>" class="btn edit-btn">Edit</a>
                        <button type="button" class="btn delete-btn" onclick="confirmDelete(<?= htmlspecialchars($row['id']) ?>, this)">Delete</button>
                      </div>
                    <?php endif; ?>

                  </div>
                </div>
          <?php }
            } else {
              echo "<p>No results found</p>"; // Changed from "No results found" for consistency
            }
            if ($stmt) $stmt->close(); // Close the statement
          ?>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="popup-box">
  <div class="popup-content">
    <div class="popup-header">
      <h3></h3>
      <span class="popup-close-icon">&times;</span>
    </div>
    <div class="popup-body"></div>
    <div class="popup-footer">
      <button class="btn popup-close-btn">Close</button>
      <a href="application.php"><button class="btn popup-close-btn">Apply</button></a>
    </div>
  </div>
</div>

<footer>
  <div class="container">
    <div class="footer-content">
      <h3>Contact Us</h3>
      <p>Email: help@gsu.edu</p>
      <p>Phone: +404-413-2000</p>
      <p>Address: 33 Gilmer Street SE <br> Georgia State University <br> Atlanta, GA 30303</p>
    </div>
    <div class="footer-content">
      <h3>Quick Links</h3>
      <ul class="list">
        <li><a href="https://news.gsu.edu/">News</a></li>
        <li><a href="https://pin.gsu.edu/events">Panther Involvement Network Events</a></li>
        <li><a href="https://news.gsu.edu/magazine-mailing-subscription/">GSU Magazine Subsciption</a></li>
        <li><a href="">Social Media Directory</a></li>
        <li><a href="">Diversity and Inclusion</a></li>
      </ul>
    </div>
    <div class="footer-content">
      <h3>Follow Us</h3>
      <ul class="list">
        <li><a href="https://www.facebook.com/GeorgiaStateUniversity">Facebook</a></li>
        <li><a href="https://twitter.com/GeorgiaStateU">Twitter</a></li>
        <li><a href="https://www.instagram.com/georgiastateuniversity/?hl=en">Instagram</a></li>
        <li><a href="https://www.linkedin.com/school/georgiastateuniversity/">LinkedIn</a></li>
      </ul>
    </div>
  </div>
  <div class="bottom-bar">
    <p>&copy; 2024 <a href="https://gsu.edu/"><strong>Georgia State University</strong></a>. All rights reserved</p>
  </div>
</footer>

<script src="projects.js"></script>

<script>
// --- Custom Confirmation Dialog (Optional, for better UX than alert/confirm) ---
function showCustomConfirm(message, onConfirm, onCancel) {
    const existingModal = document.getElementById('custom-confirm-modal');
    if (existingModal) existingModal.remove(); // Remove any previous modal

    const modal