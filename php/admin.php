<?php
session_start();
include './php/db_connection.php';

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
    $file = $_FILES['file'];

    // Handle file upload
    if ($file['name']) {
        $targetDir = "uploads/";
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
                        $url = $targetFile;
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                }
            }
        }
    }

    if (!$error) {
        $sql = "INSERT INTO wallpapers (url, theme, category, device) VALUES ('$url', '$theme', '$category', '$device')";
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
    <header>
        <h1>Admin Panel</h1>
        <a href="../index.php">Back to Home</a>
    </header>
    <main>
        <h2>Add New Image</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST" action="admin.php" enctype="multipart/form-data">
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
                <label for="url">Image URL:</label>
                <input type="text" id="url" name="url">
            </div>
            <div class="input-wrapper">
                <label for="file">Or Upload File:</label>
                <input type="file" id="file" name="file">
            </div>
            <button type="submit">Add Image</button>
        </form>
    </main>
</body>
</html>