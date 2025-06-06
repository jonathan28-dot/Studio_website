<?php
include '../includes/config.php';
// requireAdminLogin();

// Mark message as read
if (isset($_GET['mark_read'])) {
    $id = $_GET['mark_read'];
    $conn->query("UPDATE contacts SET is_read = 1 WHERE id = $id");
    $_SESSION['message'] = "Message marked as read";
    header("Location: messages.php");
    exit;
}

// Delete message
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM contacts WHERE id = $id");
    $_SESSION['message'] = "Message deleted";
    header("Location: messages.php");
    exit;
}

// Get all messages
$messages = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$page_title = "Manage Messages";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages | Studio Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body { background: #f5f7fa; }
        .main-content { margin-left: 280px; padding: 36px 20px 20px 20px; min-height: 100vh; }
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
        .message {
            padding: 13px 18px;
            margin-bottom: 25px;
            border-radius: 7px;
            font-size: 1.04rem;
            font-weight: 500;
        }
        .message.success {
            background: #e4f8ea;
            color: #1c5c2c;
            border: 1px solid #42d47c;
        }
        .messages-container {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 22px rgba(40,49,73,0.10);
            padding: 32px 32px 22px 32px;
            margin-top: 10px;
        }
        .message-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .message-item {
            padding: 18px 0 18px 0;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        .message-item.unread {
            background: #f8f5ff;
        }
        .message-item:last-child { border-bottom: none; }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .message-name {
            font-weight: 600;
            font-size: 1.07rem;
            color: #232949;
        }
        .message-email {
            color: #ff6600;
            text-decoration: none;
            font-size: .97rem;
        }
        .message-date {
            font-size: 0.88rem;
            color: #7d8fa9;
            margin-left: 10px;
            white-space: nowrap;
        }
        .message-subject {
            font-weight: 500;
            margin: 8px 0;
            color: #283149;
            font-size: 1.08rem;
        }
        .message-body {
            color: #444;
            font-size: 1rem;
            margin-bottom: 13px;
        }
        .message-actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            padding: 6px 15px;
            border-radius: 4px;
            font-size: 0.93rem;
            cursor: pointer;
            border: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: background 0.18s, color 0.18s;
        }
        .mark-read {
            background: #f5f0ff;
            color: #6a11cb;
        }
        .mark-read:hover { background: #e8e0ff; }
        .delete {
            background: #ffe3e6;
            color: #dc3545;
        }
        .delete:hover { background: #ffccd3; }
        .unread-badge {
            display: inline-block;
            padding: 2px 9px;
            background: #ff6600;
            color: white;
            border-radius: 20px;
            font-size: 0.76rem;
            font-weight: 600;
            margin-left: 9px;
            letter-spacing: 0.03em;
        }
        @media (max-width: 900px) {
            .main-content { padding: 18px 5px; }
            .messages-container { padding: 18px 7px; }
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
            <h2><i class="fas fa-envelope" style="color:#ff6600"></i> Manage Messages</h2>
        </div>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <div class="messages-container">
            <?php if (empty($messages)): ?>
                <p>No messages found.</p>
            <?php else: ?>
                <ul class="message-list">
                    <?php foreach ($messages as $message): ?>
                    <li class="message-item<?php echo !$message['is_read'] ? ' unread' : ''; ?>">
                        <div class="message-header">
                            <span>
                                <span class="message-name"><?php echo htmlspecialchars($message['name']); ?></span>
                                <?php if (!$message['is_read']): ?>
                                    <span class="unread-badge">New</span>
                                <?php endif; ?>
                            </span>
                            <span class="message-date"><?php echo date('M j, Y g:i a', strtotime($message['created_at'])); ?></span>
                        </div>
                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="message-email">
                            <?php echo htmlspecialchars($message['email']); ?>
                        </a>
                        <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                        <div class="message-body"><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                        <div class="message-actions">
                            <?php if (!$message['is_read']): ?>
                                <a href="messages.php?mark_read=<?php echo $message['id']; ?>" class="action-btn mark-read">
                                    <i class="fas fa-check"></i> Mark Read
                                </a>
                            <?php endif; ?>
                            <a href="messages.php?delete=<?php echo $message['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this message?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>