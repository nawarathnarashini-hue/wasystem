-- Create Database
CREATE DATABASE IF NOT EXISTS globetrek_db;
USE globetrek_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'staff', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Users (Passwords are: customer123, staff123, admin123 respectively, hashed using password_hash)
INSERT INTO users (name, email, phone, password, role) VALUES
('John Doe', 'customer@globetrek.lk', '+94 77 123 4567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Amal Perera', 'staff@globetrek.lk', '+94 31 222 5555', '$2y$10$X.v.f6aN/W3tH5g72rE6euzs2yS2lZ/bFnM5r6zJ2iS.Q0D3vV2yC', 'staff'),
('Administrator', 'admin@globetrek.lk', '+94 11 222 3333', '$2y$10$m92U.R.kS0nZ0sC6X0oR2euzS6m3rW/sFhM5r6zJ2iS.Q0D3vV2yC', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- 2. Tour Packages Table
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    duration INT NOT NULL,
    price INT NOT NULL,
    original_price INT,
    rating DECIMAL(3,2) DEFAULT 5.0,
    reviews INT DEFAULT 0,
    image VARCHAR(255),
    badge VARCHAR(50),
    description TEXT NOT NULL,
    hotel VARCHAR(255),
    transport VARCHAR(255),
    activities TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Packages
INSERT INTO packages (id, name, destination, duration, price, original_price, rating, reviews, image, badge, description, hotel, transport, activities) VALUES
(1, 'Bali Adventure', 'Bali, Indonesia', 7, 999, 1299, 4.8, 234, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800', 'Popular', 'Experience the magic of Bali with temple visits, rice terrace walks, and beach relaxation.', '4-star resorts in Ubud and Seminyak', 'Private AC vehicle, fast boat to Nusa Penida', 'Temple visits, cooking class, snorkeling, spa'),
(2, 'Maldives Escape', 'Malé, Maldives', 5, 1200, 1500, 4.9, 189, 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=800', 'Luxury', 'Ultimate luxury island getaway with overwater villa stay and world-class diving.', '5-star Overwater Villa Resort', 'Speedboat transfers', 'Snorkeling, diving, spa, sunset cruise'),
(3, 'Dubai Luxury Tour', 'Dubai, UAE', 5, 1800, 2200, 4.7, 312, 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800', 'Premium', 'Experience the glamour of Dubai with desert safari, Burj Khalifa, and luxury shopping.', '5-star Downtown Dubai Hotel', 'Luxury private vehicle', 'Desert safari, city tour, mosque visit, shopping'),
(4, 'Sri Lanka Heritage Tour', 'Colombo, Sri Lanka', 10, 850, 1100, 4.8, 456, 'https://images.unsplash.com/photo-1588258525935-4b626b604b6e?w=800', 'Best Value', 'Discover ancient cities, tea plantations, wildlife, and pristine beaches in our home country.', 'Boutique hotels and eco-lodges', 'Private vehicle with driver-guide', 'Safari, hiking, cultural sites, whale watching'),
(5, 'Thailand Beach Holiday', 'Phuket, Thailand', 7, 750, 950, 4.6, 378, 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800', 'Trending', 'Island hopping in Phuket with Phi Phi islands, James Bond island, and vibrant nightlife.', '4-star Beach Resort', 'Speedboat, private vehicle', 'Island hopping, snorkeling, elephant sanctuary, food tour')
ON DUPLICATE KEY UPDATE id=id;

-- 3. Itineraries Table
CREATE TABLE IF NOT EXISTS itineraries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    day VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Itineraries
INSERT INTO itineraries (package_id, day, title, description) VALUES
(1, 'Day 1', 'Arrival in Denpasar', 'Airport pickup, transfer to Ubud, welcome dinner'),
(1, 'Day 2', 'Ubud Exploration', 'Monkey Forest, Tegalalang Rice Terrace, traditional dance'),
(1, 'Day 3', 'Temple Tour', 'Tirta Empul, Besakih Temple, holy spring purification'),
(1, 'Day 4', 'Beach Day', 'Transfer to Seminyak, sunset at Tanah Lot'),
(1, 'Day 5', 'Nusa Penida', 'Day trip to Kelingking Beach and Angel''s Billabong'),
(1, 'Day 6', 'Cooking Class', 'Traditional Balinese cooking experience'),
(1, 'Day 7', 'Departure', 'Spa treatment, airport transfer'),
(2, 'Day 1', 'Arrival', 'Speedboat transfer to resort, check-in to overwater villa'),
(2, 'Day 2', 'Marine Life', 'Guided snorkeling tour, dolphin watching cruise'),
(2, 'Day 3', 'Island Hopping', 'Visit local island, cultural experience, sandbank picnic'),
(2, 'Day 4', 'Wellness', 'Spa treatment, yoga session, sunset cruise'),
(2, 'Day 5', 'Departure', 'Breakfast, departure transfer'),
(3, 'Day 1', 'Arrival', 'Airport pickup, Dubai Marina dinner cruise'),
(3, 'Day 2', 'City Tour', 'Burj Khalifa, Dubai Mall, fountain show'),
(3, 'Day 3', 'Desert Safari', 'Dune bashing, camel ride, Bedouin camp dinner'),
(3, 'Day 4', 'Abu Dhabi', 'Sheikh Zayed Mosque, Louvre Abu Dhabi'),
(3, 'Day 5', 'Departure', 'Gold Souk visit, airport transfer'),
(4, 'Day 1', 'Negombo', 'Arrival, beach relaxation, fish market visit'),
(4, 'Day 2', 'Sigiriya', 'Climb Lion Rock, village experience'),
(4, 'Day 3', 'Polonnaruwa', 'Ancient city ruins, cycling tour'),
(4, 'Day 4', 'Kandy', 'Temple of the Tooth, cultural dance'),
(4, 'Day 5', 'Nuwara Eliya', 'Tea plantation visit, scenic train ride'),
(4, 'Day 6', 'Ella', 'Nine Arch Bridge, Little Adam''s Peak hike'),
(4, 'Day 7', 'Yala Safari', 'Jeep safari, leopard spotting'),
(4, 'Day 8', 'Mirissa', 'Whale watching, beach time'),
(4, 'Day 9', 'Galle', 'Fort walking tour, turtle hatchery'),
(4, 'Day 10', 'Departure', 'Colombo city tour, airport transfer'),
(5, 'Day 1', 'Phuket', 'Arrival, Patong Beach exploration'),
(5, 'Day 2', 'Phi Phi Islands', 'Speedboat tour, Maya Bay, snorkeling'),
(5, 'Day 3', 'James Bond Island', 'Kayaking in Phang Nga Bay'),
(5, 'Day 4', 'Elephant Sanctuary', 'Ethical elephant experience, Big Buddha'),
(5, 'Day 5', 'Old Phuket', 'Old Town food tour, weekend market'),
(5, 'Day 6', 'Free Day', 'Beach relaxation or optional spa day'),
(5, 'Day 7', 'Departure', 'Airport transfer');

-- 4. Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    package_id INT,
    travel_date DATE NOT NULL,
    travelers INT NOT NULL,
    total_cost INT NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled') DEFAULT 'Pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Bookings
INSERT INTO bookings (ref, user_id, package_id, travel_date, travelers, total_cost, status) VALUES
('GT-24001', 1, 1, '2026-07-15', 2, 1998, 'Confirmed'),
('GT-24015', 1, 2, '2026-08-20', 2, 2400, 'Pending');

-- 5. Inquiries Table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('Pending', 'Replied') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Inquiries
INSERT INTO inquiries (user_id, name, email, subject, message, status) VALUES
(1, 'John Doe', 'customer@globetrek.lk', 'Custom Bali Itinerary', 'Can I get a custom itinerary for a group of 10 people in Bali?', 'Replied'),
(NULL, 'Guest Visitor', 'guest@example.com', 'Group Discount Query', 'Do you offer special discounts for student group bookings?', 'Pending');
