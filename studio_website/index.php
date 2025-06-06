<?php 
include 'includes/header.php'; 
include 'includes/config.php'; 
?>
<style>
    <?php include 'assets/css/style.css'; ?>
</style>

<main>
    <section class="hero">
        <div class="hero-content">
            <h1>Creative Studio Space</h1>
            <p>Professional photography, recording, and production services</p>
            <a href="booking.php" class="btn-primary">Book a Session</a>
        </div>
    </section>

    <section class="services-highlight">
        <h2>Our Services</h2>
        <div class="services-grid">
            <div class="service-card">
                <img src="assets/images/download (4).jpg" alt="Photography">
                <h3>Photography</h3>
                <p>Portrait, product, and event photography services</p>
            </div>
            <div class="service-card">
                <img src="assets/images/download (2).jpg" alt="Recording">
                <h3>Recording Studio</h3>
                <p>Professional audio recording and production</p>
            </div>
            <div class="service-card">
                <img src="assets/images/images (1).jpg" alt="Videography">
                <h3>Videography</h3>
                <p>Music videos, commercials, and content creation</p>
            </div>
        </div>
    </section>

    <section class="featured-work">
        <h2>Featured Work</h2>
        <div class="gallery-preview">
            <?php
            // Get 4 random gallery items
            $sql = "SELECT * FROM gallery ORDER BY RAND() LIMIT 3";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="gallery-item">';
                    echo '<img src="uploads/gallery/'.$row['image_path'].'" alt="'.$row['title'].'">';
                    echo '<div class="overlay"><h3>'.$row['title'].'</h3></div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <a href="gallery.php" class="btn-secondary">View Full Gallery</a>
    </section>
</main>

<?php include 'includes/footer.php'; ?>