<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Creative Studio Space'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($page) && $page == 'gallery'): ?>
        <link rel="stylesheet" href="assets/css/gallery.css">
    <?php endif; ?>
    <style>
    <?php include 'assets/css/style.css'; ?>
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">Creative<span>Studio</span></a>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="booking.php">Booking</a></li>
                    <li><a href="contact.php">Contact</a></li> 
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <li><a href="admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>