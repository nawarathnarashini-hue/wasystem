<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast_msg'] = "You must be logged in to book a package.";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_name = isset($_POST['package_name']) ? trim($_POST['package_name']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $travelers = isset($_POST['travelers']) ? intval($_POST['travelers']) : 1;
    $travel_date = isset($_POST['travel_date']) ? $_POST['travel_date'] : '';
    $special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';

    if (empty($package_name) || empty($travel_date)) {
        $_SESSION['toast_msg'] = "Required booking fields are missing.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Look up package price
    $price = 0;
    foreach ($packages as $pkg) {
        if ($pkg['name'] === $package_name) {
            $price = $pkg['price'];
            break;
        }
    }
    
    $total_price = $travelers * $price;
    
    // Generate confirmation reference number
    $ref_no = 'GT-' . rand(10000, 99999);

    if ($db_connected) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (ref_no, user_id, customer_name, customer_email, package_name, travelers, travel_date, special_requests, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([
                $ref_no,
                $_SESSION['user_id'],
                $name,
                $email,
                $package_name,
                $travelers,
                $travel_date,
                $special_requests,
                $total_price
            ]);
            $_SESSION['toast_msg'] = "Booking confirmed! Ref: #" . $ref_no;
        } catch (PDOException $e) {
            $_SESSION['toast_msg'] = "Booking failed. Database error.";
        }
    } else {
        // Save to Mock Session array
        $new_booking = [
            'ref' => '#' . $ref_no,
            'customer_name' => $name,
            'customer_email' => $email,
            'package_name' => $package_name,
            'travelers' => $travelers,
            'date' => $travel_date,
            'total' => $total_price,
            'status' => 'Pending'
        ];
        $_SESSION['mock_bookings'][] = $new_booking;
        $_SESSION['toast_msg'] = "Booking confirmed! Ref: #" . $ref_no . " (Simulated)";
    }

    // Redirect to customer dashboard to view bookings
    header("Location: dashboard.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
