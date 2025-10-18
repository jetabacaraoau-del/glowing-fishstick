<?php
$token = $_GET["token"] ?? '';
$token_hash = hash("sha256", $token);

// Load the database connection
$mysqli = require __DIR__ . "/config/db.php";

// Query the user by the reset token
$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Invalid or expired token.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, sans-serif;
    background-color: #f0f2f5;
}

.form-container {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
    text-align: center;
}

.form-container h2 {
    margin-bottom: 25px;
}

.form-group {
    position: relative;
    margin-top: 15px;
}

.form-container label {
    display: block;
    text-align: left;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-container input[type="password"] {
    width: 100%;
    padding: 10px 40px 10px 10px; /* Adjust padding to make room for the icon */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.form-container input[type="text"] {
    width: 100%;
    padding: 10px 40px 10px 10px; /* Ensure consistent padding when password is visible */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.toggle-password {
    position: absolute;
    top: 36px;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    color: #555;
}

.form-container button[type="submit"] {
    margin-top: 25px;
    width: 100%;
    background-color: rgb(104, 160, 86);
    color: white;
    border: none;
    padding: 12px;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
}

.form-container button[type="submit"]:hover {
    background-color: rgb(91, 128, 82);
}

    </style>
</head>
<body>

<div class="form-container">
    <h2>Reset Your Password</h2>
    <form action="process-reset-password.php" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" name="password" id="password" required>
            <button type="button" class="toggle-password" onclick="toggleVisibility('password', this)">Show</button>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
            <button type="button" class="toggle-password" onclick="toggleVisibility('password_confirmation', this)">Show</button>
        </div>

        <button type="submit">Reset Password</button>
    </form>
</div>

<script>
function toggleVisibility(fieldId, toggleButton) {
    const field = document.getElementById(fieldId);
    if (field.type === "password") {
        field.type = "text";
        toggleButton.textContent = "Hide"; // Change to "Hide" when password is visible
    } else {
        field.type = "password";
        toggleButton.textContent = "Show"; // Change to "Show" when password is hidden
    }
}

</script>

</body>
</html>
