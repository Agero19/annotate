<?php
$page_title = "Image Info";

require_once 'includes/header.php';
require_once ROOT_PATH . '/models/Image.php';

$image_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// No image id
if ($image_id <= 0) {
    echo "<p class='container pt-5 mt-3'>Invalid image ID.</p>";
    require_once 'includes/footer.php';
    exit();
}

//connect to db
$database = new Database();
$db = $database->getConnection();
$image_model = new Image($db);

// Get image by id
$image = $image_model->getImageById($image_id);

// Get image annotations
require_once ROOT_PATH . '/models/Annotation.php';
$annotation_model = new Annotation($db);
$annotations = $annotation_model->getAnnotationsByImageId($image_id);

// Check if image exists
if (!$image) {
    echo "<p class='container pt-5 mt-3'>Image not found.</p>";
    require_once 'includes/footer.php';
    exit();
}

// Check if image is private
if ($image['visibility'] == 0 && (!isLoggedIn() || $_SESSION['user_id'] != $image['user_id'])) {
    echo "<p class='container pt-5 mt-3'>You don't have access to this image.</p>";
    require_once 'includes/footer.php';
    exit();
}
?>

<main class="container pt-5 mt-3">
    <div class="d-flex image-page-wrap">

        <!-- Image Itself -->
        <div class="image-annotator" style="position: relative; display: inline-block;">
            <img id="annotatable-image" src="uploads/images/<?= htmlspecialchars($image['image_url']) ?>"
                class="img-fluid" alt="<?= htmlspecialchars($image['title']) ?>"
                data-image-id="<?= $image['image_id'] ?>">

            <div id="annotation-layer"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                <!-- Inserted circles with JS -->
            </div>
        </div>


        <!-- Image Description -->
        <div class="sidebar-annotations">
            <h2><?= htmlspecialchars($image['title']) ?></h2>
            <p><strong>Author:</strong> <?= htmlspecialchars($image['username']) ?></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($image['description'])) ?></p>
            <p><strong>Uploaded:</strong> <?= htmlspecialchars($image['created_at']) ?></p>
            <p><strong>Visibility:</strong> <?= $image['visibility'] == 1 ? 'Public' : 'Private' ?></p>

            <!-- Sidbar with Annotations -->
            <div class="annotation-sidebar">
                <h4>Annotations</h4>
                <?php foreach ($annotations as $ann): ?>
                    <div class="annotation-comment" id="comment-<?= $ann['annotation_id'] ?>">
                        <p><strong>User ID:</strong> <?= htmlspecialchars($ann['user_id']) ?></p>
                        <p><?= nl2br(htmlspecialchars($ann['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (isLoggedIn()): ?>
                    <button id="start-annotation" class="btn btn-primary mb-3">Add Annotation</button>
                <?php endif; ?>
            </div>

            <!-- Add annotations if logged in on click -->
            <?php if (isLoggedIn()): ?>
                <form id="annotation-comment-form" style="display:none;" method="post" action="add-annotation.php">
                    <input type="hidden" name="image_id" value="<?= $image['image_id'] ?>">
                    <input type="hidden" name="x" id="ann_x">
                    <input type="hidden" name="y" id="ann_y">
                    <input type="hidden" name="width" value="1">
                    <input type="hidden" name="height" value="1">
                    <div class="form-group">
                        <label for="comment">Comment:</label>
                        <textarea name="comment" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn  mt-2">Add Annotation</button>
                </form>
            <?php endif; ?>

             <!-- Delete Image button -->
            <?php if (isLoggedIn() && $_SESSION['user_id'] == $image['user_id']): ?>
                <form method="post" action="delete-image.php"
                    onsubmit="return confirm('Are you sure you want to delete this image?');">
                    <input type="hidden" name="image_id" value="<?= $image['image_id'] ?>">
                    <button type="submit" class="btn btn-danger mt-3">Delete Image</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    const annotations = <?= json_encode($annotations) ?>;
    let isAnnotating = false;

    document.addEventListener('DOMContentLoaded', () => {
        const layer = document.getElementById('annotation-layer');
        const image = document.getElementById('annotatable-image');
        const comments = document.querySelectorAll('.annotation-comment');
        const form = document.getElementById('annotation-comment-form');
        const xInput = document.getElementById('ann_x');
        const yInput = document.getElementById('ann_y');
        const startBtn = document.getElementById('start-annotation');

        // Map to store dots by ID for later access
        const dotMap = new Map();

        annotations.forEach(annot => {
            const dot = document.createElement('div');
            dot.className = 'annotation-dot';
            dot.dataset.id = annot.annotation_id;

            dot.style.position = 'absolute';
            dot.style.left = `${annot.x}%`;
            dot.style.top = `${annot.y}%`;
            dot.style.width = '12px';
            dot.style.height = '12px';
            dot.style.borderRadius = '50%';
            dot.style.cursor = 'pointer';
            dot.style.pointerEvents = 'auto';
            dot.style.transform = 'translate(-50%, -50%)';
            dot.style.background = 'rgba(255, 0, 0, 0.5)';

            dot.addEventListener('click', () => {
                comments.forEach(c => c.classList.remove('highlight'));
                const targetComment = document.querySelector(`#comment-${annot.annotation_id}`);
                if (targetComment) {
                    targetComment.classList.add('highlight');
                    targetComment.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                setActiveDot(annot.annotation_id);
            });

            layer.appendChild(dot);
            dotMap.set(annot.annotation_id, dot);
        });

        // Handle clicking annotation text
        comments.forEach(comment => {
            comment.addEventListener('click', () => {
                comments.forEach(c => c.classList.remove('highlight'));
                comment.classList.add('highlight');

                const id = comment.dataset.id;
                setActiveDot(id);
            });
        });

        // Highlight dot by annotation ID
        function setActiveDot(id) {
            dotMap.forEach(dot => dot.classList.remove('active'));
            const targetDot = dotMap.get(id);
            if (targetDot) {
                targetDot.classList.add('active');
            }
        }

        if (startBtn) {
            startBtn.addEventListener('click', () => {
                isAnnotating = true;
                form.style.display = 'none';
                alert("Теперь кликните на изображение, чтобы добавить аннотацию.");
            });
        }

        image.addEventListener('click', function (e) {
            if (!isAnnotating) return;

            const rect = image.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const clickY = e.clientY - rect.top;

            const xPercent = (clickX / image.width) * 100;
            const yPercent = (clickY / image.height) * 100;

            if (xPercent >= 0 && yPercent >= 0 && xPercent <= 100 && yPercent <= 100) {
                xInput.value = xPercent.toFixed(2);
                yInput.value = yPercent.toFixed(2);
                form.style.display = 'block';
                form.scrollIntoView({ behavior: "smooth" });
                isAnnotating = false;
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>