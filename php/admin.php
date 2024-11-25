<?php
session_start();
include './db_connection.php'; // Corrected the path

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../index.php");
    exit();
}
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $theme = $_POST['theme'];
    $category = $_POST['category'];
    $device = $_POST['device'];
    $url = $_POST['url'];
    $tags = $_POST['tags'];
    $file = $_FILES['file'];

    // Handle file upload
    if ($file['name']) {
        $targetDir = __DIR__ . '/../assets/uploads/'; // Ensure the correct path for uploads
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create the uploads directory if it doesn't exist
        }
        $targetFile = $targetDir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            $error = "File is not an image.";
        } else {
            // Check file size (5MB max)
            if ($file["size"] > 5000000) {
                $error = "Sorry, your file is too large.";
            } else {
                // Allow certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                } else {
                    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
                        $url = 'assets/uploads/' . basename($file["name"]); // Store relative path in the database
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                }
            }
        }
    }

    if (!$error) {
        // Find the lowest available ID
        $result = $conn->query("SELECT MIN(t1.id + 1) AS next_id FROM wallo_wallpapers t1 LEFT JOIN wallo_wallpapers t2 ON t1.id + 1 = t2.id WHERE t2.id IS NULL");
        $row = $result->fetch_assoc();
        $nextId = $row['next_id'] ?? 1; // Default to 1 if no rows are found

        // Insert the new image with the lowest available ID
        $sql = "INSERT INTO wallo_wallpapers (id, url, theme, category, device, tags) VALUES ('$nextId', '$url', '$theme', '$category', '$device', '$tags')";
        if ($conn->query($sql) === TRUE) {
            $success = "Image added successfully.";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../styles/dist/css/style.css">
    <title>Admin - Add Images</title>
</head>
<body id="admin-page">
  <a class="icon-back" href="../index.php#home-page"><i class="fa-light fa-arrow-left"></i></a>
  <form id="admin-form" method="POST" action="admin.php" enctype="multipart/form-data">
      <h1>Add New Image</h1>
      <a class="back-home" href="../index.php">Home preview</a>
      <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
      <?php endif; ?>
      <?php if ($success): ?>
        <p class="success"><?php echo $success; ?></p>
      <?php endif; ?>
      <div class="input-wrapper">
        <label for="theme">Theme:</label>
        <input type="text" id="theme" name="theme" required>
      </div>
      <div class="input-wrapper">
        <label for="category">Category:</label>
        <input type="text" id="category" name="category" required>
      </div>
      <div class="input-wrapper">
        <label for="device">Device:</label>
        <select id="device" name="device" required>
          <option value="mobile">Mobile</option>
          <option value="tablet">Tablet</option>
          <option value="desktop">Desktop</option>
        </select>
      </div>
      <div class="input-wrapper">
        <label for="tags">Tags (comma-separated):</label>
        <input type="text" id="tags" name="tags" class="tags">
      </div>
      <div class="input-wrapper">
        <label for="url">Image URL:</label>
        <input type="text" id="url" name="url">
      </div>
      <div class="input-wrapper">
        <label for="file">Or Upload File:</label>
        <input type="file" id="file" name="file">
        <label for="file" class="file-label">Choose a file</label>
        <span id="file-name"></span>
      </div>
      <button type="submit">Add Image</button>
    </form>
    <script>
      document.getElementById('file').addEventListener('change', function() {
        const fileName = this.files[0].name;
        document.getElementById('file-name').textContent = fileName;
      });

      // Reset the form after a successful submission
      if (<?php echo json_encode($success); ?>) {
        document.getElementById('admin-form').reset();
        document.getElementById('file-name').textContent = '';
      }
    </script>
</body>
</html>