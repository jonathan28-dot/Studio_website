<?php 
include 'includes/header.php'; 
include 'includes/config.php'; 

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    // If no errors, process form
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For now, we'll just store in database
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success = "Thank you for your message! We'll get back to you soon.";
        } else {
            $errors[] = "There was an error sending your message. Please try again.";
        }
        
        $stmt->close();
    }
}
?>
<style>
    <?php include 'assets/css/style.css'; ?>
    /* Responsive Map */
    .map-container {
        position: relative;
        padding-bottom: 30.25%;
        height: 0;
        overflow: hidden;
        margin-bottom: 30px;
    }
    .map-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    .map-btns {
        margin-bottom: 15px;
    }
    .map-btns a, .map-btns button {
        margin-right: 10px;
        padding: 7px 15px;
        border: none;
        background: #333;
        color: #fff;
        border-radius: 4px;
        text-decoration: none;
        cursor: pointer;
        font-size: 0.95em;
        transition: background 0.2s;
    }
    .map-btns a:hover, .map-btns button:hover {
        background: #555;
    }
    .inputs {
        margin-bottom: 10px;
    }
    .inputs input {
        width: 120px;
        margin-right: 6px;
    }
    .inputs label {
        font-size: 0.95em;
        margin-right: 2px;
    }

    /* Ensure contact-form and contact-info are side by side */
    .contact-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .contact-form, .contact-info {
        flex: 1 1 320px;
        min-width: 320px;
        box-sizing: border-box;
    }
    @media (max-width: 900px) {
        .contact-grid {
            flex-direction: column;
        }
    }

    /* Remove extra space above/below map-section */
    .map-section {
        margin-top: 0;
        padding-top: 0;
    }
    .map-section .container {
        padding-top: 0;
        margin-top: 0;
    }
</style>

<main class="contact-page">
    <section class="contact-hero">
        <div class="container " style="color: white">
            <h1>Get In Touch</h1>
            <p>We'd love to hear from you about your project or any questions you may have</p>
        </div>
    </section>

    <section class="contact-content">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-form">
                    <h2>Send Us a Message</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert error">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form action="contact.php" method="POST">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary">Send Message</button>
                    </form>
                </div>
                
                <div class="contact-info">
                    <h2>Contact Information</h2>
                    
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Studio Address</h3>
                            <p>123 Creative Street<br>Gichagi <br>Bridge international Academy</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h3>Phone</h3>
                            <p><a href="tel:+13235551234">(+254) 768-062-600</a></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p><a href="mailto:info@creativestudiospace.com">Josbosimwenda@gmail.com</a></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Studio Hours</h3>
                            <p>Monday - Friday: 9am - 9pm<br>
                            Saturday: 10am - 6pm<br>
                            Sunday: Closed</p>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="map-section">
        <div class="container">
            <h2>Find Us</h2>
            <div class="map-btns">
                <a
                    href="https://www.google.com/maps/dir/?api=1&destination=Bridge+International+Academy-Gicagi"
                    id="open-full-directions"
                    target="_blank"
                >
                    Open Directions in Google Maps
                </a>
                <button onclick="showDirectionsFromMyLocation();return false;">
                    Show Directions From My Location
                </button>
            </div>
            <div class="map-container" style="height:450px;">
                <iframe
                    id="gmap-iframe"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3142.243164219315!2d36.74456300925423!3d-1.2746790356054905!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1985030880f7%3A0x58c7ee617aa3be3c!2sBridge%20International%20Academy-Gicagi!5e1!3m2!1sen!2ske!4v1749023428517!5m2!1sen!2ske"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        </div>
    </section>
</main>

<script>
function showDirectionsFromMyLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            var origin = lat + ',' + lng;
            var destination = "Bridge+International+Academy-Gicagi";
            // Update iframe for directions from current location
            var directionsEmbedUrl = "https://www.google.com/maps?&output=embed&saddr=" + origin + "&daddr=" + destination;
            document.getElementById('gmap-iframe').src = directionsEmbedUrl;
            // Update the "Open Directions in Google Maps" link as well
            var openFullUrl = "https://www.google.com/maps/dir/?api=1&origin=" + origin + "&destination=" + destination;
            document.getElementById('open-full-directions').href = openFullUrl;
        }, function() {
            alert('Unable to retrieve your location.');
        });
    } else {
        alert('Geolocation is not supported by your browser.');
    }
}
</script>

<?php include 'includes/footer.php'; ?>