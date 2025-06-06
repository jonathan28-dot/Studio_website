<?php 
include 'includes/header.php'; 
include 'includes/config.php'; 

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $service = $_POST['service'];
    $date = $_POST['date'];
    $message = $_POST['message'];
    
    // Validate and sanitize inputs
    // (Add proper validation here)
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO bookings (name, email, phone, service, date, message, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssss", $name, $email, $phone, $service, $date, $message);
    
    if ($stmt->execute()) {
        $success = "Your booking request has been submitted! We'll contact you soon.";
    } else {
        $error = "There was an error processing your request. Please try again.";
    }
    
    $stmt->close();
}
?>
<style>
    <?php include 'assets/css/style.css'; ?>
</style>

<main class="booking-page">
    <h1>Book a Session</h1>
    
    <?php if (isset($success)): ?>
        <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form action="booking.php" method="POST" class="booking-form">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        
        <div class="form-group">
            <label for="service">Service</label>
            <select id="service" name="service" required>
                <option value="">Select a service</option>
                <option value="Photography">Photography Session</option>
                <option value="Recording">Recording Session</option>
                <option value="Video">Video Production</option>
                <option value="Event">Event Coverage</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="date">Preferred Date</label>
            <input type="date" id="date" name="date" required>
        </div>
        
        <div class="form-group">
            <label for="message">Special Requests</label>
            <textarea id="message" name="message" rows="4"></textarea>
        </div>
        
        <button type="submit" class="btn-primary">Submit Booking</button>
    </form>
    
    <section class="pricing">
        <h2>Our Pricing</h2>
        <div class="pricing-grid">
            <div class="pricing-card">
                <h3>Photography</h3>
                <p class="price">$150/hr</p>
                <ul>
                    <li>Professional lighting setup</li>
                    <li>High-resolution images</li>
                    <li>Basic editing included</li>
                </ul>
            </div>
            <div class="pricing-card">
                <h3>Recording</h3>
                <p class="price">$200/hr</p>
                <ul>
                    <li>State-of-the-art equipment</li>
                    <li>Sound engineer included</li>
                    <li>Basic mixing</li>
                </ul>
            </div>
            <div class="pricing-card">
                <h3>Video Production</h3>
                <p class="price">$250/hr</p>
                <ul>
                    <li>4K video recording</li>
                    <li>Professional editing</li>
                    <li>Color grading</li>
                </ul>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>