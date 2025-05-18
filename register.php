<?php
$page_title = "Register";

require_once 'includes/header.php';

if (isLoggedIn()) {
    $_SESSION['error_message'] = "You're already registered";
    redirect('index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once ROOT_PATH . '/models/User.php';

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';


    $username = htmlspecialchars(strip_tags($username));
    $email = htmlspecialchars(strip_tags($email));


    $errors = [];

    if (empty($username)) {
        $errors[] = "Username is a required field.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    if (empty($email)) {
        $errors[] = "Email is_active a required field.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Type in valid email.";
    }

    if (empty($password)) {
        $errors[] = "Password is a required field.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $database = new Database();
        $db = $database->getConnection();

        $user = new User($db);

        if ($user->emailOrUsernameExists($email, $username)) {
            $errors[] = "User with this email or username already exists.";
        } else {
            $user->username = $username;
            $user->email = $email;
            $user->password = $password;
            $user->is_active = true;

            if ($user->create()) {
                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['username'] = $user->username;
                $_SESSION['email'] = $user->email;

                $_SESSION['success_message'] = "Congrats, {$user->username}! You have successfuly registered.";

                redirect('index.php');
                exit();
            } else {
                $errors[] = "Error occured while creating your account. Please try again.";
            }
        }
    }
}
?>


<div class="pt-5 mt-3">
    <div class="container">
        <div class="">
            <h3 class="mb-0">Registration</h3>
        </div>
        <div class="">
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">


                <div class="mb-3">
                    <label for="username" class="">Username</label>
                    <input type="text" class="" id="username" name="username"
                        value="<?php echo isset($username) ? $username : ''; ?>" required>
                    <div class="">Username can contain only letters, numbers and underscores.</div>
                </div>

                <div class="mb-3">
                    <label for="email" class="">Email</label>
                    <input type="email" class="" id="email" name="email"
                        value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="">
                        <label for="password" class="">Password</label>
                        <input type="password" class="" id="password" name="password" required>
                        <div class="">Password has to contain at least 6 characters.</div>
                    </div>
                    <div class="">
                        <label for="confirm_password" class="">Confirm password</label>
                        <input type="password" class="" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>

                <div class="">
                    <button type="submit" class="">Register</button>
                </div>
            </form>

            <div class="mt-4">
                <p>Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </div>
    </div>
</div>


<?php

require_once 'includes/footer.php';
?>