<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

$success_message = '';
$error_message = '';

// Handle Staff Action: Confirm Booking
if ($user_role === 'staff' && isset($_GET['confirm_booking'])) {
    $booking_id = intval($_GET['confirm_booking']);
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Confirmed' WHERE id = ?");
        $stmt->execute([$booking_id]);
        $success_message = "Booking confirmed successfully!";
    } catch (PDOException $e) {
        $error_message = "Failed to confirm booking: " . $e->getMessage();
    }
}

// Handle Admin Action: Delete User
if ($user_role === 'admin' && isset($_GET['delete_user'])) {
    $delete_id = intval($_GET['delete_user']);
    // Protect deleting self
    if ($delete_id === $user_id) {
        $error_message = "You cannot delete your own admin account!";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            $success_message = "User account deleted successfully!";
        } catch (PDOException $e) {
            $error_message = "Failed to delete user: " . $e->getMessage();
        }
    }
}

// Handle Profile Updates (for Customers)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $new_phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if ($new_name) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$new_name, $new_phone, $user_id]);
            $_SESSION['user_name'] = $new_name; // update session name
            $user_name = $new_name;
            $success_message = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Failed to update profile: " . $e->getMessage();
        }
    }
}

// --- FETCH DATA FOR DASHBOARDS ---

