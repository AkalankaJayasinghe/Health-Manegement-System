<?php
// Include database connection
include('dbconnection.php');

// Start session to track logged-in user
session_start();

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Fetch user details from database
$user_id = $_SESSION['doctor_id'];
$query = "SELECT * FROM doctors WHERE did = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to login page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HealthCare Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* Reset default margin and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f8fb;
            display: flex;
            justify-content: flex-start;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #4CAF50;
            padding: 20px;
            color: white;
            height: 100vh;
            position: fixed;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            margin: 10px 0;
            border-radius: 5px;
        }

        .logout-btn {
            margin-top: 150%;
            background: none;
            border: 1px solid white;
            color: black;
            cursor: pointer;
            padding: 13px 80px;
            border-radius: 5px;
            font: inherit;
            text-align: start;
        }

        .logout-btn:hover {
            background-color: #45a049;
            color: inherit;
        }

        .sidebar a:hover {
            background-color: #45a049;
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }

        .header h2 {
            color: #333;
        }

        /* Profile Details */
        .profile-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .profile-details h3 {
            margin-bottom: 20px;
            color: #333;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 style="margin:30px auto">HealthCare Portal</h4>
        <a href="doctorDashboard.php">Dashboard</a>
        <a href="appointmentDoctor.php">Appointments</a>
        <a href="doctorProfile.php">Profile</a>

        <!-- Logout Form -->
        <form action="" method="POST" style="display: inline;">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Hello, <?php echo ucfirst($user['name']) . ' '; ?>!</h2>
        </div>

        <!-- Profile Details Section -->
        <div class="profile-details">
            <h3>Profile Details</h3>
            <div class="container">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Name</label>
                            <p class="form-control bg-light"><?php echo ucwords($user['name']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p class="form-control bg-light"><?php echo $user['email']; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Phone</label>
                            <p class="form-control bg-light"><?php echo $user['phone']; ?></p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Address</label>
                            <p class="form-control bg-light"><?php echo ucwords($user['address']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Date of Birth</label>
                            <p class="form-control bg-light"><?php echo $user['dob']; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Gender</label>
                            <p class="form-control bg-light"><?php echo ucwords($user['gender']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>