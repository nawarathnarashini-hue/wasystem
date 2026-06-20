<?php
require_once 'db.php';

try {
    // Retrieve first 3 featured packages
    $stmt = $pdo->query("SELECT * FROM packages LIMIT 3");
    $featured_packages = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

// Destinations list (for dynamic PHP render)
$destinations_list = [
    ["name" => "Bali", "country" => "Indonesia", "image" => "https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=600", "tours" => 12],
    ["name" => "Maldives", "country" => "Maldives", "image" => "https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=600", "tours" => 8],
    ["name" => "Dubai", "country" => "UAE", "image" => "https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=600", "tours" => 6],
    ["name" => "Sri Lanka", "country" => "Sri Lanka", "image" => "https://images.unsplash.com/photo-1588258525935-4b626b604b6e?w=600", "tours" => 15],
    ["name" => "Thailand", "country" => "Thailand", "image" => "https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=600", "tours" => 10],
    ["name" => "Singapore", "country" => "Singapore", "image" => "https://images.unsplash.com/photo-1525625293386-3f8f99389edd?w=600", "tours" => 5]
];

include 'header.php';
?>

<!-- HOME PAGE -->
<section id="home" class="page-section">
    <div class="hero">
        <div class="hero-content">
            <h1>Discover Your Next Adventure</h1>
            <p>Explore the world's most breathtaking destinations with GlobeTrek Adventures</p>
            <div class="search-box">
                <div class="form-group">
                    <label>Destination</label>
                    <input type="text" class="form-control" id="heroDest" placeholder="Where do you want to go?">
                </div>
                <div class="form-group">
                    <label>Duration</label>
                    <select class="form-control" id="heroDuration">
                        <option value="">Any Duration</option>
                        <option value="5">3-5 Days</option>
                        <option value="7">7-10 Days</option>
                        <option value="14">14+ Days</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Max Price</label>
                    <select class="form-control" id="heroPrice">
                        <option value="">Any Price</option>
                        <option value="500">Under $500</option>
                        <option value="1000">Under $1,000</option>
                        <option value="2000">Under $2,000</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="heroSearch()" style="height:46px;">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>

    <div class="stats-bar">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>150+</h3>
                <p>Destinations</p>
            </div>
            <div class="stat-item">
                <h3>50k+</h3>
                <p>Happy Travelers</p>
            </div>
            <div class="stat-item">
                <h3>12+</h3>
                <p>Years Experience</p>
            </div>
            <div class="stat-item">
                <h3>4.9</h3>
                <p>Average Rating</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2>Popular Destinations</h2>
            <p>Explore our most sought-after travel destinations around the globe</p>
        </div>
        <div class="destinations-grid">
            <?php foreach ($destinations_list as $d): ?>
                <div class="destination-card" onclick="window.location.href='packages.php?dest=<?php echo urlencode($d['name']); ?>'">
                    <img src="<?php echo $d['image']; ?>" alt="<?php echo htmlspecialchars($d['name']); ?>">
                    <div class="destination-overlay">
                        <h3><?php echo htmlspecialchars($d['name']); ?></h3>
                        <p><?php echo htmlspecialchars($d['country']); ?> • <?php echo $d['tours']; ?> Tours</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="section" style="background: var(--white); padding: 80px 20px; max-width: 100%;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="section-header">
                <h2>Featured Tour Packages</h2>
                <p>Handpicked experiences for unforgettable memories</p>
            </div>
            <div class="packages-grid">
                <?php foreach ($featured_packages as $p): ?>
                    <div class="package-card" onclick="window.location.href='package-details.php?id=<?php echo $p['id']; ?>'">
                        <div class="package-image" style="background-image:url('<?php echo $p['image']; ?>')">
                            <span class="package-badge"><?php echo htmlspecialchars($p['badge']); ?></span>
                        </div>
                        <div class="package-content">
                            <div class="package-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($p['destination']); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($p['duration']); ?> Days</span>
                            </div>
                            <h3 class="package-title"><?php echo htmlspecialchars($p['name']); ?></h3>
                            <p style="color:var(--gray); font-size:0.9rem; margin-bottom:10px;"><?php echo htmlspecialchars(substr($p['description'], 0, 85)); ?>...</p>
                            <div class="package-price">
                                <span class="price-tag">$<?php echo number_format($p['price']); ?></span>
                                <span class="price-original">$<?php echo number_format($p['original_price']); ?></span>
                            </div>
                            <div class="package-footer">
                                <span class="rating"><?php echo str_repeat('★', floor($p['rating'])); ?><?php echo ($p['rating'] - floor($p['rating']) > 0) ? '½' : ''; ?> <small>(<?php echo $p['reviews']; ?>)</small></span>
                                <button class="btn btn-primary btn-sm">View Details</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <a href="packages.php" class="btn btn-primary">View All Packages</a>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2>Why Choose Us</h2>
            <p>We make your travel dreams a reality with exceptional service</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Safe Travel</h3>
                <p>Your safety is our priority with 24/7 support and verified partners</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-tag"></i></div>
                <h3>Best Prices</h3>
                <p>Competitive rates with no hidden fees and price match guarantee</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h3>24/7 Support</h3>
                <p>Round-the-clock assistance wherever you are in the world</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-map-marked-alt"></i></div>
                <h3>Expert Guides</h3>
                <p>Local experts with deep knowledge of every destination</p>
            </div>
        </div>
    </div>

    <div class="testimonials-container section" style="background: #f1f5f9; max-width: 100%;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div class="section-header">
                <h2>What Our Travelers Say</h2>
                <p>Real experiences from real adventurers</p>
            </div>
            <div style="max-width: 800px; margin: 0 auto; overflow: hidden;">
                <div class="testimonials-slider" id="testimonialSlider">
                    <div class="testimonial-card">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150" class="testimonial-avatar" alt="Sarah">
                        <p class="testimonial-text">"The Bali Adventure was absolutely magical! GlobeTrek handled everything perfectly from airport transfers to hotel bookings. Will definitely book again!"</p>
                        <div class="testimonial-author">Sarah Johnson</div>
                        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    </div>
                    <div class="testimonial-card">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150" class="testimonial-avatar" alt="Michael">
                        <p class="testimonial-text">"Professional service and amazing itinerary. The Sri Lanka Heritage Tour exceeded all expectations. Our guide was incredibly knowledgeable."</p>
                        <div class="testimonial-author">Michael Chen</div>
                        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    </div>
                    <div class="testimonial-card">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150" class="testimonial-avatar" alt="Emma">
                        <p class="testimonial-text">"Maldives Escape was a dream come true. The resort selection was perfect and the pricing was better than booking directly. Highly recommended!"</p>
                        <div class="testimonial-author">Emma Williams</div>
                        <div class="rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                    </div>
                </div>
            </div>
            <div class="slider-controls">
                <button class="slider-btn" onclick="moveSlide(-1)"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn" onclick="moveSlide(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <div class="newsletter">
        <h2>Subscribe to Our Newsletter</h2>
        <p>Get exclusive deals and travel inspiration delivered to your inbox</p>
        <form class="newsletter-form" onsubmit="event.preventDefault(); showToast('Thank you for subscribing!');">
            <input type="email" placeholder="Enter your email address" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>
