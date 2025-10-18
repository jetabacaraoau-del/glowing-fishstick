<?php
$fromCart = isset($_GET['from_cart']) && $_GET['from_cart'] == 1;
session_start();
include('config/db.php');

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    // Sanitize inputs
    $email = trim($_POST['email']);
    $password = $_POST['pswd'];

    // Prepare statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Assuming passwords are hashed with password_hash() in DB
        if (password_verify($password, $user['password'])) {
            // Password correct - start session and redirect to dashboard or home
            $_SESSION['customerid'] = $user['id'];
            $_SESSION['customer'] = $user['email'];
            
              // Set success message
            $_SESSION['login_success'] = true;
            header("Location: index.php"); // or wherever you redirect after login
            exit();
        } else {
            // Password incorrect
            header('Location: login.php?message=1');  // 1 = wrong credentials
            exit();
        }
    } else {
        // User not found
        header('Location: login.php?message=1');
        exit();
    }
}

$customerOnly = isset($_GET['role']) && $_GET['role'] === 'customer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login</title>
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
/>
<style>
   body {
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center; /* center horizontally */
    align-items: center;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    position: relative;
    overflow: hidden;
}

/* Blurred background image */
body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    background-image: url('inc/background.jpg');
    background-size: cover;
    background-position: center;
    filter: blur(8px);
    transform: scale(1.05);
    z-index: -2;
}

/* Overlay for readability */
body::after {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    z-index: -1;
}

/* Container */
.container {
    width: 100%;
    max-width: 420px;
    padding: 40px 30px;
    background-color: rgba(255, 255, 255, 0.5);
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15), 0 4px 12px rgba(0,0,0,0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

/* Header */
.login-form h2 {
    font-weight: 700;
    font-size: 36px;
    text-align: center;
    margin-bottom: 20px;
    color: #2c3e50;
}

/* Role icons */
.role-icons {
    display: flex;
    justify-content: center;
    gap: 24px;
    margin-bottom: 24px;
}
.role-icons a, .role-icons div {
    text-align: center;
    color: #2c3e50;
    text-decoration: none;
}
.role-icons .active {
    color: #6FB75A;
    font-weight: bold;
    cursor: default;
}
.role-icons i {
    font-size: 2rem;
}
.role-icons span {
    display: block;
    font-size: 0.95rem;
    margin-top: 4px;
}

/* Inputs */
input[type="text"],
input[type="password"],
#captcha-input {
    width: 100%;
    padding: 14px 0px;
    font-size: 15px;
    border: 2px solid #ccc;
    border-radius: 12px;
    margin-bottom: 16px;
    font-family: inherit;
    background-color: #fff;
    color: #333;
}

/* Password toggle */
.password-container {
    position: relative;
}
.password-container input[type="password"] {
    padding-right: 0px;
}
.toggle-password {
    position: absolute;
    top: 50%;
    right: 0px;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 20px;
    color: #888;
}

/* CAPTCHA */
#captcha-container {
    background: linear-gradient(135deg, #fafafa, #e8f5e9);
    padding: 14px 18px;
    border-radius: 12px;
    border: 1.5px solid #c8e6c9;
    margin-bottom: 20px;
    text-align: center;
}
#captcha-canvas {
    border-radius: 10px;
    width: 100%;
    max-width: 260px;
    margin-bottom: 12px;
}

/* Buttons */
input[type="submit"], .login-btn {
    width: 100%;
    padding: 14px 0;
    font-size: 18px;
    font-weight: 700;
    border: none;
    border-radius: 12px;
    background-color: #6FB75A;
    color: #fff;
    cursor: pointer;
    transition: 0.3s ease;
    margin-top: 12px;
}
input[type="submit"]:hover, .login-btn:hover {
    background-color: #5aa24a;
    transform: scale(1.03);
}

/* Links */
.login-links {
    text-align: center;
    margin-top: 18px;
}
.login-links a {
    color: #2c3e50;
    text-decoration: none;
    margin: 0 8px;
    display: inline-block;
    transition: 0.3s;
}
.login-links a:hover {
    color: #6FB75A;
}
.login-links span {
    color: #007BFF;
    font-weight: bold;
}

