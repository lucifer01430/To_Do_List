<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}
include "../config/database.php";

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $priority = $_POST["priority"];
    $deadline = $_POST["deadline"];
    $user_id = $_SESSION["user_id"];

    $sql = "INSERT INTO tasks (user_id, title, description, priority, deadline) VALUES ('$user_id', '$title', '$description', '$priority', '$deadline')";
    if ($conn->query($sql) === TRUE) {
        $msg = "<div class='alert alert-success'>✅ Task added successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ Error: " . $conn->error . "</div>";
    }
}

include "../includes/header.php";
?>

<div class="container mt-4 mb-3">
    <div class="task-card">
        <div class="task-form">
            
            <!-- ✅ Left Section: Task Form -->
            <div class="form-section">
                <h3 class="fw-bold text-primary">📌 Add <span class="text-danger">New Task</span></h3>
                <p class="text-muted">Organize your tasks efficiently and boost productivity.</p>
                
                <?php echo $msg; ?>
                
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">📝 Title:</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter task title..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">📖 Description:</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter task details..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">⚡ Priority:</label>
                        <select name="priority" class="form-select">
                            <option value="Low">🟢 Low</option>
                            <option value="Medium">🟡 Medium</option>
                            <option value="High">🔴 High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">📅 Deadline:</label>
                        <input type="date" name="deadline" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 shadow">✅ Save Task</button>
                    <div class="text-center mt-3">
                        <a href="tasks.php" class="text-decoration-none text-primary fw-bold">⬅ Back to Task List</a>
                    </div>
                </form>
            </div>

            <!-- ✅ Right Section: Illustration -->
            <div class="image-section">
            <img src="https://cdn-icons-png.flaticon.com/512/3652/3652191.png" alt="Add Task Illustration">
            </div>
        </div>
    </div>
</div>



<?php include "../includes/footer.php"; ?>
