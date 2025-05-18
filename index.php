<?php
// Title
$page_title = "Main";

// import header
require_once 'includes/header.php';
require_once ROOT_PATH . '/models/Image.php';

// Отримання списку популярних турів для відображення на головній сторінці
$database = new Database();
$db = $database->getConnection();
$image = new Image($db);
$public_images = $image->getPublicImages(9);

?>


<main class="pt-5 mt-3">
    <div class="container">
        <div class="filters">Filters</div>

        <ul class="grid-container list-unstyled">
            <?php if (!empty($public_images)): ?>
                <?php foreach ($public_images as $image_item): ?>

                    <li class="grid-item">
                        <a href="image.php?id=<?= $image_item['image_id'] ?>" class="img-wrap">


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