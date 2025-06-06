<?php
session_start();
include '../includes/config.php';

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Booking status updated successfully!";
    header("Location: manage_bookings.php");
    exit;
}

// Handle viewing single booking
$booking = null;
if (isset($_GET['view'])) {
    $booking_id = $_GET['view'];
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
}

// Get all bookings
$bookings = $conn->query("SELECT * FROM bookings ORDER BY date DESC")->fetch_all(MYSQLI_ASSOC);
$page_title = "Manage Bookings";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings | Creative Studio Space</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome for icons -->
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
        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 22px rgba(40,49,73,0.10);
            padding: 32px 32px 22px 32px;
            margin-bottom: 34px;
        }
        .card .back-btn {
            display: inline-block;
            margin-bottom: 14px;
            padding: 7px 16px;
            background: #232949;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .card .back-btn:hover { background: #ff6600; }
        .card-title {
            font-size: 1.2rem;
            color: #ff6600;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 1px solid #f0eded;
            padding-bottom: 8px;
        }
        .booking-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
            margin-bottom: 28px;
        }
        .detail-label { color: #888; font-size: .98rem; }
        .detail-value { color: #232949; font-size: 1.08rem; font-weight: 500; margin-bottom: 10px; }
        .status-chip {
            display: inline-block;
            padding: 5px 13px;
            border-radius: 16px;
            font-size: .92rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-completed { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .special-req {
            margin-top: 8px;
            background: #f8f8f8;
            border-left: 4px solid #ff6600;
            padding: 13px 15px;
            border-radius: 4px;
            color: #555;
            font-size: 1.01rem;
        }
        .status-form { margin-top: 17px; }
        .status-form label { font-weight: 500; margin-right: 10px; }
        .status-form select {
            padding: 7px 14px;
            border-radius: 4px;
            border: 1px solid #eee;
            margin-right: 10px;
        }
        .status-form button {
            padding: 7px 20px;
            background: #ff6600;
            color: #fff;
            border-radius: 4px;
            border: none;
            font-weight: bold;
            letter-spacing: 0.04em;
            cursor: pointer;
        }
        /* Table styles */
        .table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 22px rgba(40,49,73,0.10);
            padding: 32px 24px 22px 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1.01rem;
        }
        th, td {
            padding: 13px 10px;
            text-align: left;
        }
        th {
            background: #f6f6f8;
            color: #232949;
            font-weight: 600;
            border-bottom: 2px solid #e8e8ed;
        }
        tr:not(:last-child) td { border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f7f7fd; }
        .table-actions {
            display: flex;
            gap: 7px;
        }
        .action-btn {
            padding: 6px 15px;
            border-radius: 4px;
            font-size: .93rem;
            border: none;
            transition: background 0.15s;
            cursor: pointer;
            outline: none;
            color: #fff;
            background: #232949;
            text-decoration: none;
            font-weight: 500;
        }
        .action-btn.view-btn { background: #ff6600; }
        .action-btn.edit-btn { background: #ffc107; color: #232949; }
        .action-btn.delete-btn { background: #dc3545; }
        .action-btn:hover { opacity: 0.85; }
        @media (max-width: 900px) {
            .main-content { padding: 18px 5px; }
            .card, .table-card { padding: 18px 7px; }
            .section-header h2 { font-size: 1.2rem; }
        }
        @media (max-width: 600px) {
            .main-content { margin-left: 0; }
            .section-header { flex-direction: column; gap: 7px;}
            .booking-details-grid { grid-template-columns: 1fr; gap: 10px;}
        }
    </style>
</head>
<body>
    <?php include 'sidenav.php'; ?>
    <div class="main-content">
        <div class="section-header">
            <h2><i class="fas fa-calendar-check" style="color:#ff6600"></i> Manage Bookings</h2>
        </div>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if ($booking): ?>
            <!-- Single Booking View -->
            <div class="card">
                <a href="manage_bookings.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to All Bookings</a>
                <div class="card-title">Booking Details</div>
                <div class="booking-details-grid">
                    <div>
                        <div class="detail-label">Client Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['name']); ?></div>
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                    </div>
                    <div>
                        <div class="detail-label">Service</div>
                        <div class="detail-value"><?php echo htmlspecialchars($booking['service']); ?></div>
                        <div class="detail-label">Booking Date</div>
                        <div class="detail-value"><?php echo date('F j, Y', strtotime($booking['date'])); ?></div>
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-chip status-<?php echo strtolower($booking['status']); ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="detail-label">Special Requests</div>
                <div class="special-req">
                    <?php echo !empty($booking['message']) ? htmlspecialchars($booking['message']) : 'None'; ?>
                </div>
                <form class="status-form" method="POST">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <label for="status">Update Booking Status:</label>
                    <select name="status" id="status">
                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status">Update</button>
                </form>
            </div>
        <?php else: ?>
            <!-- All Bookings List -->
            <div class="table-card">
                <div class="card-title">All Bookings</div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['service']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($booking['date'])); ?></td>
                            <td>
                                <span class="status-chip status-<?php echo strtolower($booking['status']); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td class="table-actions">
                                <a href="manage_bookings.php?view=<?php echo $booking['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i> View</a>
                                <a href="#" class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</a>
                                <a href="#" class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>