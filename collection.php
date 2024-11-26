<?php
session_start();
include './php/db_connection.php';

$collectionId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch collection images
$sql = "SELECT w.url, w.device FROM wallo_wallpapers w
        JOIN wallo_image_collections ic ON w.id = ic.image_id
        WHERE ic.collection_id = $collectionId";
$result = $conn->query($sql);

// Fetch collection name
$collection_name_sql = "SELECT name FROM wallo_collections WHERE id = $collectionId";
$collection_name_result = $conn->query($collection_name_sql);
$collection_name = $collection_name_result->fetch_assoc()['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="./assets/favicon.ico">
  <?php include './php/components/fontawesome.php'; ?>
  <link rel="stylesheet" href="./styles/dist/css/style.css">
  <title><?php echo htmlspecialchars($collection_name); ?> - Wallo</title>
</head>
<body>
  <header>
    <div class="title-wrapper">
      <a href="index.php#collection-page">
        <img src="./assets/wallo_logo.png" alt="logo">
      </a>
      <h1 class="title"><?php echo htmlspecialchars($collection_name); ?></h1>
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

  <main id="collection-images-page">
    <div class="main-container">
      <div class="image-container" id="image-container">
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <img src="<?php echo htmlspecialchars($row['url']); ?>" data-device="<?php echo htmlspecialchars($row['device']); ?>" alt="wallpaper" loading="eager">
          <?php endwhile; ?>
        <?php else: ?>
          <p>No images found in this collection.</p>
        <?php endif; ?>
        <?php $conn->close(); ?>
      </div>
    </div>
  </main>
  
  <script src="./js/general.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const selectSelected = document.querySelector('.select-selected');
      const selectItems = document.querySelector('.select-items');
      const imageContainer = document.getElementById('image-container');
      const images = Array.from(imageContainer.getElementsByTagName('img'));

      function shuffle(array) {
        for (let i = array.length - 1; i > 0; i--) {
          const j = Math.floor(Math.random() * (i + 1));
          [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
      }

      function distributeImagesEvenly(images, columns) {
        const columnWrappers = Array.from({ length: columns }, () => document.createElement('div'));
        columnWrappers.forEach(wrapper => {
          wrapper.style.columnCount = 1;
          wrapper.style.columnGap = '20px';
          wrapper.style.width = '100%';
        });

        // Distribute images evenly across columns
        images.forEach((img, index) => {
          columnWrappers[index % columns].appendChild(img);
        });

        imageContainer.innerHTML = '';
        columnWrappers.forEach(wrapper => imageContainer.appendChild(wrapper));
      }

      function shuffleImages() {
        const shuffledImages = shuffle(images);
        distributeImagesEvenly(shuffledImages, 5);
      }

      function filterImages(device) {
        const filteredImages = images.filter(img => device === 'all' || img.getAttribute('data-device') === device);
        distributeImagesEvenly(filteredImages, 5);
      }

      selectSelected.addEventListener('click', () => {
        selectItems.classList.toggle('select-hide');
        selectSelected.classList.toggle('select-arrow-active');
      });

      selectItems.addEventListener('click', (e) => {
        if (e.target.tagName === 'DIV') {
          selectSelected.textContent = e.target.textContent;
          selectSelected.dataset.value = e.target.dataset.value;
          selectItems.classList.add('select-hide');
          selectSelected.classList.remove('select-arrow-active');
          filterImages(selectSelected.dataset.value);
        }
      });

      document.addEventListener('click', (e) => {
        if (!e.target.closest('.custom-select')) {
          selectItems.classList.add('select-hide');
          selectSelected.classList.remove('select-arrow-active');
        }
      });

      // Shuffle images and set default filter
      shuffleImages();
      filterImages('all');

      // Update URL hash with collection name
      const collectionName = "<?php echo htmlspecialchars($collection_name); ?>";
      window.location.hash = collectionName.replace(/\s+/g, '-').toLowerCase();
    });
  </script>
</body>
</html>