<?php
session_start();
include('../config/db.php');

// Handle form submission
if (isset($_POST['submit'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $password   = mysqli_real_escape_string($conn, $_POST['password']); // plain text password

    // Check if email exists
    $check_email = mysqli_query($conn, "SELECT * FROM staff WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $message = "Email already exists!";
    } else {
        $insert = "INSERT INTO staff (first_name, last_name, email, password)
                   VALUES ('$first_name', '$last_name', '$email', '$password')";
        if (mysqli_query($conn, $insert)) {
            $message = "Staff added successfully!";
        } else {
            $message = "Failed to add staff.";
        }
    }
}
?>

<?php include('inc/header.php'); ?>
<?php include('inc/nav.php'); ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h4>Add Staff</h4>
        </div>
        <div class="card-body">
            <?php if (isset($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group mb-3">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group mb-3">
    <label>Password</label>
    <div class="input-group">
        <input type="text" name="password" id="password" class="form-control" required>
        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">Show</button>
    </div>
</div>

                <button type="submit" name="submit" class="btn btn-primary">Add Staff</button>
            </form>
        </div>
    </div>
</div>

<?php include('inc/footer.php'); ?>
<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const button = passwordField.nextElementSibling;

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        button.textContent = 'Hide';
    } else {
        passwordField.type = 'password';
        button.textContent = 'Show';
    }
}
</script>
