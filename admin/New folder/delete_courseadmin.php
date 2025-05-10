<?php
include 'auth.php';
checkRole('admin');
include 'config.php';

$id = $_GET['id'];
$conn->query("DELETE FROM courses WHERE course_id = '$id'");
header("Location: manage_coursesadmin.php");
?>
