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

// Handle image form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    $device = $_POST['device'];
    $url = $_POST['url'];
    $tags = $_POST['tags'];
    $collections = $_POST['collections'];
    $file = $_FILES['file'];

    // Handle file upload
    if ($file['name']) {
        $targetDir = __DIR__ . '/../assets/uploads/'; // Ensure the correct path for uploads
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create the uploads directory if it doesn't exist
        }
        $uniqueFilename = uniqid() . '-' . basename($file["name"]);
        $targetFile = $targetDir . $uniqueFilename;
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
                        $url = 'assets/uploads/' . $uniqueFilename; // Store relative path in the database
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
        $sql = "INSERT INTO wallo_wallpapers (id, url, theme, device, tags) VALUES ('$nextId', '$url', '$theme', '$device', '$tags')";
        if ($conn->query($sql) === TRUE) {
            // Insert into wallo_image_collections
            foreach ($collections as $collectionId) {
                $sql = "INSERT INTO wallo_image_collections (image_id, collection_id) VALUES ('$nextId', '$collectionId')";
                $conn->query($sql);
            }
            $success = "Image added successfully.";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Handle collection form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['collection-name'])) {
    $collectionName = $_POST['collection-name'];

    // Find the lowest available ID for the new collection
    $result = $conn->query("SELECT MIN(t1.id + 1) AS next_id FROM wallo_collections t1 LEFT JOIN wallo_collections t2 ON t1.id + 1 = t2.id WHERE t2.id IS NULL");
    $row = $result->fetch_assoc();
    $nextId = $row['next_id'] ?? 1; // Default to 1 if no rows are found

    // Insert the new collection with the lowest available ID
    $sql = "INSERT INTO wallo_collections (id, name) VALUES ('$nextId', '$collectionName')";
    if ($conn->query($sql) === TRUE) {
        $success = "Collection added successfully.";
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle collection deletion via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete-collection-ajax'])) {
    $collectionId = $_POST['collection-id'];

    // Delete the collection
    $sql = "DELETE FROM wallo_collections WHERE id = '$collectionId'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => "Error: " . $sql . "<br>" . $conn->error]);
    }
    exit();
}

// Handle user deletion via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete-user-ajax'])) {
    $userId = $_POST['user-id'];

    // Delete the user
    $sql = "DELETE FROM wallo_users WHERE id = '$userId'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => "Error: " . $sql . "<br>" . $conn->error]);
    }
    exit();
}

