<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// ✅ Database Connection
include "../config/database.php"; 

// ✅ Fetch User Name if not in session
if (!isset($_SESSION["user_name"])) {
    $user_id = $_SESSION["user_id"];
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["user_name"] = $user["name"];
    } else {
        $_SESSION["user_name"] = "User";
    }
}

$user_name = $_SESSION["user_name"];

include "../includes/header.php";
?>

<link rel="stylesheet" href="../assets/css/style.css"> <!-- ✅ Universal CSS File -->

<div class="dashboard-container">
    <div class="dashboard-card">
        <!-- ✅ Left Content -->
        <div class="dashboard-content">
            <h2 class="dashboard-title">👋 Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
            <p class="dashboard-subtitle">
                This is your personal <strong>To-Do List Dashboard</strong>. Manage tasks efficiently, stay organized, and track productivity.
            </p>
        </div>

        <!-- ✅ Right Buttons -->
        <div class="dashboard-actions">
            <a href="tasks.php" class="btn btn-primary"><i class="fas fa-tasks"></i> View Tasks</a>
            <a href="add-task.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> Add Task</a>
            <a href="profile.php" class="btn btn-info"><i class="fas fa-user"></i> Profile</a>
            <a href="../auth/logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

<!-- ✅ Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if (isset($_SESSION["success_msg"])) { ?>
        Swal.fire({
            icon: 'success',
            title: ' <?php echo addslashes($_SESSION["success_msg"]); ?>',
            text: 'Welcome to your dashboard!',
            position: 'center',  // ✅ Center me dikhane ke liye
            showConfirmButton: false,
            timer: 3000
        });
        <?php unset($_SESSION["success_msg"]); ?>
    <?php } ?>
});
</script>



<?php include "../includes/footer.php"; ?>
