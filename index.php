<?php
session_start();
include './php/db_connection.php';

// Fetch wallpapers
$sql = "SELECT url, device FROM wallpapers";
$result = $conn->query($sql);

$username = isset($_SESSION['Username']) ? $_SESSION['Username'] : 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="./assets/favicon.ico">
  <?php include './php/components/fontawesome.php'; ?>
  <link rel="stylesheet" href="./styles/dist/css/style.css">
  <title>Wallo</title>
</head>
<body data-signed-in="<?php echo isset($_SESSION['ID']) ? 'true' : 'false'; ?>">

  <header>
    <div class="title-wrapper">
      <img src="./assets/wallo_logo.png" alt="logo">
      <h1 class="title">Wallo</h1>
    </div>
  
    <div class="custom-select">
      <div class="select-selected" data-value="all">All</div>
      <div class="select-items select-hide">
        <div data-value="all">All</div>
        <div data-value="mobile">Mobile</div>
        <div data-value="tablet">Tablet</div>
        <div data-value="desktop">Desktop</div>
      </div>
    </div>
  </header>

  <nav>
    <a href="#" class="collections" data-target="collection-page"><i class="fa-sharp fa-light fa-album-collection"></i></a>
    <a href="#" class="search" data-target="search-page"><i class="fa-sharp fa-light fa-magnifying-glass"></i></a>
    <a href="#" class="home active" data-target="home-page"><i class="fa-sharp fa-light fa-house"></i></a>
    <a href="#" class="settings" data-target="settings-page"><i class="fa-light fa-gear"></i></a>
    <a href="#" class="profile" id="profile-link" data-target="profile-page"><i class="fa-light fa-user"></i></a>
  </nav>

  <main>
    <div id="collection-page"></div>

    <div id="search-page"></div>

    <div id="home-page">
      <div class="main-container">
        <div class="image-container" id="image-container">
        <?php
        if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            echo '<img src="' . htmlspecialchars($row["url"]) . '" data-device="' . htmlspecialchars($row["device"]) . '" alt="wallpaper" loading="eager">';
          }
        } else {
          echo "No wallpapers found.";
        }
        $conn->close();
        ?>
        </div>
      </div>
    </div>

    <div id="settings-page"></div>

    <div id="profile-page">
      <div class="container">
        <div class="saved-items">
          <h2><i class="fa-sharp fa-solid fa-heart"></i> Saved wallpapers</h2>
          <div class="saved-items-container"></div>
        </div>
        <div class="welcome-message">
          <h1>Welcome <?php echo htmlspecialchars($username); ?>, <a href="./php/logout.php" class="logout-btn">Logout</a></h1>
        </div>
        <div class="user-info">
          <h2>Change your login data:</h2>
        </div>
      </div>
    </div>
  </main>
  
  <script src="./js/general.js"></script>
  <script src="./js/image.js"></script>
</body>
</html>