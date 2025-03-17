<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

include "../config/database.php";

$user_id = $_SESSION["user_id"];

// ✅ Default Sorting & Filtering
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'priority';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// ✅ Build SQL Query with Sorting & Filtering
$query = "SELECT * FROM tasks WHERE user_id='$user_id'";

if (!empty($filter_status)) {
    $query .= " AND status = '$filter_status'";
}

if ($sort_by == "deadline") {
    $query .= " ORDER BY deadline ASC";
} elseif ($sort_by == "status") {
    $query .= " ORDER BY status ASC";
} else {
    $query .= " ORDER BY 
        CASE 
            WHEN priority = 'High' THEN 1 
            WHEN priority = 'Medium' THEN 2 
            WHEN priority = 'Low' THEN 3 
        END, deadline ASC";
}

$result = $conn->query($query);

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2 class="mb-4 text-primary fw-bold">
        <i class="fas fa-tasks"></i> My Tasks
    </h2>

    <!-- ✅ Sorting & Filtering Form -->
    <div class="card p-3 mb-4 shadow-sm border-0 rounded-3">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-6">
                <label class="form-label fw-bold"><i class="fas fa-sort"></i> Sort By:</label>
                <select name="sort_by" class="form-select border-0 shadow-sm" onchange="this.form.submit()">
                    <option value="priority" <?= $sort_by == 'priority' ? 'selected' : '' ?>>Priority</option>
                    <option value="deadline" <?= $sort_by == 'deadline' ? 'selected' : '' ?>>Deadline</option>
                    <option value="status" <?= $sort_by == 'status' ? 'selected' : '' ?>>Status</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold"><i class="fas fa-filter"></i> Filter By Status:</label>
                <select name="filter_status" class="form-select border-0 shadow-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="Completed" <?= $filter_status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
        </form>
    </div>

    <!-- ✅ Task Table -->
    <div class="table-responsive rounded-3 shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-primary text-dark fw-bold">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th class="text-center">Priority</th>
                    <th>Deadline</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr class="border-bottom">
                        <td><?php echo $row["title"]; ?></td>
                        <td><?php echo $row["description"]; ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?php echo ($row["priority"] == 'High') ? 'danger' : (($row["priority"] == 'Medium') ? 'warning' : 'success'); ?> p-2">
                                <?php echo $row["priority"]; ?>
                            </span>
                        </td>
                        <td><?php echo date("d M Y", strtotime($row["deadline"])); ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?php echo ($row["status"] == 'Completed') ? 'success' : 'secondary'; ?> p-2">
                                <?php echo $row["status"]; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="edit-task.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete-task.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php if ($row["status"] == "Pending") { ?>
                                <a href="mark-complete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>


<?php include "../includes/footer.php"; ?>
