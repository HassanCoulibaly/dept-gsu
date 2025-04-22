<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="index.css" />
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
                <!--<i class="fa-solid fa-bars"></i>-->
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

    <main>
        <section id="hero">
            <h1>COMPUTER SCIENCE UNDERGRADUATE <br> RESEARCH OPPORTUNITIES</h1>
            <p>
                Department of Computer Science
            </p>
        </section>
    </main>

<section class="timeline-section">
    <h1>Where to Start?</h1>
	<div class="timeline-items">
		<div class="timeline-item">
			<div class="timeline-dot"></div>
			<div class="timeline-date">1</div>
			<div class="timeline-content">
				<h3>Explore Interesting Research</h3>
				<p>Start by looking at different research projects. Find something that catches your eye or matches what you're into. Check out project summaries and see which one you'd like to be a part of.</p>
			</div>
		</div>
		<div class="timeline-item">
			<div class="timeline-dot"></div>
			<div class="timeline-date">2</div>
			<div class="timeline-content">
				<h3>Learn About The Requirements</h3>
				<p>Look into the project details. Figure out what they need – the skills and things you should know. Make sure you match up with what the project is asking for.</p>
			</div>
		</div>
		<div class="timeline-item">
			<div class="timeline-dot"></div>
			<div class="timeline-date">3</div>
			<div class="timeline-content">
				<h3>Fill in the Application Form</h3>
				<p>Ready to jump in? Fill out the application form. Show them why you're a good fit. Share your experiences and why you're excited about the project.</p>
			</div>
		</div>
		<div class="timeline-item">
			<div class="timeline-dot"></div>
			<div class="timeline-date">4</div>
			<div class="timeline-content">
				<h3>How Will I Know if I am Selected?</h3>
				<p>You will receive a response if you are selected by a GSU STEM faculty member. Be sure to check your GSU student email often.</p>
			</div>
		</div>
		
		
	
	</div>
</section>

    
<footer>
    <div class="container">
        <div class="footer-content">
            <h3>Contact Us</h3>
            <p>Email: cscundergrad@gsu.edu</p>
            <p>Phone: +1 404-413-2000</p>
            <p>Address: 7th floor, 25 Park Place<br> Georgia State University <br> Atlanta, GA 30303</p>
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



</body>
</html>