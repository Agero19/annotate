<?php
$page_title = "Login";


require_once 'includes/header.php';

// If already logged in, redirect to index
if (isLoggedIn()) {
    $_SESSION['error_message'] = "Alreay logged in.";
    redirect('index.php');
    exit();
}

// Verify if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once ROOT_PATH . '/models/User.php';

    // Get data from the form
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;

    // Cleaning data
    $username = htmlspecialchars(strip_tags($username));

    // Array for errors
    $errors = [];

    // Data validation
    if (empty($username)) {
        $errors[] = "Type in username or email.";
    }

    if (empty($password)) {
        $errors[] = "Type in password.";
    }

    // If there are no errors , proceed with login 
    if (empty($errors)) {
        // connection to db
        $database = new Database();
        $db = $database->getConnection();

        $user = new User($db);

        // Auth
        if ($user->login($username, $password)) {
            // Set session variables
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;

            // If "Remember me" is checked, set a cookie
            if ($remember) {
                // Generate token
                $token = bin2hex(random_bytes(32));

                // Set cookie for 30d
                setcookie('remember_token', $token, time() + (86400 * 30), '/');

                // Save token in the database
                // Code to save the token in the database
            }

            // Check if there is a redirect URL set in the session
            // If not, redirect to the default page
            $redirect_to = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']);

            // Set success message
            $_SESSION['success_message'] = "Congrats, {$user->username}! You have successfuly logged in.";

            // Redirect to the specified page
            redirect($redirect_to);
            exit();
        } else {
            $errors[] = "Wrong username or password, please try again.";
        }
    }
}
?>


<div class="pt-5 mt-3">
    <div class="container">
        <div class="form-wrap">
            <div class="text-center">
                <h2 class="">Log into account</h2>
            </div>

                <?php if (isset($errors) && !empty($errors)): ?>

                    <div class="errorrs">
                      <?php foreach ($errors as $error): ?>
                          <span><?php echo $error; ?></span>
                      <?php endforeach; ?>
                    </div>

                <?php endif; ?>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="d-flex align-items-center flex-column mt-2">
                      <label for="username" class="form-label">Username or email</label>
                      <input type="text" class="form-control mt-1" id="username" name="username"
                          value="<?php echo isset($username) ? $username : ''; ?>" required>
                    </div>

                    <div class="d-flex align-items-center flex-column mt-2">
                      <label for="password" class="form-label">Password</label>
                      <input type="password" class="form-control mt-1" id="password" name="password" required>
                    </div>

                    <div class="mt-2 d-flex justify-content-center">
                        <input type="checkbox" class="form-control" id="remember" name="remember">
                        <label class="form-label ms-1" for="remember">Remember me</label>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="btn">Log in</button>
                    </div>
                </form>
                
                <div class="mt-3 d-flex align-items-center flex-column">
                  <a href="reset-password.php" class="d-none">Forgot password?</a>

                    <p class="mt-1">Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
// import footer
require_once 'includes/footer.php';
?>