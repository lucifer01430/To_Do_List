<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

include "../config/database.php";

$user_id = $_SESSION["user_id"];
$result = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$user = $result->fetch_assoc();

if (!$user) {
    $user = ['name' => '', 'email' => '', 'mobile' => '', 'address' => '', 'profile_photo' => ''];
}

$msg = "";
$upload_dir = "../uploads/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $mobile = trim($_POST["mobile"]);
    $address = trim($_POST["address"]);

    if (!empty($_FILES["profile_photo"]["name"])) {
        $file_name = time() . "_" . basename($_FILES["profile_photo"]["name"]);
        $target_file = $upload_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                if (!empty($user["profile_photo"]) && file_exists($upload_dir . $user["profile_photo"])) {
                    unlink($upload_dir . $user["profile_photo"]);
                }
                $profile_photo = $file_name;
            }
        }
    } else {
        $profile_photo = $user["profile_photo"];
    }

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, mobile=?, address=?, profile_photo=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $email, $mobile, $address, $profile_photo, $user_id);

    if ($stmt->execute()) {
        $_SESSION["user_name"] = $name;
        $msg = "<div class='alert alert-success'>✅ Profile updated successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ Error updating profile: " . $conn->error . "</div>";
    }
}

include "../includes/header.php";
?>

<!-- ✅ Dashboard Style Profile Page -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <!-- ✅ Left Side - Profile Form -->
        <div class="col-md-6">
            <div class="card shadow p-4">
                <h2 class="fw-bold text-primary text-center">👤 My Profile</h2>
                <hr>
                <?php echo $msg; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="text-center mb-3">
                        <img id="profilePreview" src="<?php echo !empty($user["profile_photo"]) ? '../uploads/' . $user["profile_photo"] : 'https://via.placeholder.com/150'; ?>" 
                             class="profile-img" 
                             alt="Profile Picture">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mobile Number:</label>
                        <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Address:</label>
                        <textarea name="address" class="form-control" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Profile Picture:</label>
                        <input type="file" name="profile_photo" class="form-control" id="profileUpload">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- ✅ Right Side - Profile Details Card -->
        <div class="col-md-4">
            <div class="card shadow p-4 profile-card text-center">
                <h4 class="fw-bold text-secondary">📋 Profile Details</h4>
                <hr>
                <div class="d-flex justify-content-center">
                    <img id="profileDisplay" src="<?php echo !empty($user["profile_photo"]) ? '../uploads/' . $user["profile_photo"] : 'https://via.placeholder.com/150'; ?>" 
                         class="profile-img" 
                         alt="Profile Picture">
                </div>
                <p class="mt-3"><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($user['mobile']); ?></p>
                <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($user['address'])); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
// ✅ Fix: Image Upload Issue (Triggers Properly)
document.getElementById("profileUpload").addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const profilePreview = document.getElementById("profilePreview");
            const profileDisplay = document.getElementById("profileDisplay");

            if (profilePreview) profilePreview.src = e.target.result;
            if (profileDisplay) profileDisplay.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// ✅ Fix: Toast Message Auto-Hide After 3s
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function() {
        let toast = document.getElementById('toastMessage');
        if (toast) {
            toast.style.transition = 'opacity 1s';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast) toast.remove(); 
            }, 1000);
        }
    }, 3000);
});
</script>


<?php include "../includes/footer.php"; ?>
