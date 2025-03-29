<?php
// Include database connection
include('dbconnection.php');

// Start session to track logged-in user
session_start();

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Fetch patient details from the database
$user_id = $_SESSION['patient_id'];
$query = "SELECT * FROM patients WHERE pid = :user_id"; // Use patients table
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch doctor names from the doctor table (assuming doctor data is stored in a separate table)
$doctor_query = "SELECT did, name FROM doctors"; // Assuming there's a doctors table
$stmt = $conn->prepare($doctor_query);
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['deletebtn'])) {
    $query = "UPDATE appointments SET isdelete = 1 WHERE aid = :aid";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':aid', $_POST['aid'], PDO::PARAM_INT);
    if ($stmt->execute()) {
        $appointment_success = "Appointment successfully removed!";
    } else {
        $appointment_error = "Failed to remove appointment.";
    }
}

if (isset($_POST['logout'])) {
    // Destroy the session
    session_destroy();
    // Redirect to the login page
    header("Location: login.php");
    exit();
}

// Fetch all appointments for the user
$query = "SELECT * FROM appointments a JOIN patients p ON a.pid = p.pid WHERE p.pid = :user_id AND a.isdelete = 0 ORDER BY a.appointment_date ASC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        /* Appointment Form Grid */
        .appointment-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
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
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-badge.pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-badge.confirmed {
            background-color: #28a745;
            color: #fff;
        }

        .status-badge.completed {
            background-color: #007bff;
            color: #fff;
        }

        .status-badge.cancelled {
            background-color: #dc3545;
            color: #fff;
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

        /* Disabled Button Style */
        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h4 style="margin:30px auto">HealthCare Portal</h4>
        <a href="patientDashboard.php">Dashboard</a>
        <a href="appointmentPatient.php">Appointments</a>
        <a href="patientProfile.php">Profile</a>

        <!-- Logout Form -->
        <form action="" method="POST" style="display: inline;">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Hello, <?php echo ucfirst($user['name']) . ' '; ?>!</h2>
        </div>

        <!-- Display success message after adding appointment -->
        <?php if (isset($appointment_success)): ?>
            <div class="success-message"><?php echo $appointment_success; ?>
                <script>
                    setTimeout(function () {
                        window.location.href = 'appointmentPatient.php';
                    }, 1000);
                </script>
            </div>
        <?php endif; ?>

        <h3>Your Upcoming Appointments</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Doctor's Name</th>
                        <th>Appointment Date</th>
                        <th>Appointment Time</th>
                        <th>Status</th>
                        <th colspan="2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <?php
                            // Fetch doctor name for each appointment
                            $doctor_query = "SELECT name FROM doctors WHERE did = :did LIMIT 1";
                            $stmt = $conn->prepare($doctor_query);
                            $stmt->bindParam(':did', $appointment['did']);
                            $stmt->execute();
                            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <td class="text-center"><?php echo ucfirst($doctor['name']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td class="text-center">
                                <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                            <td class="text-center">
                                <span class="status-badge <?php echo strtolower($appointment['status']); ?>">
                                    <?php echo htmlspecialchars($appointment['status']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <form action="appointmentUpdateP.php" method="GET">
                                    <input hidden name="aid" value="<?php echo $appointment['aid']; ?>">
                                    <?php if (in_array($appointment['status'], ['cancelled', 'completed', 'confirmed'])): ?>
                                        <button type="submit" class="btn btn-secondary btn-disabled" disabled>Update</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    <?php endif; ?>
                                </form>

                            </td>
                            <td class="text-center">
                                <form action="" method="POST" onsubmit="return confirmDelete();">
                                    <input hidden name="aid" value="<?php echo $appointment['aid']; ?>">
                                    <?php if (in_array($appointment['status'], ['cancelled', 'completed','confirmed'])): ?>
                                        <button type="submit" name="deletebtn" class="btn btn-secondary btn-disabled"
                                            disabled>Delete</button>
                                    <?php else: ?>
                                        <button type="submit" name="deletebtn" class="btn btn-danger">Delete</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this appointment?");
        }
    </script>
</body>

</html>