<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

@include 'config.php';

// Get the ID of the product to be updated
$id = $_GET['edit'];

// Initialize message array
$message = [];

// Check if the form is submitted
if(isset($_POST['update_product'])){
   // Retrieve form data
   $title = $_POST['title'];
   $description = $_POST['description'];
   $image = $_FILES['image']['name'];
   $image_tmp_name = $_FILES['image']['tmp_name'];

   // Check if any field is empty
   if(empty($title) || empty($description)){
      $message[] = 'Please fill out all fields.';
   } else {
      // Prepare the update statement
      $update_statement = $conn->prepare("UPDATE success_stories SET title=?, description=? WHERE id=?");
      // Bind parameters
      $update_statement->bind_param("ssi", $title, $description, $id);

      // Execute the statement
      $upload = $update_statement->execute();

      if($upload){
         if(!empty($image)) {
            // Move uploaded image to the specified folder
            $image_folder = 'uploaded_img/'.$image;
            move_uploaded_file($image_tmp_name, $image_folder);

            // Update the image path in the database
            $update_statement = $conn->prepare("UPDATE success_stories SET image=? WHERE id=?");
            // Bind parameters
            $update_statement->bind_param("si", $image, $id);
            // Execute the statement
            $upload_image = $update_statement->execute();

            if(!$upload_image){
               $message[] = 'Could not update the image.';
            }
         }
         header('location:success_dashboard.php');
      } else {
         $message[] = 'Could not update the product.';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Product</title>
   <link rel="stylesheet" href="success_update.css">
   <style>
    /* Set width and prevent resizing */
    #description {
        width: 500px; /* Adjust width as needed */
        resize: vertical; /* Allow vertical resizing only */
    }
   </style>
   <script>
    function countCharacters(textareaId, maxCharacters) {
        var textarea = document.getElementById(textareaId);
        var characters = textarea.value.length;

        // Limit remaining characters to maxCharacters
        if (characters > maxCharacters) {
            textarea.value = textarea.value.substring(0, maxCharacters);
            characters = maxCharacters; // Update characters count
            alert("Maximum character limit reached");
        }

        // Update character count display
        var remainingCharacters = Math.max(0, maxCharacters - characters);
        document.getElementById(textareaId + "_char_count").innerHTML = remainingCharacters + " characters remaining";

        // Change color of character count based on remaining characters
        document.getElementById(textareaId + "_char_count").style.color = remainingCharacters === 0 ? "red" : "black";
    }
    </script>
</head>
<body>

<?php
// Display messages if any
if(isset($message)){
   foreach($message as $msg){
      echo '<span class="message">'.$msg.'</span>';
   }
}
?>

<div class="container">

   <div class="admin-product-form-container centered">

      <?php
         // Retrieve product data based on the ID
         $select = mysqli_query($conn, "SELECT * FROM success_stories WHERE id = '$id'");
         while($row = mysqli_fetch_assoc($select)){
      ?>
      
      <form action="" method="post" enctype="multipart/form-data">
         <h3 class="title">Update the Story</h3>
         <input type="text" class="box" name="title" value="<?php echo $row['title']; ?>" placeholder="Enter the product title">
         <span id="description_char_count" style="padding-left:10px; font-size: 12px; color: black;">1000 characters remaining</span>
         <textarea type="text" class="box" id="description" name="description" placeholder="Enter the product description" oninput="countCharacters('description', 1000)"><?php echo $row['description']; ?></textarea>
         <!--<input type="file" class="box" name="image" accept="image/png, image/jpeg, image/jpg">-->
         <input type="submit" value="Update Story" name="update_product" class="btn">
      </form>
   
      <?php }; ?>

   </div>

</div>

</body>
</html>




