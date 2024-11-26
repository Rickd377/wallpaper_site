<?php
session_start();
include './php/db_connection.php';

// Fetch wallpapers
$sql = "SELECT url, device FROM wallo_wallpapers";
$result = $conn->query($sql);

// Fetch collections and a random image from each collection
$collections_sql = "
    SELECT c.id, c.name, (
        SELECT w.url 
        FROM wallo_wallpapers w
        JOIN wallo_image_collections ic ON w.id = ic.image_id
        WHERE ic.collection_id = c.id
        ORDER BY RAND()
        LIMIT 1
    ) AS url
    FROM wallo_collections c
";
$collections_result = $conn->query($collections_sql);

// Determine user state
$isSignedIn = isset($_SESSION['ID']);
$username = $isSignedIn ? $_SESSION['Username'] : 'Guest';
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
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
<body class="<?php echo $isSignedIn ? 'logged-in' : 'logged-out'; ?>" data-signed-in="<?php echo $isSignedIn ? 'true' : 'false'; ?>">

  <header>
    <div class="title-wrapper">
      <a href="index.php#home-page">
        <img src="./assets/wallo_logo.png" alt="logo">
      </a>
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
    <a href="#" class="ai" data-target="ai-page"><i class="fa-light fa-microchip-ai"></i></a>
    <?php if ($isSignedIn): ?>
      <a href="#" class="profile" id="profile-link" data-target="profile-page"><i class="fa-light fa-user"></i></a>
      <?php if ($isAdmin): ?>
        <a href="./php/admin.php" class="admin-link"><i class="fa-light fa-user-shield"></i></a>
      <?php endif; ?>
    <?php else: ?>
      <a href="./php/register.php" class="profile" id="profile-link"><i class="fa-light fa-user"></i></a>
    <?php endif; ?>
  </nav>

  <main>
    <!-- Collection Page -->
    <div id="collection-page">
      <div class="main-container">
        <div class="collections-container">
          <?php if ($collections_result->num_rows > 0): ?>
            <?php while($collection = $collections_result->fetch_assoc()): ?>
              <div class="collection-item" data-collection-id="<?php echo $collection['id']; ?>" style="background-image: url('<?php echo htmlspecialchars($collection['url']); ?>');">
                <h3><?php echo htmlspecialchars($collection['name']); ?></h3>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p>No collections found.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Search Page -->
    <div id="search-page">
      Search page
    </div>

    <!-- Home Page -->
    <div id="home-page">
      <div class="main-container">
        <div class="image-container" id="image-container">
          <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <img src="<?php echo htmlspecialchars($row['url']); ?>" data-device="<?php echo htmlspecialchars($row['device']); ?>" alt="wallpaper" loading="eager">
            <?php endwhile; ?>
          <?php else: ?>
            <p>No wallpapers found.</p>
          <?php endif; ?>
          <?php $conn->close(); ?>
        </div>
      </div>
    </div>

    <!-- ai Page -->
    <div id="ai-page">
      Ai page
    </div>

    <!-- Profile Page -->
    <?php if ($isSignedIn): ?>
      <div id="profile-page">
        
      </div>
    <?php endif; ?>
  </main>
  
  <script src="./js/general.js"></script>
  <script src="./js/image.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Handle collection item click
      document.querySelectorAll('.collection-item').forEach(item => {
        item.addEventListener('click', () => {
          const collectionId = item.getAttribute('data-collection-id');
          window.location.href = `./collection.php?id=${collectionId}`;
        });
      });
    });
  </script>
</body>
</html>