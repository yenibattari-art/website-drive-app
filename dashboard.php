<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Mini Drive</title>
    <style>
        body { margin:0; font-family:Arial,sans-serif; background:#f7f8fa; }
        .sidebar { width:220px; height:100vh; background:#2c3e50; color:white; position:fixed; top:0; left:0; padding:20px; }
        .sidebar h2 { margin:0 0 20px; }
        .sidebar a { display:block; padding:10px; color:white; text-decoration:none; margin-bottom:10px; border-radius:6px; }
        .sidebar a:hover { background:#34495e; }
        .content { margin-left:240px; padding:20px; }
        .header { display:flex; justify-content:space-between; align-items:center; }
        .btn { background:#3498db; color:white; padding:8px 12px; border:none; border-radius:6px; cursor:pointer; text-decoration:none; margin:2px; }
        .btn:hover { background:#2980b9; }
        .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:15px; margin-top:20px; }
        .card { background:white; border-radius:10px; padding:15px; box-shadow:0 2px 6px rgba(0,0,0,0.1); text-align:center; }
        .card .icon { font-size:40px; margin-bottom:10px; }
        .card h3 { margin:0; font-size:16px; }
        .card small { color:#666; }
        .highlight { background:yellow; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>📂 Mini Drive</h2>
    <a href="dashboard.php">📁 Semua File</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="content">
    <div class="header">
        <h1>Dashboard</h1>
        <div style="display:flex; gap:10px; align-items:center;">
            <form method="get" action="dashboard.php" style="display:flex; gap:5px;">
                <input type="hidden" name="pid" value="<?php echo $pid; ?>">
                <input type="text" name="search" placeholder="🔍 Cari file atau folder" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       style="padding:6px; border-radius:6px; border:1px solid #ccc; width:200px;">
                <button class="btn" type="submit">Cari</button>
            </form>
            <button class="btn" onclick="showPopup('upload')">⬆ Upload</button>
            <button class="btn" onclick="showPopup('folder')">📁 Folder Baru</button>
        </div>
    </div>

    <div class="grid">
    <?php
    if ($search !== "") {
        $like = "%" . $search . "%";
        $stmt = $mysqli->prepare("SELECT id, filename, size, created, filepath, type 
                                  FROM files 
                                  WHERE user_id=? AND parent_id=? AND filename LIKE ? 
                                  ORDER BY type DESC, created DESC");
        $stmt->bind_param("iis", $user_id, $pid, $like);
    } else {
        $stmt = $mysqli->prepare("SELECT id, filename, size, created, filepath, type 
                                  FROM files 
                                  WHERE user_id=? AND parent_id=? 
                                  ORDER BY type DESC, created DESC");
        $stmt->bind_param("ii", $user_id, $pid);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0): ?>
        <p style="grid-column:1 / -1; text-align:center; color:#888;">❌ Tidak ada file/folder ditemukan</p>
    <?php endif;

    function highlight($text, $word) {
        if ($word === "") return htmlspecialchars($text);
        return preg_replace("/(" . preg_quote($word, '/') . ")/i", '<span class="highlight">$1</span>', htmlspecialchars($text));
    }

    while ($row = $result->fetch_assoc()): ?>
        <div class="card">
            <?php if ($row['type'] === 'folder'): ?>
                <div class="icon">📁</div>
                <h3><a href="dashboard.php?pid=<?php echo $row['id']; ?>">
                    <?php echo highlight($row['filename'], $search); ?>
                </a></h3>
                <small>Folder</small>
            <?php else: ?>
                <div class="icon">📄</div>
                <h3><?php echo highlight($row['filename'], $search); ?></h3>
                <small><?php echo round($row['size']/1024,2)." KB"; ?></small><br>
                <a class="btn" href="<?php echo $row['filepath']; ?>" download>⬇ Download</a>
            <?php endif; ?>
            <form method="post" action="delete.php" onsubmit="return confirm('Yakin hapus file/folder ini?');">
                <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                <button class="btn" type="submit">🗑 Hapus</button>
            </form>
        </div>
    <?php endwhile; ?>
    </div>
</div>

<!-- Popup sama seperti versi 1 -->
<div id="popup" class="popup hidden">
    <div class="popup-content">
        <h3 id="popup-title"></h3>
        <form id="upload-form" method="post" enctype="multipart/form-data" action="upload.php" style="display:none;">
            <input type="file" name="file" required>
            <input type="hidden" name="parent_id" value="<?php echo $pid; ?>">
            <button class="btn" type="submit">Upload</button>
            <button type="button" class="btn" onclick="closePopup()">Tutup</button>
        </form>
        <form id="folder-form" method="post" action="newfolder.php" style="display:none;">
            <input type="text" name="folder_name" placeholder="Nama Folder" required>
            <input type="hidden" name="parent_id" value="<?php echo $pid; ?>">
            <button class="btn" type="submit">Buat Folder</button>
            <button type="button" class="btn" onclick="closePopup()">Tutup</button>
        </form>
    </div>
</div>

<style>
    .popup { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6);
             display:flex; align-items:center; justify-content:center; opacity:0; visibility:hidden;
             transition:opacity 0.3s ease, visibility 0.3s ease; }
    .popup.show { opacity:1; visibility:visible; }
    .popup-content { background:white; padding:20px; border-radius:12px; width:300px; text-align:center;
                     transform:scale(0.8); opacity:0; transition:all 0.3s ease; }
    .popup.show .popup-content { transform:scale(1); opacity:1; }
</style>

<script>
    function showPopup(type) {
        let popup = document.getElementById('popup');
        popup.classList.add('show');
        if (type === 'upload') {
            document.getElementById('popup-title').innerText = "Upload File";
            document.getElementById('upload-form').style.display='block';
            document.getElementById('folder-form').style.display='none';
        } else {
            document.getElementById('popup-title').innerText = "Folder Baru";
            document.getElementById('upload-form').style.display='none';
            document.getElementById('folder-form').style.display='block';
        }
    }
    function closePopup() {
        document.getElementById('popup').classList.remove('show');
    }
    window.addEventListener('click', function(e) {
        let popup = document.getElementById('popup');
        if (e.target === popup) { closePopup(); }
    });
</script>
</body>
</html>
