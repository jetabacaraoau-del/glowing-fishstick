<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <style>
/* --- RESET & LAYOUT --- */
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background-color: #fdfaf6;
  display: flex;
  min-height: 100vh;
  color: #333;
}

/* --- HEADER --- */
.header {
  background-color: #ffffff;
  border-bottom: 2px solid #eee;
  height: 70px;
  position: fixed;
  left: 230px; /* beside sidebar */
  right: 0;
  top: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 25px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.header h4 {
  margin: 0;
  font-weight: 700;
  font-size: 1.3rem;
  letter-spacing: 0.5px;
  color: teal;
}

/* Header links/icons */
.header a,
.header i {
  color: #ffff !important;
  transition: color 0.3s ease;
}

.header a:hover,
.header i:hover {
  color: teal !important;
}

/* --- CONTAINER --- */
.container-wrapper {
  display: flex;
  flex-grow: 1;
  margin-left: 230px; /* sidebar width */
  margin-top: 70px;   /* header height */
  padding: 30px;
  transition: all 0.3s ease;
  background-color: #fdfaf6;
  min-height: calc(100vh - 70px);
  box-sizing: border-box;
}

/* --- SIDEBAR --- */
.sidebar {
  width: 230px;
  background-color: #f1b04c;
  border-right: 2px solid #eee;
  padding: 20px 0;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 2px 0 6px rgba(0,0,0,0.05);
  overflow: visible;
  border-radius: 0 12px 12px 0;
}

.brand {
  padding: 10px 20px;
}

.brand a {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.brand-logo {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.brand-name {
  font-size: 1.1rem;
  font-weight: 900;
  color: #ffff;
  letter-spacing: 0.6px;
  margin-left: 12px;
  text-transform: uppercase;
  display: flex;
  align-items: center; /* vertically center with logo */
}

/* --- NAV LINKS --- */
.nav-links {
  margin-top: 10px;
  flex-grow: 1;
  overflow: visible;
  padding-bottom: 20px;
}

.nav-links ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-links ul li {
  margin: 25px 0; /* increases space between each nav link */
}

.nav-links ul li a {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 12px 20px;
  color: #fff;
  text-decoration: none;
  font-weight: 900; /* bolder text */
  transition: all 0.3s ease;
  border-radius: 8px;
  position: relative;
}

.nav-links ul li a:hover {
  background-color: #e0f2f1;
  color: teal;
  transform: translateX(6px);
  box-shadow: 0 3px 8px rgba(0, 128, 128, 0.15);
}

.nav-links ul li a::before {
  content: "";
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 4px;
  background-color: teal;
  border-radius: 0 4px 4px 0;
  transform: scaleY(0);
  transition: transform 0.3s ease;
}

.nav-links ul li a:hover::before {
  transform: scaleY(1);
}

.nav-links i {
  font-size: 1.4rem;
  transition: color 0.3s ease, transform 0.3s ease;
}

.nav-links ul li a:hover i {
  color: teal;
  transform: scale(1.1);
}

/* --- LOGOUT BUTTON --- */
.logout-btn {
  background-color: #263238;
  color: #f8f9fa !important;
  padding: 8px 25px;
  border-radius: 10px;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.3s;
  font-weight: 600;
  margin: 0;
  position: sticky; /* sticks to bottom of sidebar */
  bottom: 20px;      /* distance from bottom */
  text-decoration: none; /* removes underline */
  z-index: 100;
}

.logout-btn:hover {
  background-color: #16a085;
  color: #212121;
  text-decoration: none;
}

/* --- DROPDOWN --- */
.dropdown-menu {
  border-radius: 10px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.dropdown-item:hover {
  background: rgba(0,150,136,0.15) !important;
  color: #004d40 !important;
}

/* --- RESPONSIVE --- */
@media (max-width: 992px) {
  .sidebar {
    left: -230px;
    transition: left 0.3s ease;
  }

  .sidebar.active {
    left: 0;
  }

  .header {
    left: 0;
  }

  .container-wrapper {
    margin-left: 0;
    margin-top: 70px;
  }
}

/* --- MAIN CONTENT --- */
.main-content {
  flex-grow: 1;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  padding: 40px;
  width: 100%;
  overflow-x: auto;
}
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
  </div>

    <!-- SIDEBAR -->
    <nav class="sidebar">

      <div class="brand">
        <a href="admin_dashboard.php">
          <img src="inc/logo1.jpg" alt="Logo" class="brand-logo">
          <span class="brand-name">Seventeas Diner</span>
        </a>
      </div>

      <div class="nav-links">
        <ul>
          <li>
            <a href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
          </li>

          <li>
            <a data-bs-toggle="collapse" href="#accountMenu"><i class="bi bi-person-circle"></i> Account</a>
            <div class="collapse" id="accountMenu">
              <ul class="nav flex-column ms-3">
                <li>
                  <a href="manage_staff.php"><i class="bi bi-people-fill"></i> Manage Staff</a>
                </li>
                <li>
                  <a href="customer_registered.php"><i class="bi bi-person-lines-fill"></i> Customer Registered</a>
                </li>
              </ul>
            </div>
          </li>

          <li>
            <a href="expenses.php"><i class="bi bi-calculator"></i> Expenses</a>
          </li>

          <li>
            <a data-bs-toggle="collapse" href="#productsMenu"><i class="bi bi-box-seam"></i> Products</a>
            <div class="collapse" id="productsMenu">
              <ul class="nav flex-column ms-3">
                <li>
                  <a href="categories.php"><i class="bi bi-tags-fill"></i> View Categories</a>
                </li>
                <li>
                  <a href="products.php"><i class="bi bi-basket-fill"></i> View Products</a>
                </li>
              </ul>
            </div>
          </li>
        </ul>
      </div>

      <a class="logout-btn w-100" href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </nav>


  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
