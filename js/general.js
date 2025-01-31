// Handle icon clicks and page navigation
const icons = document.querySelectorAll('.collections, .search, .home, .ai, .profile');
const pages = document.querySelectorAll('#collection-page, #search-page, #home-page, #ai-page, #profile-page');
const nav = document.querySelector('nav');
const customSelect = document.querySelector('.custom-select');

function showPage(targetId, updateHash = true) {
  pages.forEach(page => {
    page.style.display = page.id === targetId ? 'block' : 'none';
  });
  icons.forEach(icon => {
    icon.classList.toggle('active', icon.getAttribute('data-target') === targetId);
  });
  customSelect.style.display = targetId === 'home-page' ? 'block' : 'none';
  if (updateHash) {
    window.location.hash = targetId;
  }
}

// Navigate to register page if not logged in
document.addEventListener('DOMContentLoaded', () => {
  const profileLink = document.getElementById('profile-link');
  profileLink.addEventListener('click', (e) => {
    e.preventDefault();
    const isSignedIn = document.body.getAttribute('data-signed-in') === 'true';
    if (isSignedIn) {
      // Show the profile page
      showPage('profile-page');
    } else {
      // Redirect to the register page
      window.location.href = './php/register.php';
    }
  });

  // Initialize icon clicks for page navigation
  icons.forEach(icon => {
    icon.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = icon.getAttribute('data-target');
      showPage(targetId);
    });
  });

  // Handle custom dropdown
  const selectSelected = document.querySelector('.select-selected');
  const selectItems = document.querySelector('.select-items');
  const imageContainer = document.getElementById('image-container');
  const images = Array.from(imageContainer.getElementsByTagName('img'));

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

  // Prevent default drag behavior on custom select
  customSelect.addEventListener('dragstart', (e) => e.preventDefault());
  customSelect.addEventListener('dragover', (e) => e.preventDefault());
  customSelect.addEventListener('drop', (e) => e.preventDefault());

  // Prevent multiple selections
  selectItems.addEventListener('mousedown', (e) => {
    if (e.target.tagName === 'DIV') {
      selectSelected.textContent = e.target.textContent;
      selectSelected.dataset.value = e.target.dataset.value;
      selectItems.classList.add('select-hide');
      selectSelected.classList.remove('select-arrow-active');
      filterImages(selectSelected.dataset.value);
    }
  });

  // Shuffle images and set default filter
  filterImages('all');

  // Show the page based on the URL hash
  const hash = window.location.hash.substring(1);
  if (!hash) {
    window.location.hash = 'home-page';
    showPage('home-page', false);
  } else {
    showPage(hash, false);
  }
});

// Set the URL hash to home-page if it is empty
if (!window.location.hash) {
  window.location.hash = 'home-page';
}