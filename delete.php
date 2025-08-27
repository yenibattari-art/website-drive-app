<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    $file_id = intval($_POST['file_id']);

    $stmt = $mysqli->prepare("SELECT filepath, type FROM files WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($filepath, $type);

    if ($stmt->fetch()) {
        $stmt->close();

        if ($type === 'file' && file_exists($filepath)) {
            unlink($filepath);
        }

        $stmt = $mysqli->prepare("DELETE FROM files WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $file_id, $user_id);
        $stmt->execute();
    }
}

header("Location: dashboard.php");
exit;
