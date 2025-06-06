<?php
// Always set a fallback image (adjust the path to your actual fallback if needed)
$admin_profile_image = 'admin/uploads/admin_profiles';
$admin_profile_image = 0;
$new_messages_count = 0;
$new_bookings_count = 0;

if (isset($_SESSION['admin_id'])) {
    include_once '../includes/config.php';
    $stmt = $conn->prepare("SELECT profile_image FROM admin_users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $stmt->bind_result($db_profile_image);
    if ($stmt->fetch() && !empty($db_profile_image)) {
        // If the image path is absolute (http...) use as is
        if (preg_match('/^https?:\/\//', $db_profile_image)) {
            $admin_profile_image = htmlspecialchars($db_profile_image);
        } else {
            // Relative path - make sure it works from the sidebar's location
            // Check if file exists relative to this file
            $relative_path = (strpos($db_profile_image, '../') === 0) ? $db_profile_image : '../' . ltrim($db_profile_image, '/');
            if (file_exists($relative_path)) {
                $admin_profile_image = $relative_path;
            } elseif (file_exists($db_profile_image)) {
                $admin_profile_image = $db_profile_image;
            } else {
                $admin_profile_image = $default_profile_image;
            }
        }
    }
    $stmt->close();

    // Count new/unread messages
    $sql = "SELECT COUNT(*) FROM contacts WHERE is_read = 0";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        $new_messages_count = (int)$row[0];
    }

    // Count new/pending bookings
    $sql2 = "SELECT COUNT(*) FROM bookings WHERE status = 'pending'";
    $result2 = $conn->query($sql2);
    if ($result2) {
        $row2 = $result2->fetch_row();
        $new_bookings_count = (int)$row2[0];
    }
}
?>

<style>
/* Sidebar Styles (unchanged, as in your code) */
.sidebar {
    width: 250px;
    background: linear-gradient(to bottom, #283149, #ff6600);
    color: white;
    padding: 0;
    position: fixed;
    height: 100vh;
    box-shadow: 2px 0 10px rgba(0,0,0,0.08);
    z-index: 100;
    display: flex;
    flex-direction: column;
}
.sidebar .logo-area {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 22px 25px 14px 25px;
    border-bottom: 1px solid #1a2235;
    margin-bottom: 10px;
}
.sidebar .logo-img {
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.sidebar .logo-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.sidebar .logo-text {
    font-size: 1.25rem;
    font-weight: bold;
    line-height: 1.1;
    color: #fff;
    letter-spacing: .5px;
}
.sidebar .logo-text span {
    display: block;
    font-size: 0.9rem;
    font-weight: 400;
    color: #dbe2ef;
    margin-top: 2px;
}
.sidebar .admin-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    flex: 1;
}
.sidebar .admin-menu-section-title {
    font-size: 0.9rem;
    color: #bfc5d2;
    font-weight: 600;
    padding: 0 25px 4px 25px;
    margin-top: 12px;
    margin-bottom: 2px;
    letter-spacing: 1.2px;
}
.sidebar .admin-menu li {
    margin: 0;
    position: relative;
}
.sidebar .admin-menu a {
    display: flex;
    align-items: center;
    color: #fff;
    padding: 10px 25px;
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    border-left: 4px solid transparent;
    transition: background 0.2s, border-left 0.2s;
    gap: 10px;
    position: relative;
}
.sidebar .admin-menu a:hover, .sidebar .admin-menu a.active {
    background: rgba(255, 255, 255, 0.08);
    border-left: 4px solid #1a2235;
    color: #283149;
}
.sidebar .admin-menu i {
    width: 22px;
    text-align: center;
    font-size: 1.1rem;
}
.sidebar .sidebar-footer {
    padding: 12px 25px;
    font-size: .92rem;
    color: #bfc5d2;
    border-top: 1px solid #1a2235;
    background: #232946;
    margin-top: auto;
}
.sidebar .sidebar-footer strong {
    color: #ff6600;
    font-weight: 500;
}

/* Notification badge */
.menu-badge {
    display: inline-block;
    min-width: 21px;
    padding: 2px 6px;
    font-size: 0.85em;
    font-weight: bold;
    color: #fff;
    background: #ff3b3b;
    border-radius: 12px;
    margin-left: 7px;
    text-align: center;
    vertical-align: middle;
    box-shadow: 0 1px 5px rgba(0,0,0,0.08);
    position: relative;
    top: -1px;
    animation: badgepop 0.5s;
}
@keyframes badgepop {
    0% { transform: scale(0.7);}
    70% { transform: scale(1.2);}
    100% { transform: scale(1);}
}

@media (max-width: 1024px) {
    .sidebar { width: 180px; }
}
@media (max-width: 768px) {
    .sidebar { width: 100%; height: auto; position: relative; }
}
</style>

<!-- Sidebar Content -->
<div class="sidebar">
    <div class="logo-area">
        <!-- <div class="logo-img">
            <img src="<?php echo $admin_profile_image; ?>" alt="Admin">
        </div> -->
        <div class="logo-text">
            Studio Admin
            <span>Creative Studio</span>
        </div>
    </div>
    <div class="admin-menu-section-title">CORE</div>
    <ul class="admin-menu">
        <li>
            <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li><br>

        <div class="admin-menu-section-title">INTERFACE</div>

        <li>
            <a href="manage_bookings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_bookings.php') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i> Bookings
                <?php if ($new_bookings_count > 0): ?>
                    <span class="menu-badge"><?php echo $new_bookings_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="manage_gallery.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_gallery.php') ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Gallery
            </a>
        </li>
        <li>
            <a href="messages.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'messages.php') ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Messages
                <?php if ($new_messages_count > 0): ?>
                    <span class="menu-badge"><?php echo $new_messages_count; ?></span>
                <?php endif; ?>
            </a>
        </li>

     <div class="admin-menu-section-title">TABLES</div>
        <li>
            <a href="notifications.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'active' : ''; ?>">
                <i class="fas fa-bell"></i> Notifications
            </a>
        </li>
        <li>
            <a href="settings_profile.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'settings_profile.php') ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> Profile
            </a>
        </li>
        <li>
            <a href="admin_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_users.php') ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i> Security
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        Logged in as: <strong><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></strong>
    </div>
</div>