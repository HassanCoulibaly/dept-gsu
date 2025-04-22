<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Three Boxes</title>
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }
    
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    
    .box {
        width: 250px;
        height: 250px;
        background-color: #f0f0f0;
        margin: 0 10px;
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: background-color 0.3s ease;
        border-radius: 15px; /* Curved corners */
    }
    
    .box h2 {
        margin: 0;
        text-decoration: none; /* Remove underline */
        color: inherit; /* Inherit text color from parent */
    }

    .box:hover {
        background-color: #007bff; /* Blue color */
    }

    a {
        color: inherit;
        text-decoration: none; 
    }

    /* Mobile Responsive */
    @media only screen and (max-width: 600px) {
        .container {
            flex-direction: column;
        }
        .box {
            width: calc(100% - 20px);
            margin: 10px 0;
        }
    }
</style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="box">
        <h2>Dashboard</h2>
    </a>
    <a href="success_dashboard.php" class="box">
        <h2>Success Stories</h2>
    </a>
    <a href="project_dashboard.php" class="box">
        <h2>Projects</h2>
    </a>
    <a href="index.php" class="box" style="background-color: #007bff;">
        <h2 style="color: #fff;">Back</h2>
    </a>
</div>

</body>
</html>




