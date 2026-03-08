<?php
session_start();
// if already logged in, send to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include 'config.php';

$error_message = '';
$success_message = '';
$showSignup = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        // login attempt
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $error_message = 'Please enter a valid email and password.';
        } else {
            $stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    header('Location: index.php');
                    exit;
                } else {
                    $error_message = 'Incorrect password.';
                }
            } else {
                $error_message = 'No account found with that email.';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        $showSignup = true;
        $name     = sanitize($_POST['name'] ?? '');
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($name) || !$email || empty($password) || empty($confirm)) {
            $error_message = 'All fields are required for signup.';
        } elseif ($password !== $confirm) {
            $error_message = 'Passwords do not match.';
        } else {
            // check existing email
            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error_message = 'That email address is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
                $stmt2->bind_param('sss', $name, $email, $hash);
                if ($stmt2->execute()) {
                    $success_message = 'Registration successful! Please log in below.';
                    // after successful signup we want to show login front side
                    $showSignup = false;
                } else {
                    $error_message = 'Error creating account: ' . $conn->error;
                }
                $stmt2->close();
            }
            $stmt->close();
        }
    }
}

// if requested via GET with ?signup, show signup side
if (isset($_GET['signup'])) {
    $showSignup = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Sign Up - HR IT Support</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🔒 Welcome</h1>
            <p>Please sign in or create an account</p>
        </header>

        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="auth-wrapper">
            <div id="auth-card" class="auth-card<?php echo $showSignup ? ' show-signup' : ''; ?>">
                <!-- login front -->
                <div class="auth-front">
                    <form method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit">Log In</button>
                    </form>
                    <div class="auth-switch">
                        <span>Don't have an account?</span> <a href="#" id="to-signup">Sign up</a>
                    </div>
                </div>

                <!-- signup back -->
                <div class="auth-back">
                    <form method="POST">
                        <input type="hidden" name="action" value="register">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email2">Email</label>
                            <input type="email" id="email2" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password2">Password</label>
                            <input type="password" id="password2" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit">Sign Up</button>
                    </form>
                    <div class="auth-switch">
                        <span>Already have an account?</span> <a href="#" id="to-login">Log in</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2026 HR IT Support System. All rights reserved.</p>
        </div>
    </div>

    <script>
    document.getElementById('to-signup').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('auth-card').classList.add('show-signup');
    });
    document.getElementById('to-login').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('auth-card').classList.remove('show-signup');
    });
    </script>
</body>
</html>
