<?php
require_once 'includes/header.php';
require_once ROOT_PATH . '/models/Image.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in to delete an image.";
    redirect('login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['image_id'])) {
    $_SESSION['error_message'] = "Invalid request.";
    redirect('uploads.php');
    exit();
}

$image_id = (int) $_POST['image_id'];

$database = new Database();
$db = $database->getConnection();
$image_model = new Image($db);

$image = $image_model->getImageById($image_id);

if (!$image) {
    $_SESSION['error_message'] = "Image not found.";
    redirect('uploads.php');
    exit();
}

if ($image['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error_message'] = "You are not authorized to delete this image.";
    redirect('uploads.php');
    exit();
}

// Delete image file from filesystem
$image_path = ROOT_PATH . '/uploads/images/' . $image['image_url'];
if (file_exists($image_path)) {
    unlink($image_path); // delete file
}

// Delete image record from database
if ($image_model->deleteImage($image_id)) {
    $_SESSION['success_message'] = "Image deleted successfully.";
} else {
    $_SESSION['error_message'] = "Failed to delete image.";
}

redirect('uploads.php');
