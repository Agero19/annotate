<?php
$page_title = "My profile";

require_once 'includes/header.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "To view this page, you must be logged in.";

    redirect('login.php');
    exit();
}

require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Image.php';

$database = new Database();
$db = $database->getConnection();

$user = getCurrentUser();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['change_password'])) {
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        $errors = [];

        if (empty($current_password)) {
            $errors[] = "Type in current password";
        }

        if (empty($new_password)) {
            $errors[] = "Type in new password";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }

        if ($new_password !== $confirm_password) {
            $errors[] = "Password do nnot match";
        }

        if (empty($errors)) {
            $user_check = new User($db);
            $user_check->email = $user->email;

            if (!$user_check->login($user->email, $current_password)) {
                $errors[] = "Invalid current password";
            }

            if (empty($errors)) {
                if ($user->updatePassword($new_password)) {
                    $_SESSION['success_message'] = "Your password has been changed successfully.";


                    redirect('profile.php');
                    exit();
                } else {
                    $errors[] = "Failed to update password.";
                }
            }
        }
    }
}


// CSS стилі для сторінки
$page_styles = '
<style>
    .profile-header {
        background-color: #0d6efd;
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }
    .profile-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .profile-tabs .nav-link {
        font-weight: 500;
        padding: 1rem 1.5rem;
        color: #6c757d;
    }
    .profile-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
    }
</style>';
?>

<div class="profile-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3 text-center text-md-start mb-3 mb-md-0">
                <img src="<?php echo !empty($user->avatar) ? $user->avatar : url('assets/images/user-placeholder.webp'); ?>"
                    alt="<?php echo $user->username; ?>" class="profile-image">
            </div>
            <div class="col-md-9">
                <h1 class="display-6 mb-1"><?php echo $user->username; ?></h1>
                <p class="mb-2"><i class="fas fa-envelope me-2"></i><?php echo $user->email; ?></p>
                <p class="mb-0">
                    <i class="fas fa-user me-2"></i>Joined since:
                    <?php echo date('F Y', strtotime($user->registration_date)); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="tab-content">

    <div class="tab-pane fade  'show active' ">
        <div class="row">
            <div class="col-lg-8">
                <!-- Change Password -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Change Password</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors) && !empty($errors) && isset($_POST['change_password'])): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current password</label>
                                <input type="password" class="form-control" id="current_password"
                                    name="current_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                    required>
                                <div class="form-text">Password has to contain at least 6 characters</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm new password</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-primary">Change
                                password</button>
                        </form>
                    </div>
                </div>


            </div>

            <div class="col-lg-4">
                <!-- Account deletion -->
                <div class="card shadow-sm mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Delete Account</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-danger">Attention , this action can't be unturned</p>
                        <p>After delition of account all of you data will be deleted from our server</p>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteAccountModal">
                            <i class="fas fa-trash-alt me-1"></i> Delete Account
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAccountModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold">Are you sure, you want to delete your account?</p>
                <p>This action can't be undone, all of your data will be deleted</p>
                <form id="deleteAccountForm">
                    <div class="mb-3">
                        <label for="deleteConfirmPassword" class="form-label">Please enter you password to
                            confirm:</label>
                        <input type="password" class="form-control" id="deleteConfirmPassword" name="confirm_password"
                            required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="deleteConfirmCheck" name="confirm_delete"
                            required>
                        <label class="form-check-label" for="deleteConfirmCheck">I understad that this action can't be
                            undone, and all of my data will be deleted</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteAccount()">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<script>
    function deleteAccount() {
        const form = document.getElementById('deleteAccountForm');
        const password = document.getElementById('deleteConfirmPassword').value;
        const confirmCheck = document.getElementById('deleteConfirmCheck').checked;

        if (!password) {
            alert('Type in your password to confirm deletion');
            return;
        }

        if (!confirmCheck) {
            alert('Please confirm that you understand this action cannot be undone');
            return;
        }

        // In real app there would be an AJAX request to the server to delete the account
        alert('Your account has been deleted successfully, you will be redirected to the main page');
        window.location.href = '<?php echo url("logout.php"); ?>';
    }
</script>

<?php
require_once 'includes/footer.php';
?>