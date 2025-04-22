<?php
// projects.php

include 'config.php'; // Include config.php to access $categories array

// Connect to MySQL
$servername = "127.0.0.1:3390";
$username = "root";
$password = "";
$dbname = "csresearch";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Your existing HTML and PHP code

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

  /* CSS for Filter Checkbox Container */

  .section-title {
    margin-bottom: 20px; /* Add margin below the section title */
  }
  .section-title + .row {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .section-title + .row form {
    margin-left: 10px; /* Add margin to the left of the form */
  }
  /* CSS for the search input */
  input[type="text"] {
    margin-top: 20px;
    padding: 10px; /* Add padding to the text input */
    font-size: 16px; /* Adjust font size */
    width: auto; /* Make input width auto */
  }
  button[type="submit"] {
    padding: 10px 20px; /* Add padding to the button */
    font-size: 16px; /* Adjust font size */
    background-color: #007bff; /* Change button background color */
    color: white; /* Change button text color */
    border: none; /* Remove button border */
    cursor: pointer; /* Add pointer cursor on hover */
    transition: background-color 0.3s; /* Add smooth transition */
  }
  button[type="submit"]:hover {
    background-color: #0056b3; /* Change button background color on hover */
  }
  @media (max-width: 768px) {
    .section-title + .row form {
      flex-direction: column; /* Change form direction to column on small screens */
      align-items: flex-start; /* Align items to the start of the column */
    }
    input[type="text"],
    button[type="submit"] {
      width: 100%; /* Make input and button width 100% */
    }
    button[type="submit"] {
      margin-top: 10px; /* Add margin on top of the button */
    }
  }

  /* CSS for the dropdown select */
  select[name="category"] {
    padding: 10px 20px; /* Add padding to match button */
    font-size: 16px; /* Adjust font size */
    background-color: #FFFFFF; /* Change background color */
    color: black; /* Change text color */
  }

  /* CSS for the filter dropdown select */
  .filter-dropdown select {
    padding: 10px 20px; /* Add padding to match button */
    font-size: 16px; /* Adjust font size */
    background-color: #FFFFFF; /* Change background color */
    color: black; /* Change text color */
  }

</style>


<section class="services">
  <div class="container">
    <div class="row">
      <div class="section-title">
        <h1>Projects</h1>
        <form method="GET" action="">
          <input type="text" name="search" placeholder="Search...">
          <button type="submit">Search</button>
        </form>

         <br>
        <!-- Filter Checkbox -->
        <!-- Filter Dropdown for Paid/Not Paid -->
          <div class="filter-dropdown">
            <form method="GET" action="">
              <select name="paid">
                <option value="">Paid Status</option>
                <option value="yes">Paid</option>
                <option value="no">Not Paid</option>
              </select>
              <button type="submit">Apply Filter</button>
            </form>
          </div>

    
        <br>
        <!-- Filter Dropdown for Categories -->
        <div class="filter-dropdown">
          <form method="GET" action="">
            <select name="category">
              <option value="">All Categories</option>
              <?php
                // Loop through categories array to populate options
                foreach ($categories as $category) {
                  echo "<option value=\"$category\">$category</option>";
                }
              ?>
            </select>
            <button type="submit">Apply Filter</button>
          </form>
        </div>

        <br>

      </div>
    </div>
      
    <div class="row">
      <div class="service-items">
        <div class="row">
          <?php
            // Fetch data from research table with search and filter
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $category = isset($_GET['category']) ? $_GET['category'] : '';
            $paid = isset($_GET['paid']) ? $_GET['paid'] : '';

            $sql = "SELECT * FROM research WHERE long_description LIKE '%$search%'";

            if ($category) {
              $sql .= " AND category = '$category'";
            }

            if ($paid) {
              $sql .= " AND paid = '$paid'";
            }
            

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                ?>
                <!-- item start -->
                <div class="item">
                  <div class="item-inner">
                    <img src="./success_image/<?= $row['image'] ?>" alt="<?= $row['title'] ?>">
                    <h3><?= $row['title'] ?></h3>
                    <p><?= $row['short_description'] ?></p>      
                    <div class="read-more-cont">
                      <p><strong>Description:</strong> <br> <?= $row['long_description'] ?></p>
                      <p><strong>Category:</strong> <br> <?= $row['category'] ?></p>
                      <p><strong>Requirement:</strong> <br> <?= $row['requirement'] ?></p>
                      <p><strong>Keyword:</strong> <br> <?= $row['keyword'] ?></p>
                      <p><strong>Paid:</strong> <br> <?= ucfirst($row['paid']) ?></p> 
                    </div>
                    <button class="btn" type="button">Read More</button>
                  </div>
                </div>
                <!-- item end -->
                <?php
              }
            } else {
              echo "No results found";
            }
          ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- read more popup box start -->
<div class="popup-box">
  <div class="popup-content">
    <div class="popup-header">
      <h3></h3>
      <span class="popup-close-icon">&times;</span>
    </div>
    <div class="popup-body">
      
    </div>
    <div class="popup-footer">
      <button class="btn popup-close-btn">Close</button>
      <a href="application.php"><button class="btn popup-close-btn">Apply</button></a> 
    </div>
  </div>
</div>
<!-- read more popup box end -->

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
                <li><a href="https://twitter.com/GeorgiaStateU?ref_src=twsrc%5Egoogle%7Ctwcamp%5Eserp%7Ctwgr%5Eauthor">Twitter</a></li>   
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
</body>
</html>

<?php
// Close database connection
$conn->close();
?>

