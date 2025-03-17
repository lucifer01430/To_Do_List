<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}
include "../config/database.php";

$id = $_GET["id"];
$sql = "SELECT * FROM tasks WHERE id='$id'";
$result = $conn->query($sql);
$task = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $priority = $_POST["priority"];
    $deadline = $_POST["deadline"];

    $updateSQL = "UPDATE tasks SET title='$title', description='$description', priority='$priority', deadline='$deadline' WHERE id='$id'";
    if ($conn->query($updateSQL) === TRUE) {
        header("Location: tasks.php");
        exit();
    }
}

include "../includes/header.php";
?>

<div class="container mt-4 mb-3">
    <div class="task-card">
        <div class="task-form">
            
            <!-- ✅ Left Section: Task Edit Form -->
            <div class="form-section">
                <h3 class="fw-bold text-primary">✏️ Edit <span class="text-danger">Task</span></h3>
                <p class="text-muted">Update the task details below and stay organized.</p>
                
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">📝 Title:</label>
                        <input type="text" name="title" value="<?php echo $task['title']; ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">📖 Description:</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo $task['description']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">⚡ Priority:</label>
                        <select name="priority" class="form-select">
                            <option value="Low" <?php if ($task['priority'] == 'Low') echo 'selected'; ?>>🟢 Low</option>
                            <option value="Medium" <?php if ($task['priority'] == 'Medium') echo 'selected'; ?>>🟡 Medium</option>
                            <option value="High" <?php if ($task['priority'] == 'High') echo 'selected'; ?>>🔴 High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">📅 Deadline:</label>
                        <input type="date" name="deadline" value="<?php echo $task['deadline']; ?>" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 shadow">✅ Update Task</button>
                    <div class="text-center mt-3">
                        <a href="tasks.php" class="text-decoration-none text-primary fw-bold">⬅ Back to Task List</a>
                    </div>
                </form>
            </div>

            <!-- ✅ Right Section: Illustration -->
            <div class="image-section">
                <img src="https://cdn-icons-png.flaticon.com/512/3652/3652205.png" alt="Edit Task Illustration">

</div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
