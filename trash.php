<?php
session_start();
require_once "config.php";
if (!isset($_SESSION['user_id'])) exit;

$file_id = intval($_POST['file_id']);
$user_id = $_SESSION['user_id'];

$stmt = $mysqli->prepare("UPDATE files SET is_deleted=1 WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();

header("Location: dashboard.php");
exit;
