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
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($name && $email && $password && $confirm_password) {
        if ($password !== $confirm_password) {
            $error_message = 'Passwords do not match!';
        } elseif (strlen($password) < 6) {
            $error_message = 'Password must be at least 6 characters long!';
        } else {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $exists = $stmt->fetchColumn();

                if ($exists) {
                    $error_message = 'An account with this email already exists!';
                } else {
                    // Hash password and insert
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, "customer")');
                    $stmt->execute([$name, $email, $phone, $hashed_password]);

                    $success_message = 'Account created successfully! Redirecting to login...';
                    
                    // Redirect after delay
                    header("refresh:2;url=login.php");
                }
            } catch (PDOException $e) {
                $error_message = 'Database error: ' . $e->getMessage();
            }
        }
    } else {
        $error_message = 'Please fill in all required fields!';
    }
}

include 'header.php';
?>

<!-- REGISTER PAGE -->
<div class="auth-page-container" style="max-width: 500px; margin: 50px auto;">
    <div class="auth-page-header">
        <h2>Create Account</h2>
        <p style="color:var(--gray);">Sign up to begin booking tour packages</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" style="display:block;"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" style="display:block;"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-row">
            <label for="regName">Full Name *</label>
            <input type="text" class="form-control" name="name" id="regName" placeholder="e.g. John Doe" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>
        <div class="form-row">
            <label for="regEmail">Email Address *</label>
            <input type="email" class="form-control" name="email" id="regEmail" placeholder="e.g. john@domain.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-row">
            <label for="regPhone">Phone Number</label>
            <input type="tel" class="form-control" name="phone" id="regPhone" placeholder="e.g. +94 77 123 4567" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
        </div>
        <div class="form-row">
            <label for="regPassword">Password *</label>
            <input type="password" class="form-control" name="password" id="regPassword" placeholder="Minimum 6 characters" required>
        </div>
        <div class="form-row">
            <label for="regConfirm">Confirm Password *</label>
            <input type="password" class="form-control" name="confirm_password" id="regConfirm" placeholder="Re-enter password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
    </form>
    <p style="text-align:center; margin-top:20px; color:var(--gray);">
        Already have an account? <a href="login.php" style="color:var(--primary); font-weight:600; text-decoration:none;">Login here</a>
    </p>
</div>

<?php include 'footer.php'; ?>
