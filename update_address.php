<?php
include('config/db.php');
session_start();

// Redirect if customer is not logged in
if (!isset($_SESSION['customer']) || !isset($_SESSION['customerid'])) {
    header('Location: login.php');
    exit;
}

$c_id = $_SESSION['customerid'];

// Fetch current user data
$sql_user = "SELECT firstname, lastname, mobile FROM user_data WHERE userid = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $c_id);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user_info = $res_user->fetch_assoc();

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $mobile = trim($_POST['mobile']);

    // Validate inputs
    if ($firstname && $lastname && $mobile) {
        $update_sql = "UPDATE user_data SET firstname = ?, lastname = ?, mobile = ? WHERE userid = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("sssi", $firstname, $lastname, $mobile, $c_id);

        if ($stmt_update->execute()) {
            header('Location: myaccount.php');
            exit;
        } else {
            $message = '<div class="alert alert-danger">Error updating profile. Please try again.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">All fields are required.</div>';
    }
}

include('inc/nav.php');
?>

<style>
.card {
    box-shadow: 0 1px 3px rgba(0,0,0,.1), 0 1px 2px rgba(0,0,0,.06);
    background-color: #fff;
    border-radius: 0.25rem;
}
.card-body {
    padding: 1.25rem 1.5rem;
}
label {
    font-weight: 600;
}
.form-control {
    border-radius: 0.25rem;
}
.btn-info {
    min-width: 120px;
    margin-top: 10px;
    transition: background-color 0.3s ease;
}
.btn-info:hover {
    background-color: #117a8b;
    border-color: #117a8b;
}
</style>

<div class="container">
    <div class="main-body">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">

                        <h4 class="mb-4">Update Profile</h4>

                        <?php echo $message; ?>

                        <form method="POST" action="">

                            <div class="mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" name="firstname" id="firstname" class="form-control" required
                                    value="<?= htmlspecialchars($user_info['firstname'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" name="lastname" id="lastname" class="form-control" required
                                    value="<?= htmlspecialchars($user_info['lastname'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="tel" name="mobile" id="mobile" class="form-control" required
                                    value="<?= htmlspecialchars($user_info['mobile'] ?? '') ?>">
                            </div>

                            <button type="submit" class="btn btn-info">Save Changes</button>
                            <button type="button" class="btn btn-secondary ms-2" onclick="history.back();">Cancel</button>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>