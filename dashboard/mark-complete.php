<?php
include "../config/database.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET["id"])) {
    $task_id = intval($_GET["id"]); // Secure ID
    $sql = "UPDATE tasks SET status='Completed' WHERE id='$task_id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: tasks.php?success=marked");
        exit();
    } else {
        echo "Error updating task: " . $conn->error;
    }
} else {
    header("Location: tasks.php");
    exit();
}
?>
