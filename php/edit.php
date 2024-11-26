<?php
session_start();
include './db_connection.php';

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../index.php");
    exit();
}

$imageId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch image details
$sql = "SELECT * FROM wallo_wallpapers WHERE id = $imageId";
$result = $conn->query($sql);
$image = $result->fetch_assoc();

// Fetch collections
$collections = [];
$collections_result = $conn->query("SELECT id, name FROM wallo_collections");
if ($collections_result->num_rows > 0) {
    while ($row = $collections_result->fetch_assoc()) {
        $collections[] = $row;
    }
}

// Fetch image collections
$image_collections = [];
$image_collections_result = $conn->query("SELECT collection_id FROM wallo_image_collections WHERE image_id = $imageId");
if ($image_collections_result->num_rows > 0) {
    while ($row = $image_collections_result->fetch_assoc()) {
        $image_collections[] = $row['collection_id'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        // Handle image deletion
        $delete_sql = "DELETE FROM wallo_wallpapers WHERE id = $imageId";
        if ($conn->query($delete_sql) === TRUE) {
            $conn->query("DELETE FROM wallo_image_collections WHERE image_id = $imageId");
            header("Location: admin.php");
            exit();
        } else {
            $error = "Error deleting image: " . $conn->error;
        }
    } else {
        // Handle image update
        $device = $_POST['device'];
        $url = $_POST['url'];
        $tags = $_POST['tags'];
        $collections = isset($_POST['collections']) ? $_POST['collections'] : [];

        $update_sql = "UPDATE wallo_wallpapers SET url = '$url', device = '$device', tags = '$tags' WHERE id = $imageId";
        if ($conn->query($update_sql) === TRUE) {
            // Update image collections
            $conn->query("DELETE FROM wallo_image_collections WHERE image_id = $imageId");
            foreach ($collections as $collectionId) {
                $conn->query("INSERT INTO wallo_image_collections (image_id, collection_id) VALUES ($imageId, $collectionId)");
            }
            $success = "Image updated successfully.";
        } else {
            $error = "Error updating image: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <?php include './components/fontawesome.php'; ?>
    <link rel="stylesheet" href="../styles/dist/css/style.css">
    <title>Edit Image</title>
</head>
<body id="admin-page">
    <form id="edit-form" method="POST" action="edit.php?id=<?php echo $imageId; ?>" enctype="multipart/form-data" class="admin-form">
        <h2>Edit Image</h2>
        <a class="back-home" href="../index.php">Home preview</a>
        <?php if (isset($error)): ?>
          <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
          <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <div class="input-wrapper">
          <label for="device">Device:</label>
          <select id="device" name="device" required>
            <option value="mobile" <?php echo $image['device'] == 'mobile' ? 'selected' : ''; ?>>Mobile</option>
            <option value="tablet" <?php echo $image['device'] == 'tablet' ? 'selected' : ''; ?>>Tablet</option>
            <option value="desktop" <?php echo $image['device'] == 'desktop' ? 'selected' : ''; ?>>Desktop</option>
          </select>
        </div>
        <div class="input-wrapper">
          <label for="tags">Tags (comma-separated):</label>
          <input type="text" id="tags" name="tags" class="tags" value="<?php echo htmlspecialchars($image['tags']); ?>">
        </div>
        <div class="input-wrapper">
          <label for="url">Image URL:</label>
          <input type="text" id="url" name="url" value="<?php echo htmlspecialchars($image['url']); ?>">
        </div>
        <div class="input-wrapper">
          <label for="collections">Add to collection:</label>
          <select id="collections" name="collections[]" multiple required>
            <?php foreach ($collections as $collection): ?>
              <option value="<?php echo $collection['id']; ?>" <?php echo in_array($collection['id'], $image_collections) ? 'selected' : ''; ?>><?php echo htmlspecialchars($collection['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit">Update Image</button>
        <button type="submit" name="delete" class="delete-button" style="background-color: #ff4d4d;">Delete Image</button>
    </form>
</body>
</html>