<?php
$page_title = "Uploads";

require_once 'includes/header.php';
require_once ROOT_PATH . '/models/Image.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in to upload images.";
    redirect('login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$image = new Image($db);
$user_images = $image->getUserImages($_SESSION['user_id']);

?>

<main class="pt-5 mt-3">
    <div class="container">
        <div class="filters">Filters</div>

        <ul class="grid-container list-unstyled">
            <?php if (!empty($user_images)): ?>
                <?php foreach ($user_images as $image_item): ?>

                    <li class="grid-item">
                        <a href="<?php echo 'uploads/images/' . $image_item['image_url']; ?>" class="img-wrap">

                            <img src="<?php echo !empty($image_item['image_url']) ? 'uploads/images/' . $image_item['image_url'] : 'assets/images/image_placeholder.webp'; ?>"
                                alt="<?php echo $image_item['title'] ?>">
                            <div class="title"><?php echo $image_item['title'] ?></div>
                        </a>
                        <div class="info-wrap">
                            <div class="author-name">Author: <?= $image_item['username'] ?></div>
                        </div>
                    </li>



                <?php endforeach; ?>
            <?php else: ?>
                <li>No images found.</li>
            <?php endif; ?>
        </ul>
    </div>
</main>


<?php
// import footer
require_once 'includes/footer.php';
?>