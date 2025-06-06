<?php
session_start();

// // Check if admin is logged in
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit;
// }

include '../includes/config.php';

// Get stats for dashboard
$bookings_count = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetch_row()[0];
$gallery_items = $conn->query("SELECT COUNT(*) FROM gallery")->fetch_row()[0];
$recent_messages = $conn->query("SELECT COUNT(*) FROM contacts")->fetch_row()[0];
$recent_bookings = $conn->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Set page title for header
$page_title = "Dashboard Overview";

// Fetch admin username and profile image for welcoming
$admin_username = "Admin";
$admin_profile_image = "admin/uploads/admin_profiles.png";
if (isset($_SESSION['admin_id'])) {
    $stmt = $conn->prepare("SELECT username, profile_image FROM admin_users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $stmt->bind_result($db_username, $db_profile_image);
    if ($stmt->fetch()) {
        $admin_username = $db_username;
        if (!empty($db_profile_image)) {
            if (preg_match('#^https?://#', $db_profile_image)) {
                $admin_profile_image = $db_profile_image;
            } else {
                $try1 = '../' . ltrim($db_profile_image, '/');
                $try2 = $db_profile_image;
                if (file_exists($try1)) {
                    $admin_profile_image = $try1;
                } elseif (file_exists($try2)) {
                    $admin_profile_image = $try2;
                }
            }
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Creative Studio Space</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        /* Welcoming Styles */
        .welcome-box {
            display: flex;
            align-items: center;
            gap: 18px;
            background: #fff3e0;
            padding: 18px 24px;
            border-radius: 12px;
            margin-bottom: 28px;
            box-shadow: 0 2px 8px rgba(255,102,0,0.07);
            font-size: 1.15rem;
            animation: fadeIn 0.8s;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(-15px);}
            100% { opacity: 1; transform: none;}
        }
        .welcome-img {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #ff6600;
            background: #fff;
            flex-shrink: 0;
        }
        .welcome-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .welcome-message strong {
            color: #ff6600;
        }
        .welcome-message {
            color: #222;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 1rem;
            color: #666;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 2rem;
            font-weight: 700;
            color: #ff6600;
        }

        .stat-card i {
            font-size: 2.5rem;
            color: #2575fc;
            float: right;
            opacity: 0.3;
        }

        /* Recent Bookings */
        .recent-bookings {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .recent-bookings h2 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background: #f9f9f9;
            font-weight: 600;
            color: #555;
        }

        table tr:hover {
            background: #f5f7fa;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .view-btn {
            padding: 5px 10px;
            background: #ff6600;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .view-btn:hover {
            background: #2575fc;
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .main-content {
                margin-left: 0;
            }
            .welcome-box {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Include Sidebar and Header -->
        <?php include 'sidenav.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcoming Box -->
            <div class="welcome-box">
                <div class="welcome-img">
                    <img src="<?php echo htmlspecialchars($admin_profile_image); ?>" alt="Profile picture">
                </div>
                <div class="welcome-message">
                    Welcome back, <strong><?php echo htmlspecialchars($admin_username); ?></strong>! Hope you have a productive day managing your <span style="color:#ff6600;">Creative Studio</span>. 🚀
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <p><?php echo $bookings_count; ?></p>
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div class="stat-card">
                    <h3>Pending Bookings</h3>
                    <p><?php echo $pending_bookings; ?></p>
                    <i class="fas fa-clock"></i>
                </div>

                <div class="stat-card">
                    <h3>Gallery Items</h3>
                    <p><?php echo $gallery_items; ?></p>
                    <i class="fas fa-images"></i>
                </div>

                <div class="stat-card">
                    <h3>Messages</h3>
                    <p><?php echo $recent_messages; ?></p>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="recent-bookings">
                <h2>Recent Bookings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['service']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($booking['date'])); ?></td>
                            <td>
                                <span class="status status-<?php echo strtolower($booking['status']); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="manage_bookings.php?view=<?php echo $booking['id']; ?>" class="view-btn">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>