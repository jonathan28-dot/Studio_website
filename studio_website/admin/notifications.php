<?php
session_start();
require '../includes/config.php';

// // Only allow logged-in admins
// if (!isset($_SESSION['admin_id'])) {
//     header('Location: login.php');
//     exit;
// }

// $admin_id = $_SESSION['admin_id'];

// Mark notification as read
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND admin_id = ?");
    $stmt->bind_param("ii", $id, $admin_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Notification marked as read.";
    header("Location: notifications.php");
    exit;
}

// Delete notification
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND admin_id = ?");
    $stmt->bind_param("ii", $id, $admin_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Notification deleted.";
    header("Location: notifications.php");
    exit;
}

// Mark all as read
if (isset($_GET['mark_all'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE admin_id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "All notifications marked as read.";
    header("Location: notifications.php");
    exit;
}

// Get all notifications for this admin
$stmt = $conn->prepare("SELECT * FROM notifications WHERE admin_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Notifications";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications | Studio Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body { background: #f5f7fa; }
        .main-content { margin-left: 250px; padding: 36px 20px 20px 20px; min-height: 100vh; }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
        }
        .section-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #232949;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .notification-actions {
            display: flex;
            gap: 12px;
        }
        .notification-actions a {
            background: #e9ecef;
            color: #283149;
            border-radius: 6px;
            padding: 7px 16px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: background 0.2s;
        }
        .notification-actions a:hover { background: #ff6600; color: #fff; }
        .message {
            padding: 13px 18px;
            margin-bottom: 25px;
            border-radius: 7px;
            font-size: 1.04rem;
            font-weight: 500;
            background: #e4f8ea;
            color: #1c5c2c;
            border: 1px solid #42d47c;
        }
        .notifications-container {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 22px rgba(40,49,73,0.10);
            padding: 32px 32px 22px 32px;
            margin-top: 10px;
        }
        .notification-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notification-item {
            padding: 17px 0;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 20px;
        }
        .notification-item.unread {
            background: #fff7ef;
        }
        .notification-item:last-child { border-bottom: none; }
        .notification-content {
            flex: 1;
        }
        .notification-title {
            font-weight: 600;
            font-size: 1.09rem;
            color: #ff6600;
            margin-bottom: 2px;
        }
        .notification-message {
            color: #232949;
            font-size: 1.01rem;
            margin-bottom: 5px;
        }
        .notification-meta {
            font-size: 0.93rem;
            color: #7d8fa9;
        }
        .notification-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: .85rem;
            font-weight: 700;
            background: #f0f0f0;
            color: #283149;
            margin-right: 6px;
        }
        .notification-type.info { background: #e3eafe; color: #1556b2; }
        .notification-type.success { background: #e1f7e7; color: #1c5c2c; }
        .notification-type.warning { background: #fff5cc; color: #856404; }
        .notification-type.error { background: #ffe3e3; color: #b21b1b; }
        .notification-actions-inline {
            display: flex;
            gap: 7px;
            margin-top: 5px;
        }
        .notification-action-btn {
            border: none;
            background: #e9ecef;
            color: #232949;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: .95rem;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .notification-action-btn.read { background: #ff6600; color: #fff; }
        .notification-action-btn.delete { background: #dc3545; color: #fff; }
        .notification-action-btn:hover { opacity: 0.85; }
        @media (max-width: 900px) {
            .main-content { padding: 18px 5px; }
            .notifications-container { padding: 18px 7px; }
            .section-header h2 { font-size: 1.2rem; }
        }
        @media (max-width: 600px) {
            .main-content { margin-left: 0; }
            .section-header { flex-direction: column; gap: 7px;}
        }
    </style>
</head>
<body>
    <?php include 'sidenav.php'; ?>
    <div class="main-content">
        <div class="section-header">
            <h2><i class="fas fa-bell" style="color:#ff6600"></i> Notifications</h2>
            <div class="notification-actions">
                <a href="notifications.php?mark_all=1"><i class="fas fa-check-double"></i> Mark all as read</a>
            </div>
        </div>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <div class="notifications-container">
            <?php if (empty($notifications)): ?>
                <p style="color:#888;">No notifications found.</p>
            <?php else: ?>
                <ul class="notification-list">
                    <?php foreach ($notifications as $n): ?>
                    <li class="notification-item<?php echo !$n['is_read'] ? ' unread' : ''; ?>">
                        <div class="notification-content">
                            <div class="notification-title">
                                <?php echo htmlspecialchars($n['title']); ?>
                            </div>
                            <div class="notification-message">
                                <?php echo htmlspecialchars($n['message']); ?>
                            </div>
                            <div class="notification-meta">
                                <span class="notification-type <?php 
                                    echo $n['type'] == 'info' ? 'info' : (
                                         $n['type'] == 'success' ? 'success' : (
                                         $n['type'] == 'warning' ? 'warning' : (
                                         $n['type'] == 'error' ? 'error' : ''))); ?>">
                                    <?php echo ucfirst($n['type']); ?>
                                </span>
                                <?php echo date('M j, Y g:i a', strtotime($n['created_at'])); ?>
                            </div>
                            <div class="notification-actions-inline">
                                <?php if (!$n['is_read']): ?>
                                    <a href="notifications.php?mark_read=<?php echo $n['id']; ?>" class="notification-action-btn read">
                                        <i class="fas fa-check"></i> Mark as read
                                    </a>
                                <?php endif; ?>
                                <a href="notifications.php?delete=<?php echo $n['id']; ?>" class="notification-action-btn delete" onclick="return confirm('Are you sure you want to delete this notification?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>