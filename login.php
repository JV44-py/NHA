<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include 'config.php';

$error_message   = '';
$success_message = '';
$active_panel    = 'login'; // 'login' or 'signup'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
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
        $active_panel = 'signup';
        $name     = trim($_POST['name'] ?? '');
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($name) || !$email || empty($password) || empty($confirm)) {
            $error_message = 'All fields are required.';
        } elseif ($password !== $confirm) {
            $error_message = 'Passwords do not match.';
        } else {
            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error_message = 'That email address is already registered.';
            } else {
                $hash  = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
                $stmt2->bind_param('sss', $name, $email, $hash);
                if ($stmt2->execute()) {
                    $success_message = 'Registration successful! You may now log in.';
                    $active_panel    = 'login';
                } else {
                    $error_message = 'Error creating account: ' . $conn->error;
                }
                $stmt2->close();
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['signup'])) {
    $active_panel = 'signup';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — NHA IT Support</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=Source+Sans+3:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="login-shell">

    <div style="width:100%; max-width:400px; position:relative; z-index:1;">

        <div class="login-box">
            <div class="login-box-header">
                <div class="login-eyebrow">National Housing Authority</div>
                <h1>IT Support System</h1>
                <p>Sign in to manage support tickets</p>
            </div>

            <div class="login-body">

                <?php if ($error_message): ?>
                    <div class="alert alert-error" style="margin-bottom:16px;"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <div class="alert alert-success" style="margin-bottom:16px;"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>

                <div class="auth-panels">

                    <!-- LOGIN PANEL -->
                    <div class="auth-panel <?= $active_panel === 'login' ? 'active' : '' ?>" id="panel-login">
                        <form method="POST" autocomplete="off">
                            <input type="hidden" name="action" value="login">
                            <div class="form-group">
                                <label for="login-email">Email Address</label>
                                <input type="email" id="login-email" name="email" required placeholder="you@nha.gov.ph">
                            </div>
                            <div class="form-group">
                                <label for="login-password">Password</label>
                                <input type="password" id="login-password" name="password" required placeholder="••••••••">
                            </div>
                            <button type="submit" class="login-btn">Sign In →</button>
                        </form>
                        <div class="auth-switch">
                            Don't have an account?
                            <a href="#" onclick="showPanel('signup'); return false;">Create one</a>
                        </div>
                    </div>

                    <!-- SIGNUP PANEL -->
                    <div class="auth-panel <?= $active_panel === 'signup' ? 'active' : '' ?>" id="panel-signup">
                        <form method="POST" autocomplete="off">
                            <input type="hidden" name="action" value="register">
                            <div class="form-group">
                                <label for="reg-name">Full Name</label>
                                <input type="text" id="reg-name" name="name" required placeholder="Juan dela Cruz">
                            </div>
                            <div class="form-group">
                                <label for="reg-email">Email Address</label>
                                <input type="email" id="reg-email" name="email" required placeholder="you@nha.gov.ph">
                            </div>
                            <div class="form-group">
                                <label for="reg-password">Password</label>
                                <input type="password" id="reg-password" name="password" required placeholder="••••••••">
                            </div>
                            <div class="form-group">
                                <label for="reg-confirm">Confirm Password</label>
                                <input type="password" id="reg-confirm" name="confirm_password" required placeholder="••••••••">
                            </div>
                            <button type="submit" class="login-btn">Create Account →</button>
                        </form>
                        <div class="auth-switch">
                            Already have an account?
                            <a href="#" onclick="showPanel('login'); return false;">Sign in</a>
                        </div>
                    </div>

                </div><!-- /auth-panels -->
            </div><!-- /login-body -->
        </div><!-- /login-box -->

        <div class="login-footer">
            &copy; <?= date('Y') ?> National Housing Authority · IT Support System
        </div>
    </div>

</div>

<script>
function showPanel(name) {
    document.querySelectorAll('.auth-panel').forEach(function(p) {
        p.classList.remove('active');
    });
    document.getElementById('panel-' + name).classList.add('active');
}
</script>
</body>
</html>