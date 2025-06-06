<?php
include 'includes/config.php'; 
include 'includes/header.php';
?>
<style>
 <?php include 'assets/css/gallery.css'; ?>
</style>

<main class="gallery-page">
    <section class="gallery-hero">
        <div class="container">
            <h1>Our Portfolio</h1>
            <p>Explore our latest work across photography, recording, and video production</p>
        </div>
    </section>

    <section class="gallery-controls">
        <div class="container">
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Work</button>
                <button class="filter-btn" data-filter="photography">Photography</button>
                <button class="filter-btn" data-filter="recording">Recording</button>
                <button class="filter-btn" data-filter="video">Video</button>
            </div>
        </div>
    </section>

    <section class="gallery-container">
        <div class="container">
            <div class="gallery-grid">
                <?php
                // Get all gallery items from database
                $sql = "SELECT * FROM gallery ORDER BY created_at DESC";
                $result = $conn->query($sql); // Use $conn not $mysqli
                
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="gallery-item" data-category="'.htmlspecialchars($row['category']).'">';
                        echo '<img src="uploads/gallery/'.htmlspecialchars($row['image_path']).'" alt="'.htmlspecialchars($row['title']).'">';
                        echo '<div class="gallery-overlay">';
                        echo '<h3>'.htmlspecialchars($row['title']).'</h3>';
                        echo '<p>'.ucfirst(htmlspecialchars($row['category'])).'</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="no-items">No gallery items found. Check back soon!</p>';
                }
                ?>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>