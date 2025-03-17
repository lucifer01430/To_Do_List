<?php
session_start();
include "../config/database.php";

$emailErr = $passwordErr = "";
$email = "";
$msg = "";
$blockTime = 3 * 60 * 60; // 3 hours in seconds
$showForm = true; 

if (isset($_COOKIE["remember_email"])) {
    $email = $_COOKIE["remember_email"];
}

// ✅ Check for Success or Error Messages
$success_msg = isset($_SESSION["success_msg"]) ? $_SESSION["success_msg"] : "";
$error_msg = isset($_SESSION["error_msg"]) ? $_SESSION["error_msg"] : "";
unset($_SESSION["success_msg"], $_SESSION["error_msg"]); // ✅ Remove after showing once

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $remember = isset($_POST["remember"]) ? 1 : 0;
    $valid = true;

    if (empty($email)) {
        $emailErr = "❌ Email is required!";
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "❌ Invalid email format!";
        $valid = false;
    }
    if (empty($password)) {
        $passwordErr = "❌ Password is required!";
        $valid = false;
    }

    if ($valid) {
        $stmt = $conn->prepare("SELECT id, name, password, failed_attempts, blocked_until FROM users WHERE email = ?");
        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user["id"];

            if (!is_null($user['blocked_until']) && strtotime($user['blocked_until']) > time()) {
                $remaining_time = round((strtotime($user['blocked_until']) - time()) / 60);
                $_SESSION["error_msg"] = "🚫 Account locked! Try again in <b>$remaining_time minutes.</b>";
                header("Location: login.php");
                exit();
            } else {
                if (!is_null($user['blocked_until']) && strtotime($user['blocked_until']) < time()) {
                    $resetAttempts = $conn->prepare("UPDATE users SET failed_attempts = 0, blocked_until = NULL WHERE id = ?");
                    $resetAttempts->bind_param("i", $user_id);
                    $resetAttempts->execute();
                }

                if (password_verify($password, $user['password'])) {
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["user_name"] = $user["name"];

                    if ($remember) {
                        setcookie("remember_email", $email, time() + (86400 * 30), "/");
                    } else {
                        setcookie("remember_email", "", time() - 3600, "/");
                    }

                    $resetAttempts = $conn->prepare("UPDATE users SET failed_attempts = 0, blocked_until = NULL WHERE id = ?");
                    $resetAttempts->bind_param("i", $user_id);
                    $resetAttempts->execute();

                    $_SESSION["success_msg"] = " Welcome back, " . $user["name"] . "!";
                    header("Location: ../dashboard/dashboard.php");
                    exit();
                } else {
                    $failed_attempts = $user["failed_attempts"] + 1;

                    if ($failed_attempts >= 3) {
                        $block_until = date("Y-m-d H:i:s", time() + $blockTime);
                        $stmt = $conn->prepare("UPDATE users SET failed_attempts = ?, blocked_until = ? WHERE id = ?");
                        $stmt->bind_param("isi", $failed_attempts, $block_until, $user_id);
                        $stmt->execute();
                        $_SESSION["error_msg"] = "🚨 Too many failed attempts! Account locked for <b>3 hours</b>.";
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                        $stmt->bind_param("ii", $failed_attempts, $user_id);
                        $stmt->execute();
                        $attempts_left = 3 - $failed_attempts;
                        $_SESSION["error_msg"] = "⚠️ Wrong password. <b>$attempts_left attempts</b> left.";
                    }
                    header("Location: login.php");
                    exit();
                }
            }
        } else {
            $_SESSION["error_msg"] = "❌ No account found with this email!";
            header("Location: login.php");
            exit();
        }
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="col-md-4">
        <div class="card shadow-lg p-4 border-0 rounded-4">
            <h3 class="text-center fw-bold text-primary">🔐 Login</h3>
            <hr>

            <form method="post" id="loginForm">
                <div class="mb-3">
                    <label class="form-label fw-bold">📧 Email:</label>
                    <input type="email" name="email" id="email" class="form-control <?php echo !empty($emailErr) ? 'is-invalid' : ''; ?>" 
                    value="<?php echo htmlspecialchars($email); ?>" required>
                    <div class="invalid-feedback"><?php echo $emailErr; ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">🔑 Password:</label>
                    <input type="password" name="password" id="password" class="form-control <?php echo !empty($passwordErr) ? 'is-invalid' : ''; ?>" required>
                    <div class="invalid-feedback"><?php echo $passwordErr; ?></div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember" <?php echo isset($_COOKIE["remember_email"]) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    <a href="forgot-password.php" class="text-decoration-none text-primary fw-bold">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3 shadow">🚀 Login</button>
                <div class="text-center mt-3">
                   <small>New User? <a href="register.php" class="fw-bold text-primary">Register Here</a></small>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ✅ Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if (isset($_SESSION["success_msg2"])) { ?>
        Swal.fire({
            icon: 'success',
            title: '🎉 <?php echo addslashes($_SESSION["success_msg2"]); ?>',
            text: 'Registered Successfully!',
            position: 'center',  // ✅ Center me dikhane ke liye
            showConfirmButton: false,
            timer: 3000
        });
        <?php unset($_SESSION["success_msg2"]); ?>
    <?php } ?>
});
</script>
<script>
// ✅ Live Form Validation (JavaScript)
document.addEventListener("DOMContentLoaded", function () {
    let emailInput = document.getElementById("email");
    let passwordInput = document.getElementById("password");

    function validateInput(input, condition) {
        if (condition) {
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
        } else {
            input.classList.remove("is-valid");
            input.classList.add("is-invalid");
        }
    }

    // ✅ Email Validation
    emailInput.addEventListener("input", function () {
        let emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
        validateInput(emailInput, emailPattern.test(emailInput.value));
    });

    // ✅ Password Validation
    passwordInput.addEventListener("input", function () {
        validateInput(passwordInput, passwordInput.value.length >= 6);
    });

});
</script>


<?php include "../includes/footer.php"; ?>
