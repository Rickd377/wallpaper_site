const stylesheets = [
  "https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css",
  "https://site-assets.fontawesome.com/releases/v6.6.0/css/sharp-duotone-solid.css",
  "https://site-assets.fontawesome.com/releases/v6.6.0/css/sharp-thin.css",
  "https://site-assets.fontawesome.com/releases/v6.6.0/css/sharp-solid.css",
  "https://site-assets.fontawesome.com/releases/v6.6.0/css/sharp-regular.css",
  "https://site-assets.fontawesome.com/releases/v6.6.0/css/sharp-light.css",
  "./styles/dist/css/style.css"
];
stylesheets.forEach(href => {
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = href;
  document.head.appendChild(link);
});