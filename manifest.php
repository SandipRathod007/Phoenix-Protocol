<?php
// Force the server to treat this PHP file as a JSON file
header('Content-Type: application/json');
?>
{
  "name": "Phoenix Protocol",
  "short_name": "Phoenix",
  "start_url": "index.php",
  "display": "standalone",
  "background_color": "#0f0f13",
  "theme_color": "#0f0f13",
  "orientation": "portrait",
  "icons": [
    {
      "src": "https://cdn-icons-png.flaticon.com/512/744/744922.png",
      "sizes": "192x192",
      "type": "image/png"
    }
  ]
}