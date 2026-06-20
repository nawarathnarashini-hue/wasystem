<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // 1. Fetch Package details
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$id]);
    $package = $stmt->fetch();

    if (!$package) {
        // Redirect to packages list if not found
        header('Location: packages.php');
        exit;
    }

    // 2. Fetch Package Itineraries
    $stmt = $pdo->prepare("SELECT * FROM itineraries WHERE package_id = ? ORDER BY id ASC");
    $stmt->execute([$id]);
    $itineraries = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

$booking_success = false;
$booking_ref = '';
$error_message = '';

// Handle Booking Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $travel_date = filter_input(INPUT_POST, 'travel_date', FILTER_SANITIZE_SPECIAL_CHARS);
    $travelers = isset($_POST['travelers']) ? intval($_POST['travelers']) : 1;
    $special_requests = filter_input(INPUT_POST, 'special_requests', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($travel_date && $travelers >= 1) {
        $total_cost = $travelers * $package['price'];
        $ref = 'GT-' . Math_random_ref(); // simulated reference generation function

        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (ref, user_id, package_id, travel_date, travelers, total_cost, status, special_requests) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)");
            $stmt->execute([$ref, $user_id, $package['id'], $travel_date, $travelers, $total_cost, $special_requests]);
            
            $booking_success = true;
            $booking_ref = $ref;
        } catch (PDOException $e) {
            $error_message = "Failed to submit booking: " . $e->getMessage();
        }
    } else {
        $error_message = "Invalid booking details!";
    }
}

// Generate random reference string
function Math_random_ref() {
    return 'GT-' . rand(10000, 99999);
}

include 'header.php';
?>

