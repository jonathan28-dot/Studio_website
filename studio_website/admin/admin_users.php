<?php
session_start();
require '../includes/config.php';
require 'sidenav.php'; // Loads your modern sidebar

// Helper function to handle profile image upload
function handle_profile_image_upload($input_name, $existing_file = null) {
    if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/admin_profiles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
        $new_name = uniqid('admin_', true) . '.' . $ext;
        $target = $upload_dir . $new_name;

        // Validate file type and size (max 2MB, jpg/png/jpeg/gif)
        $valid_ext = ['jpg','jpeg','png','gif'];
        if (!in_array($ext, $valid_ext)) return $existing_file; // skip if not valid
        if ($_FILES[$input_name]['size'] > 2*1024*1024) return $existing_file; // skip if too big

        // Move uploaded file
        if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $target)) {
            // Optionally delete old file
            if ($existing_file && file_exists($existing_file) && strpos($existing_file, 'default_profile.png') === false) {
                @unlink($existing_file);
            }
            return $target;
        }
    }
    return $existing_file;
}

// Handle Add User
$add_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $profile_image = handle_profile_image_upload('profile_image');

    if (!$username || !$email || !$password) {
        $add_message = '<span style="color:#d32f2f;">All fields are required.</span>';
    } else {
        $existing = $conn->prepare("SELECT id FROM admin_users WHERE username=? OR email=?");
        $existing->bind_param("ss", $username, $email);
        $existing->execute();
        $existing->store_result();
        if ($existing->num_rows > 0) {
            $add_message = '<span style="color:#d32f2f;">Username or email already exists.</span>';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password, profile_image, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $username, $email, $hashed, $profile_image);
            if ($stmt->execute()) {
                $add_message = '<span style="color:#388e3c;">New admin user added successfully.</span>';
            } else {
                $add_message = '<span style="color:#d32f2f;">Failed to add user.</span>';
            }
            $stmt->close();
        }
        $existing->close();
    }
}

// Handle Delete User
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if ($delete_id != ($_SESSION['admin_id'] ?? 0)) {
        // Delete image file if not default
        $imgq = $conn->query("SELECT profile_image FROM admin_users WHERE id=$delete_id");
        if ($imgq && $imgrow = $imgq->fetch_assoc()) {
            $img = $imgrow['profile_image'];
            if ($img && file_exists($img) && strpos($img, 'default_profile.png') === false) {
                @unlink($img);
            }
        }
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_users.php?deleted=1");
        exit;
    }
}

