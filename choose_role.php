<?php
// choose_role.php
include('config/db.php');

$adminLogin = 'admin/login.php';
$staffLogin = 'staff/login.php';
$customerLogin = 'login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Choose Role</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    background: linear-gradient(135deg,rgb(193, 183, 204) 0%,rgb(129, 142, 163) 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .container {
    background: white;
    padding: 2.5rem 3rem;
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15);
    max-width: 700px;
  }
  h2 {
    font-weight: 700;
    color: #333;
    margin-bottom: 2rem;
  }
  .role-card {
    cursor: pointer;
    border-radius: 1rem;
    box-shadow: 0 0.3rem 0.8rem rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .role-card:hover {
    transform: translateY(-8px) scale(1.05);
    box-shadow: 0 0.7rem 1.8rem rgba(0,0,0,0.25);
  }
  .role-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
    user-select: none;
  }
  .card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #444;
    user-select: none;
  }
</style>
</head>
<body>
<div class="container text-center">
  <h2>Login As</h2>
  <div class="row justify-content-center">

    <div class="col-8 col-sm-6 col-md-4 mb-4">
      <div class="card role-card" onclick="window.location.href='<?= htmlspecialchars($adminLogin) ?>'">
        <div class="card-body d-flex flex-column align-items-center">
          <div class="role-icon">🛠️</div>
          <h5 class="card-title">Admin</h5>
        </div>
      </div>
    </div>

    <div class="col-8 col-sm-6 col-md-4 mb-4">
      <div class="card role-card" onclick="window.location.href='<?= htmlspecialchars($staffLogin) ?>'">
        <div class="card-body d-flex flex-column align-items-center">
          <div class="role-icon">👷‍♂️</div>
          <h5 class="card-title">Staff</h5>
        </div>
      </div>
    </div>

    <div class="col-8 col-sm-6 col-md-4 mb-4">
      <div class="card role-card" onclick="window.location.href='<?= htmlspecialchars($customerLogin) ?>'">
        <div class="card-body d-flex flex-column align-items-center">
          <div class="role-icon">👤</div>
          <h5 class="card-title">Customer</h5>
        </div>
      </div>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