</style>
</head>
<body>
    <div class="container" aria-label="Login form container">
        <div class="login-form" role="main">
             <div class="login-form-inner">
                 <h2 style="text-align: center; font-size: 36px; color: #2c3e50; margin-bottom: 24px;">Seventeas Diner</h2>
                
                    <!-- Role icons start -->
                    <?php if (!$fromCart): ?>
                    <div class="role-icons">
                        <a href="admin/login.php" title="Admin Login">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin</span>
                        </a>
                        <a href="staff/login.php" title="Staff Login">
                            <i class="fas fa-user-tie"></i>
                            <span>Staff</span>
                        </a>
                        <a href="customer/login.php" title="Customer Login" class="active">
                            <i class="fas fa-user"></i>
                            <span>Customer</span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <!-- Role icons end -->
                </div>

                <form method="post" novalidate>
                    <div class="message" aria-live="polite" role="alert">
                        <?php if (isset($_GET['message']) && $_GET['message'] == 1) echo "<p>Incorrect email or password.</p>"; ?>
                    </div>
                    <input
                      type="text"
                      name="email"
                      placeholder="E-mail Address"
                      required
                      aria-label="Email"
                    />
                    <div class="password-container">
                        <input
                          type="password"
                          name="pswd"
                          id="password"
                          placeholder="Password"
                          required
                          autocomplete="current-password"
                          aria-label="Password"
                        />
                        <button
                          type="button"
                          class="toggle-password"
                          aria-label="Toggle password visibility"
                          onclick="togglePassword()"
                        >
                          <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="captcha-container">
                        <canvas
                          id="captcha-canvas"
                          width="200"
                          height="50"
                          aria-hidden="true"
                        ></canvas>
                        <input
                          type="text"
                          id="captcha-input"
                          name="captcha"
                          placeholder="Enter the characters shown"
                          required
                          autocomplete="off"
                          aria-describedby="captchaHelp"
                          aria-label="CAPTCHA input"
                        />
                        <input type="hidden" id="captcha-answer" />
                    </div>
                   <button type="submit" name="submit" class="login-btn">Login</button>
                </form>
                <div class="login-links" style="text-align: center; margin-top: 10px;">
                  <a href="forgot-password.php" class="forgot-password"><h4>Forgot Password?</h4></a>
                  <br />
                  <a href="register.php" class="sign-up"><h4>Don't have an Account? <span>Sign up</span></h4></a>
                </div>
            </div>
        </div>
    </div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleButton = document.querySelector('.toggle-password i');
    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleButton.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        passwordField.type = "password";
        toggleButton.classList.replace("fa-eye-slash", "fa-eye");
    }
}

function generateCaptcha(length = 6) {
    const canvas = document.getElementById('captcha-canvas');
    const ctx = canvas.getContext('2d');
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let captcha = '';

    for (let i = 0; i < length; i++) {
        captcha += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    document.getElementById('captcha-answer').value = captcha;

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#e6e6e6';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    for (let i = 0; i < 5; i++) {
        ctx.beginPath();
        ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.strokeStyle = `rgba(0,0,0,${Math.random()})`;
        ctx.stroke();
    }

    ctx.font = '24px Courier New';
    ctx.fillStyle = '#000';
    ctx.textBaseline = 'middle';
    ctx.textAlign = 'center';
    ctx.fillText(captcha, canvas.width / 2, canvas.height / 2);

    for (let i = 0; i < 30; i++) {
        ctx.beginPath();
        ctx.arc(Math.random() * canvas.width, Math.random() * canvas.height, 1, 0, 2 * Math.PI);
        ctx.fillStyle = '#555';
        ctx.fill();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    generateCaptcha();

    const form = document.querySelector('form');
    form.addEventListener('submit', function (e) {
        const userInput = document.getElementById('captcha-input').value.trim();
        const actualCaptcha = document.getElementById('captcha-answer').value;

        if (userInput !== actualCaptcha) {
            alert('Incorrect CAPTCHA. Please try again.');
            e.preventDefault();
            generateCaptcha();
            document.getElementById('captcha-input').value = '';
        }
    });
});
</script>
</body>
</html>