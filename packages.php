<?php
require_once 'db.php';

// Retrieve filter parameters
$dest = isset($_GET['dest']) ? trim($_GET['dest']) : '';
$dur = isset($_GET['dur']) ? trim($_GET['dur']) : '';
$price = isset($_GET['price']) ? trim($_GET['price']) : '';

try {
    // Build dynamic SQL query
    $sql = "SELECT * FROM packages WHERE 1=1";
    $params = [];

    if ($dest !== '') {
        $sql .= " AND (destination LIKE :dest OR name LIKE :dest)";
        $params[':dest'] = '%' . $dest . '%';
    }

    if ($dur !== '') {
        // Handle dropdown search mappings
        if ($dur == '3') {
            $sql .= " AND duration BETWEEN 3 AND 5";
        } elseif ($dur == '7') {
            $sql .= " AND duration BETWEEN 7 AND 10";
        } elseif ($dur == '14') {
            $sql .= " AND duration >= 14";
        } else {
            $sql .= " AND duration = :dur";
            $params[':dur'] = intval($dur);
        }
    }

    if ($price !== '') {
        $sql .= " AND price <= :price";
        $params[':price'] = intval($price);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all_packages = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

include 'header.php';
?>

<!-- PACKAGES PAGE -->
<section id="packages" class="page-section">
    <div class="about-hero" style="height: 40vh; min-height: 300px;">
        <h1>Tour Packages</h1>
        <p>Find your perfect getaway from our curated collection</p>
    </div>
    <div class="section">
        <!-- Server-side form for filters -->
        <form method="GET" action="packages.php" class="filters-bar">
            <div class="filter-group">
                <label for="filterDest">Destination</label>
                <input type="text" class="form-control" name="dest" id="filterDest" placeholder="Search destination..." value="<?php echo htmlspecialchars($dest); ?>">
            </div>
            <div class="filter-group">
                <label for="filterDuration">Duration</label>
                <select class="form-control" name="dur" id="filterDuration">
                    <option value="">All Durations</option>
                    <option value="3" <?php echo ($dur == '3') ? 'selected' : ''; ?>>3-5 Days</option>
                    <option value="5" <?php echo ($dur == '5') ? 'selected' : ''; ?>>5 Days</option>
                    <option value="7" <?php echo ($dur == '7') ? 'selected' : ''; ?>>7-10 Days</option>
                    <option value="10" <?php echo ($dur == '10') ? 'selected' : ''; ?>>10 Days</option>
                    <option value="14" <?php echo ($dur == '14') ? 'selected' : ''; ?>>14+ Days</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterPrice">Max Price ($)</label>
                <input type="number" class="form-control" name="price" id="filterPrice" placeholder="Enter max price" value="<?php echo htmlspecialchars($price); ?>">
            </div>
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <button type="submit" class="btn btn-primary" style="height: 46px;"><i class="fas fa-search"></i> Filter</button>
                <a href="packages.php" class="btn btn-outline" style="height: 46px; display: flex; align-items: center; justify-content: center;">Reset</a>
            </div>
        </form>

        <div class="packages-grid" id="allPackages">
            <?php if (count($all_packages) > 0): ?>
                <?php foreach ($all_packages as $p): ?>
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
                            <p style="color:var(--gray); font-size:0.9rem; margin-bottom:10px;"><?php echo htmlspecialchars(substr($p['description'], 0, 80)); ?>...</p>
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
            <?php else: ?>
                <div class="empty-state" style="grid-column:1/-1;">
                    <i class="fas fa-search"></i>
                    <h3>No packages found</h3>
                    <p>Try adjusting your search criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
