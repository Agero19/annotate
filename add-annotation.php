<?php
require_once 'includes/header.php';
require_once ROOT_PATH . '/models/Annotation.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in to add annotations.";
    redirect('login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $annotation = new Annotation($db);
    $annotation->image_id = (int) $_POST['image_id'];
    $annotation->user_id = $_SESSION['user_id'];
    $annotation->x = (int) $_POST['x'];
    $annotation->y = (int) $_POST['y'];
    $annotation->width = (int) ($_POST['width'] ?? 1);
    $annotation->height = (int) ($_POST['height'] ?? 1);
    $annotation->comment = $_POST['comment'];

    if ($annotation->create()) {
        $_SESSION['success_message'] = "Annotation added.";
    } else {
        $_SESSION['error_message'] = "Failed to add annotation.";
    }

    redirect("image.php?id=" . $annotation->image_id);
}
?>