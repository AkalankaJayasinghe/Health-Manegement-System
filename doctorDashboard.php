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

// Fetch doctor details from the database (assuming doctors table)
$doctor_id = $_SESSION['doctor_id'];
$query = "SELECT * FROM doctors WHERE did = :doctor_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - HealthCare Portal</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS file for better maintainability -->
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

        /* New Header Styling */
        .new-header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Content Wrapper for Left Text and Right Image */
        .content-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        /* Paragraph Styling */
        .intro-text {
            font-size: 20px;
            /* Increased font size for bigger paragraphs */
            color: #555;
            line-height: 1.6;
            flex: 1;
            /* Takes up available space on the left */
        }

        /* Photo Styling */
        .dashboard-photo {
            max-width: 50%;
            /* Limits image width to half the container */
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Appointment Form Grid */
        .appointment-form {
            display: block;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            width: 170%;
            margin: 10px;
        }

        .form-group label {
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .form-group button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #45a049;
        }

        /* Appointments Table Wrapper */
        .table-wrapper {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 30px;
        }

        /* Appointments Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
            position: sticky;
            top: 0;
            background: white;
            z-index: 2;
        }

        /* Success Message */
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h4 style="margin:30px auto">HealthCare Portal</h4>
        <a href="doctorDashboard.php">Dashboard</a>
        <a href="appointmentDoctor.php">Appointments</a>
        <a href="doctorProfile.php">Profile</a>
        <form action="" method="POST">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Welcome, Dr. <?php echo htmlspecialchars(ucfirst($doctor['name'])); ?>!</h2>
        </div>

        <!-- New Header -->
        <div class="new-header">
            <h3>Your Doctor Dashboard</h3>
        </div>

        <!-- Content Wrapper for Text and Image -->
        <div class="content-wrapper m-5">
            <!-- Paragraphs on the Left -->
            <div class="intro-text">
                <p style="font-size: 25px; text-align: justify; margin: 50px 100px;">
                    Welcome to your personalized dashboard, Dr.
                    <?php echo htmlspecialchars(ucfirst($doctor['name'])); ?>! As an esteemed physician within our
                    HealthCare Portal, this intuitive platform has been meticulously crafted to support your vital role
                    in delivering top-tier medical care. Here, you’re equipped with a comprehensive suite of tools
                    designed to simplify your day-to-day responsibilities: effortlessly manage your patient
                    appointments, update their statuses with a few clicks, and maintain an up-to-date professional
                    profile that reflects your expertise and dedication. Whether you’re scheduling consultations,
                    reviewing patient histories, or marking appointments as completed, this dashboard serves as your
                    command center, ensuring you remain organized amidst a demanding schedule. Our goal is to streamline
                    your workflow, freeing you to focus on what you do best—diagnosing, treating, and caring for your
                    patients with the compassion and precision that define your practice. Beyond the technical features,
                    this portal is a testament to our commitment to supporting you, offering seamless integration with
                    your clinical routines and instant access to critical information at your fingertips. Should you
                    ever encounter a challenge or need guidance on maximizing the platform’s capabilities, our
                    responsive support team stands ready to assist, ensuring you’re never alone in navigating this
                    digital space. Your work transforms lives, and we’re honored to provide a tool that enhances your
                    ability to do so every day!
                </p>
            </div>

            <!-- Photo on the Right -->
            <img src="doctor.webp" alt="Doctor Dashboard Photo" style="margin-right: 30px" class="dashboard-photo">
        </div>

        <!-- Success Message -->
        <?php if (!empty($status_message)): ?>
            <div class="success-message"> <?php echo htmlspecialchars($status_message); ?> </div>
        <?php endif; ?>

        <!-- Appointments Table (Commented Section Remains Unchanged) -->
        <!--
        <h3>Upcoming Appointments</h3>
        <table>
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Appointment Date</th>
                    <th>Appointment Time</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                        <td>
                            <form method="POST" action="doctor_dashboard.php">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                <select name="status" class="status-select" required>
                                    <option value="Pending" <?php echo ($appointment['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Completed" <?php echo ($appointment['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Cancelled" <?php echo ($appointment['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-update">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        -->
    </div>
</body>

</html>