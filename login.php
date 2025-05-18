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
        <div class="">
            <h3 class="">Log into account</h3>
        </div>
        <div class="">
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="">
                    <ul class="">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="">
                    <label for="username" class="form-label">Username or email</label>
                    <input type="text" class="" id="username" name="username"
                        value="<?php echo isset($username) ? $username : ''; ?>" required>
                </div>

                <div class="">
                    <label for="password" class="">Password</label>
                    <input type="password" class="" id="password" name="password" required>
                </div>

                <div class="">
                    <input type="checkbox" class="" id="remember" name="remember">
                    <label class="" for="remember">Remember me</label>
                </div>

                <div class="">
                    <button type="submit" class="">Log in</button>
                </div>

                <div class="mt-3">
                    <a href="reset-password.php">Forgot password?</a>
                </div>
            </form>

            <div class="mt-4">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>
</div>


<?php
// import footer
require_once 'includes/footer.php';
?>