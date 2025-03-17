<?php
session_start();
include "../config/database.php";

$nameErr = $emailErr = $passwordErr = $confirmPasswordErr = "";
$name = $email = "";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $valid = true;

    // ✅ PHP Server-Side Validation
    if (empty($name)) {
        $nameErr = "❌ Name is required!";
        $valid = false;
    }
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
    } elseif (strlen($password) < 6) {
        $passwordErr = "❌ Password must be at least 6 characters!";
        $valid = false;
    }
    if ($password !== $confirm_password) {
        $confirmPasswordErr = "❌ Passwords do not match!";
        $valid = false;
    }

    if ($valid) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkUser->bind_param("s", $email);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows > 0) {
            $emailErr = "❌ Email already registered!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION["registered_successfully"] = true; // ✅ Session for toast message
                $_SESSION["success_msg2"] = "Registered Successfully!"; 
                header("Location: login.php"); // ✅ Redirect to login page
                exit();
            } else {
                $msg = "<div class='alert alert-danger'>❌ Registration failed. Try again!</div>";
            }
        }
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="text-center fw-bold text-primary">📝 Register</h3>
                <hr>
                <?php if (!empty($msg)) echo $msg; ?>

                <form method="post" id="registerForm" novalidate>
                    <div class="form-floating mb-3">
                        <input type="text" name="name" id="name" class="form-control <?php echo (!empty($nameErr)) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo htmlspecialchars($name); ?>" required>
                        <label for="name">👤 Full Name</label>
                        <div class="invalid-feedback"><?php echo $nameErr; ?></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" name="email" id="email" class="form-control <?php echo (!empty($emailErr)) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo htmlspecialchars($email); ?>" required>
                        <label for="email">📧 Email Address</label>
                        <div class="invalid-feedback"><?php echo $emailErr; ?></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($passwordErr)) ? 'is-invalid' : ''; ?>" required>
                        <label for="password">🔑 Password</label>
                        <div class="invalid-feedback"><?php echo $passwordErr; ?></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirmPasswordErr)) ? 'is-invalid' : ''; ?>" required>
                        <label for="confirm_password">🔒 Confirm Password</label>
                        <div class="invalid-feedback"><?php echo $confirmPasswordErr; ?></div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 shadow">🚀 Register</button>

                    <div class="text-center mt-3">
                        <small>Already have an account? <a href="login.php" class="fw-bold text-primary">Login Here</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ✅ Live Form Validation (JavaScript)
document.addEventListener("DOMContentLoaded", function () {
    let nameInput = document.getElementById("name");
    let emailInput = document.getElementById("email");
    let passwordInput = document.getElementById("password");
    let confirmPasswordInput = document.getElementById("confirm_password");

    function validateInput(input, condition) {
        if (condition) {
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
        } else {
            input.classList.remove("is-valid");
            input.classList.add("is-invalid");
        }
    }

    // ✅ Name Validation
    nameInput.addEventListener("input", function () {
        validateInput(nameInput, nameInput.value.trim().length > 0);
    });

    // ✅ Email Validation
    emailInput.addEventListener("input", function () {
        let emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
        validateInput(emailInput, emailPattern.test(emailInput.value));
    });

    // ✅ Password Validation
    passwordInput.addEventListener("input", function () {
        validateInput(passwordInput, passwordInput.value.length >= 6);
    });

    // ✅ Confirm Password Validation
    confirmPasswordInput.addEventListener("input", function () {
        validateInput(confirmPasswordInput, confirmPasswordInput.value === passwordInput.value);
    });
});
</script>

<?php include "../includes/footer.php"; ?>
