<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // PHPMailer Include Karein
include "../config/database.php";
session_start();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $checkEmail = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($checkEmail->num_rows > 0) {
        $otp = rand(100000, 999999); // 6-Digit OTP Generate
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;

        // **Send OTP via Email**
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@gmail.com'; // ✅ Replace with your email
            $mail->Password   = 'your-app-password'; // ✅ Replace with your App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // **Sender Email**
            $mail->setFrom('your-email@gmail.com', 'To-Do List App');

            // **Recipient Email**
            $mail->addAddress($email);
            
            $mail->Subject = 'To-Do List - Password Reset OTP';
            
            // **HTML Email Formatting**
            $mail->isHTML(true);
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px;'>
                    <h3 style='color: #007bff;'>🔑 Password Reset Request</h3>
                    <p>Hello,</p>
                    <p>We received a request to reset your password for the To-Do List App.</p>
                    <p><strong>Your OTP for password reset is: <span style='color:blue;'>$otp</span></strong></p>
                    <p>Please enter this OTP to reset your password.</p>
                    <br>
                    <p>If you did not request a password reset, please ignore this email.</p>
                    <p>Thank you!<br><strong>To-Do List Support Team</strong></p>
                </div>
            ";

            $mail->send();
            $_SESSION["success_msg"] = "✅ OTP Sent to Your Email!";
            header("Location: reset-password.php");
            exit();
        } catch (Exception $e) {
            $_SESSION["error_msg"] = "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION["error_msg"] = "❌ Email not found! Please enter a registered email.";
    }
}

include "../includes/header.php";
?>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="col-md-4">
        <div class="card shadow-lg p-4 border-0 rounded-4">
            <h3 class="text-center fw-bold text-primary">🔑 Forgot Password?</h3>
            <p class="text-center text-muted">Enter your email to receive an OTP for password reset.</p>
            <hr>

            <form method="post" id="forgotPasswordForm">
                <div class="mb-3">
                    <label class="form-label fw-bold">📧 Email Address:</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 shadow">📩 Send OTP</button>
                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none text-primary fw-bold">⬅ Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let successMsg = "<?php echo isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : ''; ?>";
    let errorMsg = "<?php echo isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : ''; ?>";

    if (successMsg) {
        Swal.fire({ icon: 'success', title: 'Success!', text: successMsg, timer: 3000, showConfirmButton: false });
        <?php unset($_SESSION['success_msg']); ?>
    }

    if (errorMsg) {
        Swal.fire({ icon: 'error', title: 'Error!', text: errorMsg, timer: 3000, showConfirmButton: false });
        <?php unset($_SESSION['error_msg']); ?>
    }
});
</script>

<?php include "../includes/footer.php"; ?>
