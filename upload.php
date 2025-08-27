<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;

$upload_dir = "uploads/" . $user_id . "/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $filename = basename($_FILES['file']['name']);
    $filepath = $upload_dir . time() . "_" . $filename;
    $filesize = $_FILES['file']['size'];

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
        $stmt = $mysqli->prepare("INSERT INTO files (user_id, filename, filepath, size, type, parent_id) VALUES (?, ?, ?, ?, 'file', ?)");
        $stmt->bind_param("issii", $user_id, $filename, $filepath, $filesize, $parent_id);
        $stmt->execute();
    }
}

header("Location: dashboard.php?pid=" . $parent_id);
exit;
