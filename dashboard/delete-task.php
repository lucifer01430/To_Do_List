 
<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}
include "../config/database.php";

$id = $_GET["id"];
$sql = "DELETE FROM tasks WHERE id='$id'";
$conn->query($sql);

header("Location: tasks.php");
exit();
?>
