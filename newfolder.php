<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
$folder_name = trim($_POST['folder_name']);

if ($folder_name !== "") {
    $stmt = $mysqli->prepare("INSERT INTO files (user_id, filename, type, parent_id, size, filepath) VALUES (?, ?, 'folder', ?, 0, '')");
    $stmt->bind_param("isi", $user_id, $folder_name, $parent_id);
    $stmt->execute();
}

header("Location: dashboard.php?pid=" . $parent_id);
exit;