<!-- PACKAGE DETAILS PAGE -->
<section id="package-details" class="page-section">
    <div id="pkgDetailsHero" class="package-details-hero" style="background-image: linear-gradient(to top, rgba(0,0,0,0.7), transparent), url('<?php echo $package['image']; ?>');">
        <div class="package-details-content">
            <div id="pkgDetailsHeader">
                <span class="package-badge" style="position:static; display:inline-block; margin-bottom:10px;"><?php echo htmlspecialchars($package['badge']); ?></span>
                <h1><?php echo htmlspecialchars($package['name']); ?></h1>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($package['destination']); ?> • <i class="fas fa-clock"></i> <?php echo htmlspecialchars($package['duration']); ?> Days • <span class="rating" style="color:var(--warning);"><?php echo str_repeat('★', floor($package['rating'])); ?></span> <?php echo $package['rating']; ?></p>
            </div>
        </div>
    </div>
    
    <div style="max-width: 1200px; margin: 20px auto 0; padding: 0 20px;">
        <?php if ($booking_success): ?>
            <div class="alert alert-success" style="display:block;">Booking submitted successfully! Reference Number: <strong><?php echo htmlspecialchars($booking_ref); ?></strong>. You can view its status in your dashboard.</div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" style="display:block;"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
    </div>

    <div class="details-grid">
        <div class="details-main" id="pkgDetailsMain">
            <h3>Overview</h3>
            <p style="margin-bottom:30px; line-height:1.8; color:var(--gray);"><?php echo htmlspecialchars($package['description']); ?></p>
            
            <h3>Travel Itinerary</h3>
            <?php foreach ($itineraries as $day): ?>
                <div class="itinerary-item">
                    <div>
                        <div class="itinerary-day"><?php echo htmlspecialchars($day['day']); ?></div>
                        <h4 style="margin-bottom:8px;"><?php echo htmlspecialchars($day['title']); ?></h4>
                        <p style="color:var(--gray);"><?php echo htmlspecialchars($day['description']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <h3 style="margin-top:30px;">What's Included</h3>
            <div class="features-grid" style="margin-top:20px;">
                <div class="feature-card" style="text-align:left; padding:25px;">
                    <h4><i class="fas fa-hotel" style="color:var(--primary); margin-right:10px;"></i>Hotel</h4>
                    <p style="margin-top:10px; color:var(--gray);"><?php echo htmlspecialchars($package['hotel']); ?></p>
                </div>
                <div class="feature-card" style="text-align:left; padding:25px;">
                    <h4><i class="fas fa-bus" style="color:var(--primary); margin-right:10px;"></i>Transport</h4>
                    <p style="margin-top:10px; color:var(--gray);"><?php echo htmlspecialchars($package['transport']); ?></p>
                </div>
                <div class="feature-card" style="text-align:left; padding:25px;">
                    <h4><i class="fas fa-hiking" style="color:var(--primary); margin-right:10px;"></i>Activities</h4>
                    <p style="margin-top:10px; color:var(--gray);"><?php echo htmlspecialchars($package['activities']); ?></p>
                </div>
            </div>
            
            <h3 style="margin-top:30px;">Photo Gallery</h3>
            <div class="gallery-grid" style="margin-top:20px;">
                <div class="gallery-item"><img src="<?php echo $package['image']; ?>" alt="Gallery"></div>
                <div class="gallery-item"><img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400" alt="Beach"></div>
                <div class="gallery-item"><img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=400" alt="Resort"></div>
                <div class="gallery-item"><img src="https://images.unsplash.com/photo-1540206351-d6465b3ac5c1?w=400" alt="Adventure"></div>
            </div>
        </div>
        
        <div class="details-sidebar">
            <div class="booking-card">
                <h3>Book This Package</h3>
                <div id="pkgBookingInfo">
                    <div class="price-row"><span>Package Price (per person)</span><span>$<?php echo number_format($package['price']); ?></span></div>
                    <div class="price-row"><span>Duration</span><span><?php echo $package['duration']; ?> Days</span></div>
                    <div class="price-row"><span>Original Price</span><span style="text-decoration:line-through;">$<?php echo number_format($package['original_price']); ?></span></div>
                    <div class="price-row" style="border-top:2px solid #eee; padding-top:15px;"><span class="price-total">From $<?php echo number_format($package['price']); ?></span><span style="font-size:0.9rem; color:var(--gray);">per person</span></div>
                </div>
                <button class="btn btn-primary" style="width:100%; margin-top:20px;" onclick="openBookingOverlay()">Book Now</button>
            </div>
        </div>
    </div>
</section>

<!-- MODALS OVERLAY -->
<div class="modal-overlay" id="modalOverlay">
    <!-- Booking Modal -->
    <div class="modal" id="bookingModal" style="display:none; max-width:600px;">
        <div class="modal-header">
            <h3>Book Your Trip</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="bookingForm" method="POST" action="package-details.php?id=<?php echo $package['id']; ?>">
                    <input type="hidden" name="submit_booking" value="1">
                    <div class="form-row">
                        <label>Package</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($package['name']); ?>" readonly>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                        <div class="form-row">
                            <label>Full Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" readonly style="background-color:#e9ecef;">
                        </div>
                        <div class="form-row">
                            <label>Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" readonly style="background-color:#e9ecef;">
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                        <div class="form-row">
                            <label for="bookTravelersInput">Travelers *</label>
                            <input type="number" class="form-control" name="travelers" id="bookTravelersInput" min="1" value="1" required onchange="updateTotalCost(<?php echo $package['price']; ?>)">
                        </div>
                        <div class="form-row">
                            <label for="bookDateInput">Travel Date *</label>
                            <input type="date" class="form-control" name="travel_date" id="bookDateInput" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="bookRequestsInput">Special Requests</label>
                        <textarea class="form-control" name="special_requests" id="bookRequestsInput" rows="3"></textarea>
                    </div>
                    <div class="price-row" style="border-top:2px solid #eee; padding-top:15px; margin-top:15px;">
                        <span>Total Cost:</span>
                        <span class="price-total" id="bookTotalCostSpan">$<?php echo number_format($package['price']); ?></span>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:15px;">Confirm Booking</button>
                </form>
            <?php else: ?>
                <div style="text-align:center; padding: 20px;">
                    <i class="fas fa-lock" style="font-size:3rem; color:var(--gray); margin-bottom:20px;"></i>
                    <h4>Login Required</h4>
                    <p style="color:var(--gray); margin-bottom:20px;">Please login to your account to book this travel package.</p>
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function openBookingOverlay() {
        const overlay = document.getElementById('modalOverlay');
        const modal = document.getElementById('bookingModal');
        if (overlay && modal) {
            overlay.classList.add('active');
            modal.style.display = 'block';
            
            // Set min date
            const dateInput = document.getElementById('bookDateInput');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.min = today;
            }
        }
    }
    
    function updateTotalCost(pricePerPerson) {
        const travelersInput = document.getElementById('bookTravelersInput');
        const totalSpan = document.getElementById('bookTotalCostSpan');
        if (travelersInput && totalSpan) {
            const qty = parseInt(travelersInput.value) || 1;
            totalSpan.textContent = '$' + (qty * pricePerPerson).toLocaleString();
        }
    }
</script>

<?php include 'footer.php'; ?>
