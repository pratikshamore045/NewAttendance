<?php
session_start();
//echo "in log out";
$_SESSION['user_id'] = null;

header("location:signin.php");
?>







