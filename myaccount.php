<?php
include('config/db.php');
session_start();

if (!isset($_SESSION['customer']) || empty($_SESSION['customer']) || !isset($_SESSION['customerid'])) {
    header('location: login.php');
    exit;
}

$c_id = $_SESSION['customerid'];

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = $_FILES['profile_image']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExtension, $allowed)) {
        $uploadDir = 'uploads/profile_images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $newFileName = $c_id . '_' . time() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $safePath = mysqli_real_escape_string($conn, $destPath);
            mysqli_query($conn, "UPDATE user_data SET profile_image = '$safePath' WHERE userid = '$c_id'");
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $uploadError = "Failed to upload the image.";
        }
    } else {
        $uploadError = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    }
}

include('inc/nav.php');

$sql_user = "SELECT u.email, ud.profile_image, ud.firstname, ud.lastname, ud.mobile
             FROM users u JOIN user_data ud ON u.id = ud.userid WHERE u.id = '$c_id'";
$res_user = mysqli_query($conn, $sql_user);
$user_info = mysqli_fetch_assoc($res_user) ?: ['email' => '', 'profile_image' => '', 'firstname' => '', 'lastname' => '', 'mobile' => ''];
$avatar = !empty($user_info['profile_image']) ? $user_info['profile_image'] : "https://bootdey.com/img/Content/avatar/avatar7.png";
?>

<style>
.profile-card {
    background: #fff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    position: relative;
}
.edit-btn-top-right {
    position: absolute;
    top: 1rem;
    right: 1rem;
}
.avatar-wrapper {
    text-align: center;
    margin-bottom: 1.5rem;
    position: relative;
    display: inline-block;
}
.avatar-wrapper img {
    height: 140px;
    width: 140px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #dee2e6;
    transition: all 0.3s ease-in-out;
}
.avatar-wrapper img:hover {
    box-shadow: 0 0 10px rgba(0,123,255,0.6);
}
#profile_image_input {
    display: none;
}
.camera-icon-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    transform: translate(25%, 25%);
    background-color: #ffffff;
    border: 1px solid #ced4da;
    border-radius: 50%;
    padding: 6px 8px;
    cursor: pointer;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
}
.camera-icon-btn i {
    font-size: 16px;
    color: #495057;
}
.upload-error {
    color: red;
    text-align: center;
}
.profile-label {
    font-weight: 500;
    color: #6c757d;
}
.profile-value {
    font-size: 1rem;
    color: #212529;
}
</style>

<div class="container py-5">
    <h2 class="text-center fw-bold mb-4" style="font-size: 1.9rem; color: #333; margin-top: -45px;">My Profile</h2>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="profile-card" style="padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">

                <!-- Edit Profile Button -->
                <div class="edit-btn-top-right">
                    <a href="update_address.php" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; padding: 0.35rem 0.75rem;">
                        ✏️ Edit Profile
                    </a>
                </div>

                <!-- Profile Image -->
                <div class="text-center mb-3">
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="avatar-wrapper" style="position: relative; display: inline-block;">
                            <img src="<?= htmlspecialchars($avatar); ?>" id="avatarPreview" alt="Profile Picture"
                                 style="height: 140px; width: 140px; object-fit: cover; border-radius: 50%; border: 3px solid #dee2e6; transition: all 0.3s;">
                            <label for="profile_image_input" class="camera-icon-btn" style="position: absolute; bottom: 0; right: 0; transform: translate(25%, 25%); background: #fff; border: 1px solid #ced4da; border-radius: 50%; padding: 6px 8px; cursor: pointer;">
                                <i class="fa fa-camera" style="font-size: 16px; color: #495057;"></i>
                            </label>
                        </div>
                        <input type="file" name="profile_image" id="profile_image_input" accept="image/*" onchange="previewAvatar(event); document.getElementById('uploadForm').submit();" />
                    </form>
                    <?php if (!empty($uploadError)): ?>
                        <div class="upload-error mt-2" style="color: #d9534f; font-size: 0.9rem;"><?= htmlspecialchars($uploadError) ?></div>
                    <?php endif; ?>
                    <h4 class="mt-3" style="font-weight: 600; color: #222;"><?= htmlspecialchars($user_info['firstname'] . ' ' . $user_info['lastname']) ?></h4>
                </div>

                <!-- User Info -->
                <hr style="border-color: #e9ecef;">
                <div class="row mb-3">
                    <div class="col-sm-4 profile-label" style="font-weight: 500; color: #6c757d;">Full Name</div>
                    <div class="col-sm-8 profile-value" style="font-size: 1rem; color: #212529;"><?= htmlspecialchars($user_info['firstname'] . ' ' . $user_info['lastname']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 profile-label" style="font-weight: 500; color: #6c757d;">Email</div>
                    <div class="col-sm-8 profile-value" style="font-size: 1rem; color: #212529;"><?= htmlspecialchars($user_info['email']) ?></div>
                </div>
                <div class="row mb-0">
                    <div class="col-sm-4 profile-label" style="font-weight: 500; color: #6c757d;">Mobile</div>
                    <div class="col-sm-8 profile-value" style="font-size: 1rem; color: #212529;"><?= htmlspecialchars($user_info['mobile']) ?></div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