// Handle add/remove admin rights
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['user-id']) || isset($_POST['user-id-remove']))) {
    if (isset($_POST['user-id'])) {
        $userId = $_POST['user-id'];
        $sql = "UPDATE wallo_users SET is_admin = 1 WHERE id = '$userId' AND is_admin = 0";
        if ($conn->query($sql) === TRUE) {
            $success = "Admin rights added successfully.";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    if (isset($_POST['user-id-remove'])) {
        $userIdRemove = $_POST['user-id-remove'];
        $sql = "UPDATE wallo_users SET is_admin = 0 WHERE id = '$userIdRemove' AND is_admin = 1";
        if ($conn->query($sql) === TRUE) {
            $success = "Admin rights removed successfully.";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Fetch existing collections
$collections = [];
$result = $conn->query("SELECT id, name FROM wallo_collections");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $collections[] = $row;
    }
}

// Fetch existing users
$users = [];
$result = $conn->query("SELECT id, username, email, is_admin FROM wallo_users");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch existing wallpapers and their collections
$wallpapers = [];
$result = $conn->query("SELECT w.id, w.url, w.device, GROUP_CONCAT(c.name SEPARATOR ', ') AS collections
                        FROM wallo_wallpapers w
                        LEFT JOIN wallo_image_collections ic ON w.id = ic.image_id
                        LEFT JOIN wallo_collections c ON ic.collection_id = c.id
                        GROUP BY w.id, w.url, w.device");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $wallpapers[] = $row;
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
    <title>Admin - Add Images</title>
</head>
<body id="admin-page">
    <form id="admin-form" method="POST" action="admin.php" enctype="multipart/form-data" class="admin-form">
        <h2>Add New Image</h2>
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
        <div class="input-wrapper">
          <label for="collections">Add to collection:</label>
          <select id="collections" name="collections[]" multiple required>
            <?php foreach ($collections as $collection): ?>
              <option value="<?php echo $collection['id']; ?>"><?php echo htmlspecialchars($collection['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit">Add Image</button>
    </form>

    <form method="POST" action="admin.php" class="collection-form">
        <h2>Add New Collection</h2>
        <div class="input-wrapper">
          <label for="collection-name">Collection name:</label>
          <input type="text" name="collection-name" id="collection-name" required>
        </div>
        <button type="submit">Add Collection</button>
    </form>

    <form action="admin.php" class="collection-edit">
        <h2>Edit Collections</h2>
        <div class="existing-collections">
          <?php if (!empty($collections)): ?>
            <ul>
              <?php foreach ($collections as $collection): ?>
                <li>
                  <?php echo htmlspecialchars($collection['name']); ?>
                  <button type="button" class="delete-button" data-collection-id="<?php echo $collection['id']; ?>">
                    <i class="fa-sharp fa-solid fa-trash delete-user"></i>
                  </button>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>No collections found.</p>
          <?php endif; ?>
        </div>
    </form>

    <form action="" class="image-form">
        <h2>Edit Wallpapers</h2>
        <table>
          <thead>
            <tr>
              <th>Image</th>
              <th>Collections</th>
              <th>Device</th>
              <th>Edit</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($wallpapers as $wallpaper): ?>
            <tr>
              <td><img src="../<?php echo htmlspecialchars($wallpaper['url']); ?>?<?php echo time(); ?>" alt="image"></td>
              <td><?php echo htmlspecialchars($wallpaper['collections']); ?></td>
              <td><?php echo htmlspecialchars($wallpaper['device']); ?></td>
              <td><a href="./edit.php?id=<?php echo $wallpaper['id']; ?>">Edit</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
    </form>

    <form action="" class="user-form">
        <h2>Users</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Admin Status</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
              <td><?php echo htmlspecialchars($user['id']); ?></td>
              <td><?php echo htmlspecialchars($user['username']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
              <td>
                <button type="button" class="delete-user-button" data-user-id="<?php echo $user['id']; ?>">
                  <i class="fa-sharp fa-solid fa-trash delete-user"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
    </form>

    <form action="admin.php" method="POST" class="add-admin-form">
        <h2>Add/Remove Admin</h2>
        <div class="input-wrapper">
          <label for="user-id">User id (add admin rights):</label>
          <input type="text" name="user-id" id="user-id">
        </div>
        <div class="input-wrapper">
          <label for="user-id-remove">User id (remove admin rights):</label>
          <input type="text" name="user-id-remove" id="user-id-remove">
        </div>
        <button type="submit">Submit</button>
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

    // JavaScript to handle delete collection button click
    document.querySelectorAll('.delete-button').forEach(button => {
      button.addEventListener('click', function() {
        const collectionId = this.getAttribute('data-collection-id');
        if (confirm('Are you sure you want to delete this collection?')) {
          fetch('admin.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              'delete-collection-ajax': true,
              'collection-id': collectionId
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert('Error deleting collection: ' + data.error);
            }
          })
          .catch(error => {
            console.error('Error:', error);
          });
        }
      });
    });

    // JavaScript to handle delete user button click
    document.querySelectorAll('.delete-user-button').forEach(button => {
      button.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        if (confirm('Are you sure you want to delete this user?')) {
          fetch('admin.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              'delete-user-ajax': true,
              'user-id': userId
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert('Error deleting user: ' + data.error);
            }
          })
          .catch(error => {
            console.error('Error:', error);
          });
        }
      });
    });

    // JavaScript to handle add/remove admin rights form submission
    document.querySelector('.add-admin-form').addEventListener('submit', function(event) {
      event.preventDefault();
      const userId = document.getElementById('user-id').value;
      const userIdRemove = document.getElementById('user-id-remove').value;
      const formData = new FormData();
      if (userId) {
        formData.append('user-id', userId);
      }
      if (userIdRemove) {
        formData.append('user-id-remove', userIdRemove);
      }
      fetch('admin.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        location.reload();
      })
      .catch(error => {
        console.error('Error:', error);
      });
    });

    // JavaScript to handle form submissions and reload the page upon success
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(form);
        fetch(form.action, {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          location.reload();
        })
        .catch(error => {
          console.error('Error:', error);
        });
      });
    });
  </script>
</body>
</html>