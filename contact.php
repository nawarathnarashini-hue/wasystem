<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$inquiry_success = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Check if logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $name = $_SESSION['user_name'];
        $email = $_SESSION['user_email'];
    } else {
        $user_id = NULL;
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    }

    if ($name && $email && $subject && $message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO inquiries (user_id, name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$user_id, $name, $email, $subject, $message]);
            $inquiry_success = true;
        } catch (PDOException $e) {
            $error_message = "Failed to submit inquiry: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill in all required fields!";
    }
}

include 'header.php';
?>

<!-- CONTACT PAGE -->
<section id="contact" class="page-section">
    <div class="about-hero" style="height: 40vh; min-height: 300px;">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you. Get in touch with our team.</p>
    </div>
    <div class="contact-grid">
        <div class="contact-info">
            <h2>Get In Touch</h2>
            <p style="color:var(--gray); margin-bottom:30px;">Have questions about a package? Need a custom itinerary? Our travel experts are here to help.</p>
            
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <h4>Office Address</h4>
                    <p>123 Beach Road, Negombo<br>Sri Lanka, 11500</p>
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <div>
                    <h4>Phone</h4>
                    <p>+94 31 222 3456<br>+94 77 123 4567</p>
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <h4>Email</h4>
                    <p>info@globetrek.lk<br>bookings@globetrek.lk</p>
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-clock"></i>
                <div>
                    <h4>Working Hours</h4>
                    <p>Mon - Sat: 9:00 AM - 6:00 PM<br>Sunday: Closed</p>
                </div>
            </div>
            
            <div class="map-container">
                <i class="fas fa-map-marked-alt" style="font-size:3rem; margin-bottom:10px;"></i>
                <p>Google Map Integration Area</p>
            </div>
        </div>
        
        <div class="contact-form">
            <h3>Send an Inquiry</h3>
            
            <?php if ($inquiry_success): ?>
                <div class="alert alert-success" style="display:block;">Your message has been sent successfully! Our team will contact you shortly.</div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" style="display:block;"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form id="contactForm" method="POST" action="contact.php">
                <input type="hidden" name="submit_inquiry" value="1">
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="form-row">
                        <label for="contactName">Full Name *</label>
                        <input type="text" class="form-control" name="name" id="contactName" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div class="form-row">
                        <label for="contactEmail">Email Address *</label>
                        <input type="email" class="form-control" name="email" id="contactEmail" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                <?php endif; ?>

                <div class="form-row">
                    <label for="contactSubject">Subject *</label>
                    <input type="text" class="form-control" name="subject" id="contactSubject" required value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>
                <div class="form-row">
                    <label for="contactMessage">Message *</label>
                    <textarea class="form-control" name="message" id="contactMessage" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
