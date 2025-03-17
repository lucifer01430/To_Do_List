<?php
include "../config/database.php";
session_start();
$msg = "";

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST["otp"];
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    if ($entered_otp == $_SESSION['reset_otp']) {
        $email = $_SESSION['reset_email'];
        $conn->query("UPDATE users SET password='$new_password' WHERE email='$email'");

        // Clear Session Data and Redirect to Login Page
        $_SESSION['reset_success'] = "✅ Password reset successful! Please login with your new password.";
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_otp']);
        header("Location: login.php"); // ✅ Redirect to login page
        exit();
    } else {
        $msg = "<div class='alert alert-danger'>❌ Incorrect OTP. Try Again!</div>";
    }
}

include "../includes/header.php";
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="text-center">Reset Password</h3>
                <?php echo $msg; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Enter OTP:</label>
                        <input type="number" name="otp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password:</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