if ($user_role === 'admin') {
    // 1. Analytics Cards
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $total_staff = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();
    $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $total_inquiries = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();

    // 2. Recent Bookings logs
    $recent_activity = $pdo->query("
        SELECT b.created_at, b.ref, u.email, p.name as pkg_name, b.total_cost 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN packages p ON b.package_id = p.id 
        ORDER BY b.created_at DESC LIMIT 5
    ")->fetchAll();

    // 3. Customers list
    $customers = $pdo->query("
        SELECT u.id, u.name, u.email, u.created_at, COUNT(b.id) as booking_count 
        FROM users u 
        LEFT JOIN bookings b ON u.id = b.user_id 
        WHERE u.role = 'customer' 
        GROUP BY u.id 
        ORDER BY u.created_at DESC
    ")->fetchAll();

    // 4. Staff list
    $staff_members = $pdo->query("SELECT id, name, email, phone FROM users WHERE role = 'staff' ORDER BY name ASC")->fetchAll();

    // 5. Monthly Revenue Reports
    $reports = $pdo->query("
        SELECT DATE_FORMAT(travel_date, '%M %Y') as month, COUNT(id) as bookings, SUM(total_cost) as revenue 
        FROM bookings 
        GROUP BY month 
        ORDER BY travel_date DESC
    ")->fetchAll();

} elseif ($user_role === 'staff') {
    // 1. Stats Counter
    $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
    $confirmed_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'")->fetchColumn();
    $new_inquiries = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Pending'")->fetchColumn();

    // 2. Pending Bookings Table
    $pending_list = $pdo->query("
        SELECT b.id, b.ref, u.name as cust_name, p.name as pkg_name, b.travel_date, b.total_cost 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN packages p ON b.package_id = p.id 
        WHERE b.status = 'Pending' 
        ORDER BY b.created_at ASC
    ")->fetchAll();

    // 3. All Bookings Table
    $all_bookings = $pdo->query("
        SELECT b.ref, u.name as cust_name, p.name as pkg_name, b.status 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN packages p ON b.package_id = p.id 
        ORDER BY b.created_at DESC
    ")->fetchAll();

    // 4. Customer Inquiries
    $inquiries = $pdo->query("SELECT name, email, subject, created_at, status FROM inquiries ORDER BY created_at DESC")->fetchAll();

    // 5. Packages List
    $packages_list = $pdo->query("SELECT name, destination, price FROM packages ORDER BY id ASC")->fetchAll();

} else { // customer
    // 1. Stats Counters
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_bookings = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'Confirmed'");
    $stmt->execute([$user_id]);
    $confirmed_bookings = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'Pending'");
    $stmt->execute([$user_id]);
    $pending_bookings = $stmt->fetchColumn();

    // 2. Recent Bookings Table
    $stmt = $pdo->prepare("
        SELECT b.ref, p.name as pkg_name, b.travel_date, b.travelers, b.status, b.total_cost 
        FROM bookings b 
        JOIN packages p ON b.package_id = p.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $bookings_list = $stmt->fetchAll();

    // 3. Inquiry History
    $stmt = $pdo->prepare("SELECT created_at, subject, status FROM inquiries WHERE user_id = ? OR email = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id, $user_email]);
    $inquiries_list = $stmt->fetchAll();

    // 4. User profile details
    $stmt = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_phone = $stmt->fetchColumn();
}

include 'header.php';
?>

<!-- Status Alerts -->
<div style="max-width: 1200px; margin: 20px auto 0; padding: 0 20px;">
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" style="display:block;"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" style="display:block;"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
</div>

<!-- CUSTOMER DASHBOARD VIEW -->
<?php if ($user_role === 'customer'): ?>
    <div id="customer-dashboard-view">
        <div class="dashboard-layout">
            <div class="sidebar">
                <div class="sidebar-user">
                    <div class="sidebar-avatar"><i class="fas fa-user"></i></div>
                    <h4 id="custName"><?php echo htmlspecialchars($user_name); ?></h4>
                    <p style="opacity:0.7; font-size:0.9rem;">Traveler</p>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="#" class="active" onclick="showDashTab('cust-overview')"><i class="fas fa-home"></i> Overview</a></li>
                    <li><a href="#" onclick="showDashTab('cust-bookings')"><i class="fas fa-suitcase"></i> My Bookings</a></li>
                    <li><a href="#" onclick="showDashTab('cust-inquiries')"><i class="fas fa-envelope"></i> Inquiries</a></li>
                    <li><a href="#" onclick="showDashTab('cust-profile')"><i class="fas fa-user-cog"></i> Profile</a></li>
                </ul>
            </div>
            <div class="main-content">
                <div class="dashboard-header">
                    <h2>Welcome, <span id="dashNameHeader"><?php echo htmlspecialchars($user_name); ?></span></h2>
                    <a href="packages.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Booking</a>
                </div>
                
                <!-- Overview Tab -->
                <div id="cust-overview" class="dash-tab active">
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fas fa-suitcase"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $total_bookings; ?></h4>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $confirmed_bookings; ?></h4>
                                <p>Confirmed</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $pending_bookings; ?></h4>
                                <p>Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Recent Bookings</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ref</th>
                                            <th>Package</th>
                                            <th>Date</th>
                                            <th>Travelers</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($bookings_list) > 0): ?>
                                            <?php foreach ($bookings_list as $b): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($b['ref']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['pkg_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['travel_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['travelers']); ?></td>
                                                    <td>
                                                        <span class="status-badge <?php 
                                                            if ($b['status'] == 'Confirmed') echo 'status-confirmed';
                                                            elseif ($b['status'] == 'Pending') echo 'status-pending';
                                                            else echo 'status-cancelled';
                                                        ?>"><?php echo htmlspecialchars($b['status']); ?></span>
                                                    </td>
                                                    <td>$<?php echo number_format($b['total_cost']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" style="text-align:center; color:var(--gray);">No bookings found yet. <a href="packages.php">Book a package!</a></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bookings Tab -->
                <div id="cust-bookings" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>All Bookings</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ref</th>
                                            <th>Package</th>
                                            <th>Travel Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($bookings_list) > 0): ?>
                                            <?php foreach ($bookings_list as $b): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($b['ref']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['pkg_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['travel_date']); ?></td>
                                                    <td>
                                                        <span class="status-badge <?php 
                                                            if ($b['status'] == 'Confirmed') echo 'status-confirmed';
                                                            elseif ($b['status'] == 'Pending') echo 'status-pending';
                                                            else echo 'status-cancelled';
                                                        ?>"><?php echo htmlspecialchars($b['status']); ?></span>
                                                    </td>
                                                    <td><button class="btn btn-outline btn-sm" onclick="showToast('Booking Ref: <?php echo $b['ref']; ?>')">View</button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" style="text-align:center; color:var(--gray);">No bookings found yet.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Inquiries Tab -->
                <div id="cust-inquiries" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Inquiry History</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($inquiries_list) > 0): ?>
                                            <?php foreach ($inquiries_list as $i): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($i['created_at']); ?></td>
                                                    <td><?php echo htmlspecialchars($i['subject']); ?></td>
                                                    <td><span class="status-badge <?php echo ($i['status'] == 'Replied') ? 'status-confirmed' : 'status-pending'; ?>"><?php echo htmlspecialchars($i['status']); ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" style="text-align:center; color:var(--gray);">No inquiries submitted yet.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Tab -->
                <div id="cust-profile" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Profile Settings</h3>
                        </div>
                        <div class="content-card-body">
                            <form method="POST" action="dashboard.php">
                                <input type="hidden" name="update_profile" value="1">
                                <div class="form-row">
                                    <label>Full Name</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required>
                                </div>
                                <div class="form-row">
                                    <label>Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" readonly style="background-color:#e9ecef;">
                                </div>
                                <div class="form-row">
                                    <label>Phone</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- STAFF DASHBOARD VIEW -->
<?php if ($user_role === 'staff'): ?>
    <div id="staff-dashboard-view">
        <div class="dashboard-layout">
            <div class="sidebar">
                <div class="sidebar-user">
                    <div class="sidebar-avatar" style="background:var(--warning);"><i class="fas fa-briefcase"></i></div>
                    <h4><?php echo htmlspecialchars($user_name); ?></h4>
                    <p style="opacity:0.7; font-size:0.9rem;">Travel Agent</p>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="#" class="active" onclick="showDashTab('staff-overview')"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="#" onclick="showDashTab('staff-bookings')"><i class="fas fa-suitcase"></i> Bookings</a></li>
                    <li><a href="#" onclick="showDashTab('staff-inquiries')"><i class="fas fa-envelope"></i> Inquiries</a></li>
                    <li><a href="#" onclick="showDashTab('staff-packages')"><i class="fas fa-box"></i> Packages</a></li>
                </ul>
            </div>
            <div class="main-content">
                <div class="dashboard-header">
                    <h2>Staff Dashboard</h2>
                    <span style="color:var(--gray);">Access Level: Operations</span>
                </div>
                
                <!-- Overview -->
                <div id="staff-overview" class="dash-tab active">
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fas fa-suitcase"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $total_bookings; ?></h4>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $pending_bookings; ?></h4>
                                <p>Pending</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $confirmed_bookings; ?></h4>
                                <p>Confirmed</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon red"><i class="fas fa-envelope"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $new_inquiries; ?></h4>
                                <p>Pending Inquiries</p>
                            </div>
                        </div>
                    </div>
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Pending Bookings</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ref</th>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($pending_list) > 0): ?>
                                            <?php foreach ($pending_list as $b): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($b['ref']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['cust_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['pkg_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['travel_date']); ?></td>
                                                    <td>$<?php echo number_format($b['total_cost']); ?></td>
                                                    <td><a href="dashboard.php?confirm_booking=<?php echo $b['id']; ?>" class="btn btn-success btn-sm">Confirm</a></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" style="text-align:center; color:var(--gray);">No pending bookings at the moment. Good job!</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bookings -->
                <div id="staff-bookings" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>All Bookings</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ref</th>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_bookings as $b): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($b['ref']); ?></td>
                                                <td><?php echo htmlspecialchars($b['cust_name']); ?></td>
                                                <td><?php echo htmlspecialchars($b['pkg_name']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php 
                                                        if ($b['status'] == 'Confirmed') echo 'status-confirmed';
                                                        elseif ($b['status'] == 'Pending') echo 'status-pending';
                                                        else echo 'status-cancelled';
                                                    ?>"><?php echo htmlspecialchars($b['status']); ?></span>
                                                </td>
                                                <td><button class="btn btn-outline btn-sm" onclick="showToast('Ref: <?php echo $b['ref']; ?>')">View</button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Inquiries -->
                <div id="staff-inquiries" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Customer Inquiries</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inquiries as $i): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($i['name']); ?></td>
                                                <td><?php echo htmlspecialchars($i['subject']); ?></td>
                                                <td><?php echo htmlspecialchars($i['created_at']); ?></td>
                                                <td><span class="status-badge <?php echo ($i['status'] == 'Replied') ? 'status-confirmed' : 'status-pending'; ?>"><?php echo htmlspecialchars($i['status']); ?></span></td>
                                                <td>
                                                    <?php if ($i['status'] === 'Pending'): ?>
                                                        <button class="btn btn-primary btn-sm" onclick="showToast('Reply sent to <?php echo htmlspecialchars($i['email']); ?>')">Reply</button>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline btn-sm" disabled>Answered</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Packages -->
                <div id="staff-packages" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Manage Packages</h3>
                            <button class="btn btn-primary btn-sm" onclick="showToast('Database insertion locked')">Add Package</button>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Package Name</th>
                                            <th>Destination</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($packages_list as $p): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                                <td><?php echo htmlspecialchars($p['destination']); ?></td>
                                                <td>$<?php echo number_format($p['price']); ?></td>
                                                <td><span class="status-badge status-confirmed">Active</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ADMIN DASHBOARD VIEW -->
<?php if ($user_role === 'admin'): ?>
    <div id="admin-dashboard-view">
        <div class="dashboard-layout">
            <div class="sidebar">
                <div class="sidebar-user">
                    <div class="sidebar-avatar" style="background:var(--danger);"><i class="fas fa-user-shield"></i></div>
                    <h4><?php echo htmlspecialchars($user_name); ?></h4>
                    <p style="opacity:0.7; font-size:0.9rem;">System Admin</p>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="#" class="active" onclick="showDashTab('admin-overview')"><i class="fas fa-chart-line"></i> Analytics</a></li>
                    <li><a href="#" onclick="showDashTab('admin-users')"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="#" onclick="showDashTab('admin-staff')"><i class="fas fa-user-tie"></i> Staff</a></li>
                    <li><a href="#" onclick="showDashTab('admin-reports')"><i class="fas fa-file-alt"></i> Reports</a></li>
                </ul>
            </div>
            <div class="main-content">
                <div class="dashboard-header">
                    <h2>Admin Dashboard</h2>
                    <span style="color:var(--gray);">Access Level: Root Administrator</span>
                </div>
                
                <!-- Overview -->
                <div id="admin-overview" class="dash-tab active">
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $total_users; ?></h4>
                                <p>Total Users</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fas fa-user-tie"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $total_staff; ?></h4>
                                <p>Staff Members</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon orange"><i class="fas fa-suitcase"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $total_bookings; ?></h4>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon red"><i class="fas fa-envelope"></i></div>
                            <div class="stat-info">
                                <h4><?php echo $total_inquiries; ?></h4>
                                <p>Total Inquiries</p>
                            </div>
                        </div>
                    </div>
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Recent Activity</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Activity</th>
                                            <th>User</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_activity as $act): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($act['created_at']); ?></td>
                                                <td>New Booking (<?php echo htmlspecialchars($act['ref']); ?>)</td>
                                                <td><?php echo htmlspecialchars($act['email']); ?></td>
                                                <td><?php echo htmlspecialchars($act['pkg_name']); ?> - $<?php echo number_format($act['total_cost']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customers list -->
                <div id="admin-users" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Manage Customers</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Bookings</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $c): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($c['name']); ?></td>
                                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                                <td><?php echo htmlspecialchars($c['booking_count']); ?></td>
                                                <td><?php echo htmlspecialchars($c['created_at']); ?></td>
                                                <td><a href="dashboard.php?delete_user=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Staff list -->
                <div id="admin-staff" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Manage Staff</h3>
                            <button class="btn btn-primary btn-sm" onclick="showToast('Staff generation locked')">Add Staff</button>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($staff_members as $s): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($s['name']); ?></td>
                                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                                <td><?php echo htmlspecialchars($s['phone']); ?></td>
                                                <td><a href="dashboard.php?delete_user=<?php echo $s['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this staff member?');">Delete</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reports list -->
                <div id="admin-reports" class="dash-tab" style="display:none;">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Booking Reports</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Bookings</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($reports) > 0): ?>
                                            <?php foreach ($reports as $rep): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($rep['month']); ?></td>
                                                    <td><?php echo htmlspecialchars($rep['bookings']); ?></td>
                                                    <td>$<?php echo number_format($rep['revenue']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" style="text-align:center; color:var(--gray);">No booking report data yet.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
