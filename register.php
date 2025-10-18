<?php
ob_start(); // start output buffering
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config/db.php');

if (isset($_POST['submit'])) {
    // Sanitize and validate input
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: register.php?message=1&error=' . urlencode('Invalid email format'));
        exit();
    }

    if ($password !== $confirm_password) {
        header('Location: register.php?message=4&error=' . urlencode('Passwords do not match'));
        exit();
    }
    
        // Password requirements
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        header('Location: register.php?message=5&error=' . urlencode('Password must be at least 8 characters long, include uppercase, lowercase, number, and special character.'));
        exit();
    }


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $checkQuery = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        header('Location: register.php?message=3&error=' . urlencode('Email already registered'));
        exit();
    }

    if ($stmt = mysqli_prepare($conn, "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)")) {
        mysqli_stmt_bind_param($stmt, "ssss", $first_name, $last_name, $email, $hashed_password);

        if (mysqli_stmt_execute($stmt)) {
            $user_id = mysqli_insert_id($conn);

            if ($stmt_data = mysqli_prepare($conn, "INSERT INTO user_data (userid, firstname, lastname) VALUES (?, ?, ?)")) {
                mysqli_stmt_bind_param($stmt_data, "iss", $user_id, $first_name, $last_name);

if (mysqli_stmt_execute($stmt_data)) {
    mysqli_stmt_close($stmt_data);

    $_SESSION['customer'] = $email;
    $_SESSION['customerid'] = $user_id;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Redirect to login.php after successful registration
    header('Location: login.php?message=registered');
    exit();


                } else {
                    $error_message = mysqli_error($conn);
                    header('Location: register.php?message=2&error=' . urlencode('Error inserting user data: ' . $error_message));
                    exit();
                }
            } else {
                $error_message = mysqli_error($conn);
                header('Location: register.php?message=2&error=' . urlencode('Database error (user_data insert prepare): ' . $error_message));
                exit();
            }
        } else {
            $error_message = mysqli_error($conn);
            header('Location: register.php?message=2&error=' . urlencode('Error inserting user: ' . $error_message));
            exit();
        }

        mysqli_stmt_close($stmt);
    } else {
        $error_message = mysqli_error($conn);
        header('Location: register.php?message=2&error=' . urlencode('Database error (users insert prepare): ' . $error_message));
        exit();
    }

    mysqli_close($conn);
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<style>
/* --- Body with fixed background --- */
body {
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 103vh;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
    color: #24292f;
    position: relative;
    overflow: hidden;
}

/* --- Blurred background image --- */
body::before {
    content: "";
    position: fixed;
    top: 0; 
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('inc/background.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    filter: blur(8px);           /* blur effect */
    transform: scale(1.05);      /* optional: avoid edges showing unblurred */
    z-index: -2;                 /* behind everything */
}

/* --- Dark overlay --- */
body::after {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.45); /* dark overlay on top of blurred bg */
    z-index: -1;
}

/* --- Container --- */
.container {
    width: 100%;
    max-width: 420px;
    padding: 40px 30px;
    background-color: rgba(255, 255, 255, 0.5);
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15), 0 4px 12px rgba(0,0,0,0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    margin-top: 100px;
}

.container:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.3), 0 6px 16px rgba(0,0,0,0.2);
}

/* --- Headings --- */
h2 {
    font-weight: 600;
    font-size: 24px;
    text-align: center;
    margin: 0 0 20px 0;   /* bottom margin consistent */
}

/* --- Form labels --- */
label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;    /* consistent spacing to next input */
}

/* --- Form inputs --- */
input[type="text"],
input[type="password"],
input[type="email"] {
    width: 100%;           /* full width within container */
    padding: 10px 12px;    /* consistent padding inside input */
    font-size: 14px;
    border: 1px solid #d0d7de;
    border-radius: 6px;
    margin-bottom: 15px;   /* consistent spacing between inputs */
    box-sizing: border-box; /* ensures padding doesn't break width */
    transition: border-color 0.2s;
}

input:focus {
    border-color: #0969da;
    outline: none;
    box-shadow: 0 0 0 2px rgba(9,105,218,0.3);
}

/* --- Error text --- */
.error-text {
    font-size: 13px;
    color: #d1242f;
    margin: -8px 0 12px 0; /* consistent spacing relative to input */
}

/* --- Buttons --- */
.btn-register {
    width: 100%;           
    padding: 12px;         
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    background: #6FB75A; /* light green */
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 15px;   
    transition: background 0.2s;
}

.btn-register:hover {
    background: #5aa24a; /* slightly darker green on hover */
}

/* --- Links --- */
.login-links {
    text-align: center;
    margin-top: 12px;      /* consistent spacing above links */
}

.login-links a {
    font-size: 14px;
    color: #0969da;
    text-decoration: none;
    margin: 0 4px;        /* spacing between links */
}

.login-links a:hover {
    text-decoration: underline;
}

</style>
</head>
<body>

<div class="container">
    <h2>Create Account</h2>
    <form method="post" novalidate>
        <div class="message">
            <?php if (isset($_GET['error'])) echo "<p class='error-text'>" . htmlspecialchars($_GET['error']) . "</p>"; ?>
        </div>

        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name" required>

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" id="last_name" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
        <div id="email-error" class="error-text"></div>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <div id="password-error" class="error-text"></div>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit" name="submit" class="btn-register">Create account</button>
    </form>

    <div class="login-links">
        <a href="login.php">Already have an account? Sign in</a>
    </div>
</div>


<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggleIcon = field.nextElementSibling.querySelector('i');
    
    if (field.type === "password") {
        field.type = "text";
        toggleIcon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        field.type = "password";
        toggleIcon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

</script>

<!-✅ AJAX Email Validation -->
<script>
document.querySelector("input[name='email']").addEventListener("blur", function() {
    const email = this.value.trim();
    if (email === "") return;

    fetch("check_email.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "email=" + encodeURIComponent(email)
    })
    .then(res => res.text())
    .then(data => {
        const errorBox = document.querySelector(".message");

        if (data === "exists") {
            errorBox.innerHTML = "<p style='color:red;'>Email already registered</p>";
        } else {
            errorBox.innerHTML = "";
        }
    })
    .catch(err => console.error(err));
});
</script>

<script>
    document.getElementById("password").addEventListener("input",function) {
        const password= this.value;
        let message = "";
        
        if(password.length < 8) ;
         message = "password must be at least 8 characters. ";
    }else if (!/[A-Z]/.test(password)) {
        message = "Password must contain at least one uppercase letter.";
    } else if (!/[a-z]/.test(password)) {
        message = "Password must contain at least one lowercase letter.";
    } else if (!/[0-9]/.test(password)) {
        message = "Password must contain at least one number.";
    } else if (!/[\W_]/.test(password)) {
        message = "Password must contain at least one special character.";
    }

    const errorBox = document.querySelector(".message");
    errorBox.innerHTML = message ? `<p>${message}</p>` : "";
});
</script>
</body>
</html>