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

?>

<main class="pt-5 mt-3">
  <div class="profile-container">

    <!-- Sidebar: User Info -->
    <aside class="profile-sidebar">
      <img
        src="<?php echo !empty($user->avatar) ? $user->avatar : url('assets/images/user-placeholder.webp'); ?>"
        alt="<?php echo $user->username; ?>" class="profile-image">

      <h1 class="profile-username"><?php echo $user->username; ?></h1>
      <p class="profile-email">
        <i class="fas fa-envelope"></i>
        <?php echo $user->email; ?>
      </p>
      <p class="profile-joined">
        <i class="fas fa-user"></i>
        Joined since: <?php echo date('F Y', strtotime($user->registration_date)); ?>
      </p>
    </aside>

    <!-- Main Content -->
    <section class="profile-content">
      <!-- Change Password -->
      <div class="card">
        <div class="card-header">
          <h5>Change Password</h5>
        </div>
        <div class="card-body">
          <?php if (isset($errors) && !empty($errors) && isset($_POST['change_password'])): ?>
          <div class="alert">
            <ul>
              <?php foreach ($errors as $error): ?>
              <li><?php echo $error; ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required>
            <small>Password has to contain at least 6 characters</small>

            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" class="btn" name="change_password">Change password</button>
          </form>
        </div>
      </div>

      <!-- Delete Account -->
      <div class="card danger">
        <div class="card-header">
            <h5>Delete Account</h5>
        </div>
        <div class="card-body">
            <p class="text-danger">Attention, this action can't be undone.</p>
            <p>After deletion, all your data will be removed from our server.</p>

            <form id="deleteAccountForm" onsubmit="event.preventDefault(); deleteAccount();">
            <label for="deleteConfirmPassword">Confirm password</label>
            <input type="password" id="deleteConfirmPassword" name="deleteConfirmPassword" required>

            <label class="d-flex">
                <input type="checkbox" id="deleteConfirmCheck">
                I understand that this action is permanent
            </label>

            <button type="submit" class="btn btn-danger">
                Delete Account
            </button>
            </form>
        </div>
        </div>
    </section>

  </div>
</main>

<script>
  function deleteAccount() {
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

    // In a real app, make an AJAX request here to delete the account
    alert('Your account has been deleted successfully, you will be redirected to the main page');
    window.location.href = '<?php echo url("logout.php"); ?>';
  }
</script>

<?php
require_once 'includes/footer.php';
?>