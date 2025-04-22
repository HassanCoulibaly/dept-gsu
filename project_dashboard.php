<?php
// Include database configuration
include 'config.php';

// Delete project if delete parameter is set
if(isset($_GET['delete'])){
   // Check if the confirmation code is correct
   if(isset($_POST['confirmation_code']) && $_POST['confirmation_code'] == '1234') {
       $id = $_GET['delete'];
       mysqli_query($conn, "DELETE FROM research WHERE id = $id");
       header('location:project_dashboard.php');
   } else {
       echo "<script>alert('Incorrect password or cancellation denied.');</script>";
   }
}

// Handle search query
if(isset($_GET['search'])){
    $search_query = $_GET['search'];
    $select = mysqli_query($conn, "SELECT * FROM research WHERE title LIKE '%$search_query%'");
} else {
    $select = mysqli_query($conn, "SELECT * FROM research");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Projects Dashboard</title>
   <link rel="stylesheet" href="project_dashboard.css">
</head>
<body>

<div class="container">
   <h2>Projects Dashboard</h2>
   <a href="add_new_project.php">
      <button>Add New Project</button>
   </a>
   <br>
   <br>
   <div class="search-section">
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by Title">
        <button type="submit">Search</button>
    </form>
   </div>
   <br>
   <div class="project-display">
      <table class="project-display-table">
      <thead>
    <tr>
        <th>Image</th>
        <th>Title</th>
        <th>Short Description</th>
        <th>Long Description</th>
        <th>Category</th>
        <th>Requirements</th>
        <th>Keywords</th>
        <th>Paid</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>
</thead>
<?php while($row = mysqli_fetch_assoc($select)){ ?>
<tr>
    <td> <img src="./success_image/<?php echo $row['image']; ?>" height="100" alt=""> </td>
    <td><?php echo $row['title']; ?></td>
    <td><?php echo $row['short_description']; ?></td>
    <td><?php echo $row['long_description']; ?></td>
    <td><?php echo $row['category']; ?></td> 
    <td><?php echo $row['requirement']; ?></td>
    <td><?php echo $row['keyword']; ?></td>
    <td><?php echo $row['paid']; ?></td>
    <td><a style="color: #fff;" href="edit_project.php?edit=<?php echo $row['id']; ?>" class="btn"><i class="fas fa-edit"></i> Edit </a></td>
    <td>
        <form id="delete_form_<?php echo $row['id']; ?>" method="POST" action="project_dashboard.php?delete=<?php echo $row['id']; ?>">
            <input type="hidden" name="confirmation_code" id="confirmation_code_<?php echo $row['id']; ?>">
            <a style="color: #fff;" href="#" class="btn" onclick="setConfirmationCode(<?php echo $row['id']; ?>)">
                <i class="fas fa-trash"></i> Delete
            </a>
        </form>
    </td>
</tr>
<?php } ?>

      </table>
      <br>
   </div>
   <a href="options.php">
      <button>Back</button>
   </a>
</div>

<script>
    function setConfirmationCode(id) {
        var confirmation_code = prompt("Please enter the 4-digit code:");
        if (confirmation_code === null) {
            // Cancellation denied
            alert("Cancellation denied.");
        } else if (confirmation_code === '1234') {
            document.getElementById("confirmation_code_" + id).value = confirmation_code;
            document.getElementById("delete_form_" + id).submit();
        } else {
            // Incorrect password
            alert("Incorrect password.");
        }
    }
</script>

</body>
</html>

