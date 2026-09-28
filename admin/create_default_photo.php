<?php
require_once '../config.php';

// Create default photo directory if it doesn't exist
$default_photo_dir = '../assets/images/';
if (!file_exists($default_photo_dir)) {
    mkdir($default_photo_dir, 0777, true);
}

// Create a default photo using GD
$width = 200;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Set background color (light gray)
$bg_color = imagecolorallocate($image, 240, 240, 240);
imagefill($image, 0, 0, $bg_color);

// Add a user icon
$icon_color = imagecolorallocate($image, 150, 150, 150);
$center_x = $width / 2;
$center_y = $height / 2;
$radius = 40;

// Draw circle for head
imagefilledellipse($image, $center_x, $center_y - 20, $radius * 2, $radius * 2, $icon_color);

// Draw body
$body_points = array(
    $center_x, $center_y + 20,
    $center_x - 30, $center_y + 80,
    $center_x + 30, $center_y + 80
);
imagefilledpolygon($image, $body_points, 3, $icon_color);

// Save the image
$default_photo_path = $default_photo_dir . 'default-candidate.jpg';
imagejpeg($image, $default_photo_path, 90);
imagedestroy($image);

echo "Default candidate photo created successfully!";
?> 