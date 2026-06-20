<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobeTrek Adventures</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Loading Screen -->
    <div class="loader" id="loader">
        <div class="loader-spinner"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-globe-americas"></i>
                GlobeTrek Adventures
            </a>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php" class="<?php echo ($current_page == 'index.php' || $current_page == 'ind.php' || $current_page == '') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">About</a></li>
                <li><a href="packages.php" class="<?php echo ($current_page == 'packages.php' || $current_page == 'package-details.php') ? 'active' : ''; ?>">Packages</a></li>
                <li><a href="guides.php" class="<?php echo ($current_page == 'guides.php') ? 'active' : ''; ?>">Travel Guides</a></li>
                <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
                <?php endif; ?>
            </ul>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="nav-auth" id="navUser">
                    <span id="userName" style="margin-right:10px; font-weight:600; color: var(--primary);"><i class="fas fa-user" style="margin-right:5px;"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
                </div>
            <?php else: ?>
                <div class="nav-auth" id="navAuth">
                    <a href="login.php" class="btn btn-outline btn-sm">Login</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Register</a>
                </div>
            <?php endif; ?>

            <div class="mobile-toggle" onclick="toggleMobile()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>
