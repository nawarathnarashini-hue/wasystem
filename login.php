<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        try {
            // Find user by email
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful, set session details
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error_message = 'Invalid email or password!';
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = 'All fields are required!';
    }
}

include 'header.php';
?>

<!-- LOGIN PAGE -->
<div class="auth-page-container">
    <div class="auth-page-header">
        <h2>Welcome Back</h2>
        <p style="color:var(--gray);">Access your bookings and travel logs</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" style="display:block;"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-row">
            <label for="loginEmail">Email Address</label>
            <input type="email" class="form-control" name="email" id="loginEmail" placeholder="e.g. customer@globetrek.lk" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-row">
            <label for="loginPassword">Password</label>
            <input type="password" class="form-control" name="password" id="loginPassword" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
    </form>
    <p style="text-align:center; margin-top:20px; color:var(--gray);">
        Don't have an account? <a href="register.php" style="color:var(--primary); font-weight:600; text-decoration:none;">Register here</a>
    </p>
</div>

<?php include 'footer.php'; ?>
