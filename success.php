<?php
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

// Fetch data from success_stories table
$sql = "SELECT * FROM success_stories";
$result = $conn->query($sql);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="success.css">
    <link href="images/gsulogo.png" rel="icon">
    <link href="images/gsulogo.png" rel="apple-touch-icon"> 
    <title>Computer Science Department</title>
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

    <h1>Success Stories</h1>

    <div class="card-container" id="cardContainer">
        <?php
        // Loop through fetched data and generate card HTML for each success story
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="card">';
                echo '<img src="success_image/' . $row['image_url'] . '" height="100" alt="">';
                echo '<div class="card-content">';
                echo '<h3>'.$row['title'].'</h3>';
                echo '<p>'.$row['story_text'].'</p>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "0 results";
        }
        ?>
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
                    <li><a href="https://news.gsu.edu/magazine-mailing-subscription/">GSU Magazine Subscription</a></li>
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

</body>
</html>

<?php
// Close database connection
$conn->close();
?>

