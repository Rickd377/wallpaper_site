// Handle icon clicks
const icons = document.querySelectorAll('.collections, .search, .home, .settings, .profile');
const pages = document.querySelectorAll('#collection-page, #search-page, #home-page, #settings-page, #profile-page');

icons.forEach(icon => {
  icon.addEventListener('click', (e) => {
    e.preventDefault();
    if (!icon.classList.contains('active')) {
      icons.forEach(i => i.classList.remove('active'));
      icon.classList.add('active');

      const targetId = icon.getAttribute('data-target');
      pages.forEach(page => {
        if (page.id === targetId) {
          page.style.display = 'block';
        } else {
          page.style.display = 'none';
        }
      });
    }
  });
});

// Handle custom dropdown
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

  function shuffleImages() {
    const shuffledImages = shuffle(images);
    imageContainer.innerHTML = '';
    shuffledImages.forEach(img => imageContainer.appendChild(img));
  }

  function filterImages(device) {
    images.forEach(img => {
      if (device === 'all' || img.src.includes(device)) {
        img.style.display = 'block';
      } else {
        img.style.display = 'none';
      }
    });
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

  shuffleImages(); // Shuffle images on page load
  filterImages('all'); // Show all images by default
});