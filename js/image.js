document.addEventListener('DOMContentLoaded', () => {
  const imageContainer = document.getElementById('image-container');
  const images = imageContainer.getElementsByTagName('img');

  Array.from(images).forEach(image => {
    image.addEventListener('click', () => {
      image.classList.toggle('selected');
    });
  });
});