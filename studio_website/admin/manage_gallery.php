<?php
session_start();

include '../includes/config.php';

// Upload logic (same as before) ...

// Delete logic (same as before) ...

// Get all gallery items
$gallery_items = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Set page title for header
$page_title = "Gallery";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery | Creative Studio Space</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body { background: #f6f8fa; }
        .main-content { margin-left: 280px; padding: 30px 20px 20px 20px; }
        .gallery-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .gallery-page-header h2 {
            font-size: 2.1rem;
            font-weight: 700;
            color: #283149;
            margin: 0;
        }
        .gallery-search-bar {
            background: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(40,49,73,0.08);
            padding: 6px 16px;
            gap: 8px;
            border: 1px solid #ececec;
        }
        .gallery-search-bar input {
            border: none;
            outline: none;
            background: none;
            font-size: 1.04rem;
            width: 150px;
        }
        .gallery-search-bar i { color: #ff6600; }
        .gallery-container {
            margin-top: 12px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }
        .gallery-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 16px 0 rgba(40,49,73,0.09);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.22s, box-shadow 0.22s;
            position: relative;
        }
        .gallery-card:hover {
            transform: translateY(-7px) scale(1.017);
            box-shadow: 0 10px 36px 0 rgba(40,49,73,0.16);
        }
        .gallery-card img {
            width: 100%;
            height: 170px;
            object-fit: cover;
            background: #e8eaed;
        }
        .gallery-card-body {
            padding: 16px 15px 11px 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .gallery-card-body h3 {
            font-size: 1.09rem;
            font-weight: 700;
            color: #283149;
            margin-bottom: 4px;
        }
        .gallery-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: .89rem;
            color: #888;
            margin-bottom: 8px;
        }
        .gallery-meta .cat-badge {
            background: #ff6600;
            color: #fff;
            padding: 2px 10px;
            border-radius: 13px;
            font-size: .75rem;
            font-weight: 600;
        }
        .gallery-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }
        .gallery-actions a, .gallery-actions button {
            border: none;
            background: none;
            color: #ff6600;
            font-weight: 600;
            font-size: .96rem;
            border-radius: 4px;
            padding: 5px 11px;
            transition: background 0.16s;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .gallery-actions a:hover, .gallery-actions button:hover {
            background: #fdf3eb;
        }
        .gallery-actions .delete-btn {
            color: #fff;
            background: #ff6282;
        }
        .gallery-actions .delete-btn:hover {
            background: #d73356;
        }
        .fab-upload {
            position: fixed;
            right: 38px;
            bottom: 38px;
            background: #ff6600;
            color: #fff;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 22px rgba(40,49,73,.14);
            cursor: pointer;
            z-index: 999;
            transition: background 0.19s;
        }
        .fab-upload:hover { background: #e25500; }
        .modal-bg {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(40,49,73,0.22);
            align-items: center;
            justify-content: center;
            z-index: 1001;
        }
        .modal-bg.active { display: flex; }
        .modal-upload-form {
            background: #fff;
            border-radius: 10px;
            padding: 30px 24px 22px 24px;
            width: 97%;
            max-width: 390px;
            box-shadow: 0 8px 40px rgba(40,49,73,0.17);
            position: relative;
        }
        .modal-upload-form h3 {
            font-size: 1.18rem;
            margin-bottom: 14px;
            color: #ff6600;
            font-weight: 700;
        }
        .modal-close {
            position: absolute;
            top: 13px; right: 16px;
            font-size: 1.3rem;
            color: #888;
            background: none;
            border: none;
            cursor: pointer;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 7px; font-weight: 600; color: #404040; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1.01rem;
            background: #f8f8f8;
        }
        .upload-btn-main {
            padding: 10px 24px;
            background: #ff6600;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 8px;
        }
        .upload-btn-main:hover { background: #e25500; }
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
        .message.error {
            background: #fff2f4;
            color: #a82332;
            border: 1px solid #ff6282;
        }
        @media (max-width: 1000px) {
            .main-content { padding: 24px 2vw; }
            .gallery-page-header { flex-direction: column; align-items: flex-start; gap: 18px; }
            .gallery-container { padding: 10px 2vw; }
        }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .gallery-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'sidenav.php'; ?>
    <div class="admin-container">
        
        <div class="main-content">
            <!-- Header and search/filter -->
            <div class="gallery-page-header">
                <h2><i class="fas fa-images" style="color:#ff6600"></i> Gallery</h2>
                <form class="gallery-search-bar" method="get" action="#" id="gallerySearchForm">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="gallerySearchInput" placeholder="Search images..." autocomplete="off">
                </form>
            </div>

            <div class="gallery-container">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="gallery-grid" id="galleryGrid">
                    <?php if (empty($gallery_items)): ?>
                        <p style="color:#888;">No gallery items found. Upload some images to get started.</p>
                    <?php else: ?>
                        <?php foreach ($gallery_items as $item): ?>
                        <div class="gallery-card" data-title="<?php echo strtolower(htmlspecialchars($item['title'])); ?>" data-category="<?php echo strtolower($item['category']); ?>">
                            <img src="../uploads/gallery/<?php echo $item['image_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="gallery-card-body">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="gallery-meta">
                                    <span class="cat-badge"><?php echo ucfirst($item['category']); ?></span>
                                    <span><i class="far fa-clock"></i> <?php echo date('M j, Y', strtotime($item['created_at'])); ?></span>
                                </div>
                                <div class="gallery-actions">
                                    <a href="../uploads/gallery/<?php echo $item['image_path']; ?>" target="_blank"><i class="fas fa-eye"></i> View</a>
                                    <a href="manage_gallery.php?delete=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this image?');"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Floating Action Button for Upload -->
    <div class="fab-upload" onclick="document.getElementById('uploadModalBg').classList.add('active')" title="Add Image">
        <i class="fas fa-plus"></i>
    </div>
    <!-- Modal Upload Form -->
    <div class="modal-bg" id="uploadModalBg">
        <div class="modal-upload-form">
            <button class="modal-close" onclick="document.getElementById('uploadModalBg').classList.remove('active')">
                <i class="fas fa-times"></i>
            </button>
            <h3><i class="fas fa-upload"></i> Upload New Image</h3>
            <form action="manage_gallery.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Image Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="photography">Photography</option>
                        <option value="recording">Recording</option>
                        <option value="video">Video</option>
                        <option value="event">Event</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gallery_image">Image File</label>
                    <input type="file" id="gallery_image" name="gallery_image" accept="image/*" required>
                </div>
                <button type="submit" class="upload-btn-main">Upload Image</button>
            </form>
        </div>
    </div>
    <script>
        // Close modal on ESC
        window.addEventListener('keyup', function(e){
            if(e.key === "Escape") document.getElementById('uploadModalBg').classList.remove('active');
        });
        // Optional: Clicking outside modal closes it
        document.getElementById('uploadModalBg').addEventListener('click', function(e){
            if(e.target === this) this.classList.remove('active');
        });

        // Simple client-side live search/filter for gallery
        document.getElementById('gallerySearchInput').addEventListener('input', function() {
            const value = this.value.trim().toLowerCase();
            document.querySelectorAll('.gallery-card').forEach(card => {
                const title = card.getAttribute('data-title');
                const category = card.getAttribute('data-category');
                if(title.includes(value) || category.includes(value)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        // Prevent search form submission
        document.getElementById('gallerySearchForm').addEventListener('submit', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>