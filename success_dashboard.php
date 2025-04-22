<?php
@include 'config.php';

// Delete product if delete parameter is set
if(isset($_GET['delete'])){
   $id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM success_stories WHERE id = $id");
   header('location:success_dashboard.php');
}

// Handle search query
if(isset($_GET['search'])){
    $search_query = $_GET['search'];
    $select = mysqli_query($conn, "SELECT * FROM success_stories WHERE title LIKE '%$search_query%'");
} else {
    $select = mysqli_query($conn, "SELECT * FROM success_stories");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Product Table</title>
   <link rel="stylesheet" href="success_dashboard.css">
</head>
<body>

<div class="container">
   <h2>Success Stories Dashboard</h2>
   <a href="add_new_story.php">
      <button>Add New Story</button>
   </a>
   <br>
   <br>
   <div class="search-section">
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by Name">
        <button type="submit">Search</button>
    </form>
   </div>
   <br>
   <div class="product-display">
      <table class="product-display-table">
         <thead>
            <tr>
               <th>Image</th>
               <th>Name</th>
               <th>Story</th>
               <th>Edit</th>
               <th>Delete</th> <!-- New column for delete button -->
            </tr>
         </thead>
         <?php while($row = mysqli_fetch_assoc($select)){ ?>
         <tr>
            <td> <img src="success_image/<?php echo $row['image']; ?>" height="100" alt=""> </td>
            <td><?php echo $row['title']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td><a style="color: #fff;" href="success_update.php?edit=<?php echo $row['id']; ?>" class="btn"><i class="fas fa-edit"></i> Edit </a></td>
            <td><button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn"> <i class="fas fa-trash"></i> Delete </button></td>
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
   function confirmDelete(id) {
      var code = prompt("Please enter the 4-digit code to delete the story:");
      if (code === "1234") {
         window.location.href = "success_delete.php?delete=" + id;
      } else {
         alert("Incorrect password. Deletion canceled.");
      }
   }
</script>

</body>
</html>