// Handle Edit User
$edit_message = '';
if (isset($_POST['edit_user'])) {
    $edit_id = (int)$_POST['edit_id'];
    $edit_username = trim($_POST['edit_username']);
    $edit_email = trim($_POST['edit_email']);
    $edit_password = $_POST['edit_password'];

    $old_img = '';
    $result_img = $conn->query("SELECT profile_image FROM admin_users WHERE id=$edit_id");
    if ($result_img && $row_img = $result_img->fetch_assoc()) $old_img = $row_img['profile_image'];

    $profile_image = handle_profile_image_upload('edit_profile_image', $old_img);

    if (!$edit_username || !$edit_email) {
        $edit_message = '<span style="color:#d32f2f;">Username and email required.</span>';
    } else {
        if ($edit_password) {
            $hashed = password_hash($edit_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_users SET username=?, email=?, password=?, profile_image=? WHERE id=?");
            $stmt->bind_param("ssssi", $edit_username, $edit_email, $hashed, $profile_image, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE admin_users SET username=?, email=?, profile_image=? WHERE id=?");
            $stmt->bind_param("sssi", $edit_username, $edit_email, $profile_image, $edit_id);
        }
        if ($stmt->execute()) {
            $edit_message = '<span style="color:#388e3c;">User updated.</span>';
        } else {
            $edit_message = '<span style="color:#d32f2f;">Update failed.</span>';
        }
        $stmt->close();
    }
}

// Fetch all admin users after any action
$users = $conn->query("SELECT * FROM admin_users ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Users | Studio Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            margin-left: 250px;
            padding: 36px 20px 20px 20px;
            min-height: 100vh;
            background: #f5f7fa;
        }
        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .users-header h3 {
            margin-bottom: 0;
        }
        .btn {
            display: inline-block;
            padding: 7px 17px;
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 2px;
            transition: background 0.18s;
        }
        .btn-primary {
            background: #ff6600;
            color: #fff;
        }
        .btn-primary:hover { background: #e65a00; }
        .btn-warning {
            background: #ffb300;
            color: #fff;
        }
        .btn-warning:hover { background: #ff9800; }
        .btn-danger {
            background: #e53935;
            color: #fff;
        }
        .btn-danger:hover { background: #b71c1c; }
        .btn-close {
            background: #b0b6be;
            color: #fff;
            padding: 3px 8px;
            font-size: 1.1em;
            float: right;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(40,49,73,0.08);
            margin-top: 26px;
        }
        .table th, .table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e6ed;
            text-align: left;
            font-size: 1.04rem;
        }
        .table thead {
            background: #f3f8fb;
        }
        .table th {
            font-weight: bold;
            color: #283149;
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        .user-img {
            object-fit: cover;
            border-radius: 50%;
            border:1px solid #ddd;
        }
        .text-muted {
            color: #b0b6be !important;
            font-size: 1.15em;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 10010;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: rgba(40,49,73,0.27);
            align-items: center;
            justify-content: center;
        }
        .modal.show { display: flex; }
        .modal-content {
            background: #fff;
            border-radius: 12px;
            padding: 24px 28px 14px 28px;
            min-width: 320px;
            max-width: 95vw;
            box-shadow: 0 4px 18px rgba(40,49,73,0.18);
            position: relative;
            animation: modalPop .25s;
        }
        @keyframes modalPop {
            0% { transform: scale(.75); opacity:0; }
            100% { transform: scale(1); opacity:1; }
        }
        .modal-content label {
            font-weight: 600;
            margin-top: 12px;
            margin-bottom: 4px;
            display: block;
            color: #283149;
        }
        .modal-content input[type="text"], .modal-content input[type="email"], .modal-content input[type="password"], .modal-content input[type="file"] {
            width: 100%;
            padding: 10px;
            border-radius: 7px;
            border: 1px solid #cfd8dc;
            background: #f3f8fb;
            font-size: 1.05rem;
            margin-bottom: 10px;
        }
        .modal-content input[type="text"]:focus, .modal-content input[type="email"]:focus, .modal-content input[type="password"]:focus {
            border-color: #ff6600;
            outline: none;
        }
        .modal-content .modal-actions {
            margin-top: 18px;
            text-align: right;
        }
        .modal-content .message {
            margin-bottom: 10px;
            background: #f3f8fb;
            padding: 8px 10px;
            border-radius: 6px;
        }
        .modal-content .btn-close {
            position: absolute;
            right: 12px;
            top: 12px;
            padding: 1px 10px;
        }
        .edit-img-preview, .add-img-preview {
            display: block;
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 8px;
            border: 1px solid #ccc;
        }
        @media (max-width: 900px) {
            .main-content { margin-left: 0; padding: 18px 5px; }
            .table th, .table td { font-size: 0.98rem; }
        }
        @media (max-width: 600px) {
            .main-content { margin-left: 0; padding: 8px 1px; }
            .table th, .table td { padding: 7px 2px; }
            .modal-content { padding: 10px 5px; min-width: 0; }
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="users-header">
        <h3><i class="fa fa-users"></i> Admin Users</h3>
        <button class="btn btn-primary" onclick="showAddModal()"><i class="fa fa-plus"></i> Add Admin</button>
    </div>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="message" style="color:#388e3c;background:#eafaf1;border-radius:7px;padding:8px 15px;margin:17px 0;">
            User deleted successfully.
        </div>
    <?php endif; ?>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Profile Image</th>
                <th>Created At</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <?php
                    $profile_image_src = !empty($row['profile_image']) ? htmlspecialchars($row['profile_image']) : 'assets/images/default_profile.png';
                    ?>
                    <img src="<?= $profile_image_src ?>" alt="Profile" width="40" height="40" class="user-img">
                </td>
                <td><?= $row['created_at'] ?></td>
                <td><?= $row['last_login'] ?? '-' ?></td>
                <td>
                    <button class="btn btn-warning" title="Edit" onclick="showEditModal(
                        <?= $row['id'] ?>,
                        '<?= htmlspecialchars(addslashes($row['username'])) ?>',
                        '<?= htmlspecialchars(addslashes($row['email'])) ?>',
                        '<?= htmlspecialchars(addslashes($profile_image_src)) ?>'
                    )"><i class="fa fa-edit"></i></button>
                    <?php if ($row['id'] != ($_SESSION['admin_id'] ?? 0)): ?>
                        <a href="admin_users.php?delete=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete user?')" title="Delete"><i class="fa fa-trash"></i></a>
                    <?php else: ?>
                        <span class="text-muted" title="You cannot delete your own account"><i class="fa fa-lock"></i></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <button class="btn btn-close" onclick="closeAddModal()">&times;</button>
        <h4 style="margin-top:0"><i class="fa fa-user-plus" style="color:#ff6600"></i> Add Admin User</h4>
        <?php if ($add_message): ?>
            <div class="message"><?= $add_message ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <label for="add-username">Username</label>
            <input type="text" name="username" id="add-username" required>
            <label for="add-email">Email</label>
            <input type="email" name="email" id="add-email" required>
            <label for="add-password">Password</label>
            <input type="password" name="password" id="add-password" required minlength="6">
            <label for="add-profile-image">Profile Image</label>
            <img src="assets/images/default_profile.png" alt="Preview" class="add-img-preview" id="add-img-preview">
            <input type="file" name="profile_image" id="add-profile-image" accept="image/*" onchange="previewAddImage(this)">
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary" name="add_user"><i class="fa fa-plus"></i> Add</button>
                <button type="button" class="btn btn-close" onclick="closeAddModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <button class="btn btn-close" onclick="closeEditModal()">&times;</button>
        <h4 style="margin-top:0"><i class="fa fa-user-edit" style="color:#ff6600"></i> Edit Admin User</h4>
        <?php if ($edit_message): ?>
            <div class="message"><?= $edit_message ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off" id="editForm">
            <input type="hidden" name="edit_id" id="edit-id">
            <label for="edit-username">Username</label>
            <input type="text" name="edit_username" id="edit-username" required>
            <label for="edit-email">Email</label>
            <input type="email" name="edit_email" id="edit-email" required>
            <label for="edit-password">New Password <small style="color:#b0b6be">(leave blank to keep current)</small></label>
            <input type="password" name="edit_password" id="edit-password" minlength="6">
            <label for="edit-profile-image">Profile Image</label>
            <img src="assets/images/default_profile.png" alt="Preview" class="edit-img-preview" id="edit-img-preview">
            <input type="file" name="edit_profile_image" id="edit-profile-image" accept="image/*" onchange="previewEditImage(this)">
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary" name="edit_user"><i class="fa fa-save"></i> Save</button>
                <button type="button" class="btn btn-close" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('addModal').classList.add('show');
    setTimeout(function() {
        document.getElementById('add-username').focus();
    }, 100);
}
function closeAddModal() {
    document.getElementById('addModal').classList.remove('show');
    document.getElementById('add-img-preview').src = "assets/images/default_profile.png";
    document.getElementById('add-profile-image').value = '';
}

function showEditModal(id, username, email, img) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-username').value = username;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-password').value = '';
    document.getElementById('edit-img-preview').src = img || "assets/images/default_profile.png";
    document.getElementById('edit-profile-image').value = "";
    document.getElementById('editModal').classList.add('show');
    setTimeout(function() {
        document.getElementById('edit-username').focus();
    }, 100);
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    document.getElementById('edit-img-preview').src = "assets/images/default_profile.png";
    document.getElementById('edit-profile-image').value = '';
}

// Preview functions for images
function previewAddImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('add-img-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
function previewEditImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('edit-img-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Close modal by clicking outside
document.addEventListener('mousedown', function(e) {
    ['addModal', 'editModal'].forEach(function(modalId) {
        var modal = document.getElementById(modalId);
        if (modal.classList.contains('show')) {
            var content = modal.querySelector('.modal-content');
            if (!content.contains(e.target) && e.target !== content) {
                modal.classList.remove('show');
            }
        }
    });
});
</script>
</body>
</html>