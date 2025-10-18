<?php

// Get the submitted email and token
$email = $_POST["email"];
$token = $_POST["token"];

if (!isset($email) || empty($email)) {
    die("Email is required.");
}

$token_hash = hash("sha256", $token);

// Create a connection to the database
$mysqli = require __DIR__ . "/config/db.php";

// Prepare the SQL query to find the user based on token and email
$sql = "SELECT * FROM users WHERE reset_token_hash = ? AND email = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $token_hash, $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    die("Token not found or email mismatch.");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Token has expired.");
}

// Validate the password
if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters.");
}

if (!preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter.");
}

if (!preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number.");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match.");
}

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

// Update the password and reset the token
$sql = "UPDATE users
        SET password = ?,  -- ← changed from password_hash
            reset_token_hash = NULL,
            reset_token_expires_at = NULL
        WHERE id = ?";


$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $password_hash, $user["id"]);
$stmt->execute();

echo "Password updated. You can now login.";
?>
