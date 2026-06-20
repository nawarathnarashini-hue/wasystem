<?php
require_once 'db.php';

try {
    // Query destinations dynamically from seeded packages
    $stmt = $pdo->query("SELECT DISTINCT destination, image FROM packages LIMIT 6");
    $destinations = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

include 'header.php';
?>

<!-- TRAVEL GUIDES PAGE -->
<section id="guides" class="page-section">
    <div class="about-hero" style="height: 40vh; min-height: 300px;">
        <h1>Travel Guides</h1>
        <p>Expert tips and advice for your next adventure</p>
    </div>
    <div class="section">
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('tips')">Travel Tips</button>
            <button class="tab-btn" onclick="switchTab('visa')">Visa Info</button>
            <button class="tab-btn" onclick="switchTab('packing')">Packing Checklist</button>
            <button class="tab-btn" onclick="switchTab('destinations')">Recommendations</button>
        </div>
        
        <div id="tips" class="tab-content active">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-passport"></i></div>
                    <h3>Document Safety</h3>
                    <p>Always keep digital copies of your passport, visa, and insurance in cloud storage and email.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <h3>Money Matters</h3>
                    <p>Inform your bank before traveling. Carry a mix of local currency and cards for flexibility.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-first-aid"></i></div>
                    <h3>Health First</h3>
                    <p>Pack a basic first-aid kit and check vaccination requirements at least 4 weeks before departure.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-wifi"></i></div>
                    <h3>Stay Connected</h3>
                    <p>Buy a local SIM card or portable WiFi device. Download offline maps before you travel.</p>
                </div>
            </div>
        </div>
        
        <div id="visa" class="tab-content">
            <div class="content-card">
                <div class="content-card-body">
                    <h3 style="margin-bottom:20px;">Visa Requirements by Destination</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>Country</th><th>Requirement</th><th>Processing Time</th><th>Notes</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Sri Lanka</td><td>ETA / Visa on Arrival</td><td>24-48 hours</td><td>30 days standard</td></tr>
                                <tr><td>Thailand</td><td>Visa Exempt (30 days)</td><td>Immediate</td><td>Passport valid 6 months</td></tr>
                                <tr><td>Bali (Indonesia)</td><td>Visa on Arrival</td><td>At airport</td><td>$35 USD fee</td></tr>
                                <tr><td>Maldives</td><td>Free on Arrival (30 days)</td><td>Immediate</td><td>Return ticket required</td></tr>
                                <tr><td>Dubai (UAE)</td><td>Pre-approved Visa</td><td>3-5 days</td><td>Apply through airline</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="packing" class="tab-content">
            <div class="features-grid">
                <div class="feature-card" style="text-align:left;">
                    <h3><i class="fas fa-tshirt" style="color:var(--primary); margin-right:10px;"></i>Clothing</h3>
                    <ul style="list-style:none; margin-top:15px; color:var(--gray);">
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Lightweight, breathable fabrics</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Modest attire for temples</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Comfortable walking shoes</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Swimwear & cover-up</li>
                    </ul>
                </div>
                <div class="feature-card" style="text-align:left;">
                    <h3><i class="fas fa-soap" style="color:var(--primary); margin-right:10px;"></i>Essentials</h3>
                    <ul style="list-style:none; margin-top:15px; color:var(--gray);">
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Sunscreen (SPF 50+)</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Insect repellent</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Universal adapter</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Portable charger</li>
                    </ul>
                </div>
                <div class="feature-card" style="text-align:left;">
                    <h3><i class="fas fa-file-alt" style="color:var(--primary); margin-right:10px;"></i>Documents</h3>
                    <ul style="list-style:none; margin-top:15px; color:var(--gray);">
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Passport & copies</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Travel insurance</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Hotel confirmations</li>
                        <li><i class="fas fa-check" style="color:var(--success); margin-right:8px;"></i>Emergency contacts</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div id="destinations" class="tab-content">
            <div class="content-card">
                <div class="content-card-body">
                    <h3 style="margin-bottom:20px;">Top Recommended Destinations</h3>
                    <div class="destinations-grid">
                        <?php foreach ($destinations as $d): ?>
                            <div class="destination-card" style="height:200px;" onclick="window.location.href='packages.php?dest=<?php echo urlencode($d['destination']); ?>'">
                                <img src="<?php echo $d['image']; ?>" alt="<?php echo htmlspecialchars($d['destination']); ?>">
                                <div class="destination-overlay">
                                    <h3><?php echo htmlspecialchars($d['destination']); ?></h3>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
