    
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3>About Us</h3>
                <p>Creative Studio Space is a premier multimedia production facility offering photography, recording, and video services.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="gallery.php">Portfolio</a></li>
                    <li><a href="booking.php">Book a Session</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h3>Services</h3>
                <ul class="footer-links">
                    <li><a href="services.php#photography">Photography</a></li>
                    <li><a href="services.php#recording">Recording Studio</a></li>
                    <li><a href="services.php#video">Video Production</a></li>
                    <li><a href="services.php#events">Event Coverage</a></li>
                    <li><a href="services.php#editing">Editing Services</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h3>Contact Info</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Creative Street, Gichagi , Bridge International Academy</p>
                <p><i class="fas fa-phone"></i> (+254) 768-062-600</p>
                <p><i class="fas fa-envelope"></i> josbosimwenda@gmail.com</p>
                <p><i class="fas fa-clock"></i> Mon-Fri: 8am-9pm, Sat: 10am-6pm</p>
            </div>
        </div>
        
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> Creative Studio Space. All Rights Reserved.</p>
        </div>
    </footer>
    
    <script src="assets/js/main.js"></script>
    <?php if (isset($page) && $page == 'booking'): ?>
        <script src="assets/js/booking.js"></script>
    <?php endif; ?>
</body>
</html>