<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin.css">
   
</head>
<body>

<div class="form-container">
    <form action="submit_login.php" method="post" enctype="multipart/form-data">
        <h2>Login</h2>

        <label for="email">Email:</label><br><br>
        <input type="text" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br><br>
        <input type="text" id="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>
</div>

</body>
</html>
