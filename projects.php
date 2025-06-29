<?php
session_start();
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
  .section-title + .row {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .section-title + .row form {
    margin-left: 10px;
  }
  input[type="text"] {
    margin-top: 20px;
    padding: 10px;
    font-size: 16px;
    width: auto;
  }
  button[type="submit"] {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  button[type="submit"]:hover {
    background-color: #0056b3;
  }
  @media (max-width: 768px) {
    .section-title + .row form {
      flex-direction: column;
      align-items: flex-start;
    }
    input[type="text"],
    button[type="submit"] {
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
</style>

<section class="services">
  <div class="container">
    <div class="row">
      <div class="section-title">
        <h1>Projects</h1>
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
          <a href="add_new_project.php" class="btn">+ New Project</a>
        <?php endif; ?>
        <form method="GET" action="">
          <input type="text" name="search" placeholder="Search...">
          <button type="submit">Search</button>
        </form>

        <br>
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
        <div class="filter-dropdown">
          <form method="GET" action="">
            <select name="category">
              <option value="">All Categories</option>
              <?php foreach ($categories as $category) echo "<option value=\"$category\">$category</option>"; ?>
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
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $paid = $_GET['paid'] ?? '';

            $sql = "SELECT * FROM research WHERE long_description LIKE '%$search%'";
            if ($category) $sql .= " AND category = '$category'";
            if ($paid) $sql .= " AND paid = '$paid'";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
          ?>
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

                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                      <div class="action-buttons">
                        <a href="edit_project.php?edit=<?= $row['id'] ?>" class="btn edit-btn">Edit</a>
                        <a href="delete_project.php?delete=<?= $row['id'] ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this project?');">Delete</a>
                      </div>
                    <?php endif; ?>

                  </div>
                </div>
          <?php }
            } else {
              echo "No results found";
            }
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
</body>
</html>

<?php $conn->close(); ?>
