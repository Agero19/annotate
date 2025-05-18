<?php
$page_title = "Image Upload";

require_once 'includes/header.php';
require_once ROOT_PATH . '/models/Image.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in to upload images.";
    redirect('login.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $image = new Image($db);

    // Get data from the form
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $visibility = isset($_POST['visibility']) ? $_POST['visibility'] : 'private';

    // Cleaning data
    $title = htmlspecialchars(strip_tags($title));
    $description = htmlspecialchars(strip_tags($description));

    // Array for errors
    $errors = [];

    // Data validation
    if (empty($title)) {
        $errors[] = "Title is a required field.";
    }

    if (empty($description)) {
        $errors[] = "Description is a required field.";
    }

    if (empty($_FILES['image']['name'])) {
        $errors[] = "Image is a required field.";
    } else {
        // Validate image file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Invalid image format. Only JPG, PNG, GIF, JPEG and WEBP are allowed.";
        }
    }

    // If there are no errors, proceed with image upload
    if (empty($errors)) {
        // Handle file upload
        $target_dir = "uploads/images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        if (isset($_POST["submit"])) {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                echo "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["image"]["size"] > 5000000) { // 5MB limit
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        $image->user_id = $_SESSION['user_id'];
        $image->username = $_SESSION['username'];
        $image->title = $title;
        $image->description = $description;
        $image->visibility = ($visibility === 'public') ? 1 : 0;

        $image->image_url = $_FILES['image']['name'];
        // Attempt to upload file
        if ($uploadOk == 1 && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Save image data to the database
            if ($image->create()) {
                $_SESSION['success_message'] = "Image uploaded successfully.";
                redirect('uploads.php');
                exit();
            } else {
                $errors[] = "Error occurred while uploading the image. Please try again.";
            }
        } else {
            $errors[] = "Sorry, there was an error uploading your file.";
        }
    }
}
?>


<main class="pt-5 mt-3">
    <div class="container">


        <div class="form-wrap">
            <div class="text-center">
                <h2 class="">Upload Image</h2>
            </div>


            <?php if (isset($errors) && !empty($errors)): ?>

              <div class="errorrs">
                <?php foreach ($errors as $error): ?>
                    <span><?php echo $error; ?></span>
                <?php endforeach; ?>
              </div>

            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
                enctype="multipart/form-data">

                <div class="d-flex align-items-center flex-column mt-2">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control mt-1" id="title" name="title"
                        value="<?php echo isset($title) ? $title : ''; ?>">
                </div>

                <div class="d-flex align-items-center flex-column mt-2">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control mt-1"
                        rows="5"><?php echo isset($description) ? $description : ''; ?></textarea>
                </div>

                <div class="d-flex align-items-center flex-column mt-2">
                    <label for="image" class="form-label">Image</label>

                    <label class="custom-file-upload btn mt-1">
                        <input type="file" name="image" id="image" />
                        Choose File
                    </label>
                </div>

                <div class="d-flex align-items-center flex-column mt-2">
                    <label for="visibility" class="form-label">Visibility</label>
                    <select name="visibility" class="form-control mt-1" id="visibility">
                        <option value="">Select visibility</option>
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                    </select>
                </div>
                
                <div class="d-flex justify-content-center mt-3">
                    <button type="submit" class="btn" name="submit">Upload Image</button>
                </div>
            </form>
        </div>
        </div>
    </div>
</main>
<?php
//import footer
require_once 'includes/footer.php';
?>