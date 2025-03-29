<?php
// Include database connection
include('dbconnection.php');

// Start session to track logged-in user
session_start();

// Check if user is logged in and is a doctor
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Fetch doctor details from the database (assuming users table)
$doctor_id = $_SESSION['doctor_id'];
$query = "SELECT * FROM doctors WHERE did = :doctor_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch appointments for the doctor
$query = "SELECT a.aid, a.pid, a.appointment_date, a.appointment_time, a.status, p.name AS patient_name 
          FROM appointments a
          JOIN patients p ON a.pid = p.pid
          JOIN doctors d ON a.did = d.did
          WHERE a.did = :doctor_id AND a.isdelete = 0
          ORDER BY a.appointment_date ASC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update appointment status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_SANITIZE_NUMBER_INT);
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    if ($appointment_id && $new_status) {
        // Update the status in the database
        $update_query = "UPDATE appointments SET status = :status WHERE aid = :appointment_id";
        $stmt = $conn->prepare($update_query);
        $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
        $stmt->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);
        $stmt->execute();

        // Set a success message
        $status_message = "Appointment status updated to $new_status successfully!";
    }
}

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
        /* Existing CSS styles remain unchanged */
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
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Status Badges */
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

        /* Form Controls */
        .form-control {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 50%;
        }

        .btn-primary {
            background-color: #4CAF50;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        .btn-primary:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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

        <!-- Success Message -->
        <?php if (!empty($status_message)): ?>
            <div class="success-message"><?php echo $status_message; ?></div>
            <script>
                setTimeout(function () {
                    window.location.href = 'appointmentDoctor.php';
                }, 1000);
            </script>
        <?php endif; ?>

        <!-- Appointments Table -->
        <h3 style="padding: 30px 20px;">Upcoming Appointments</h3>
        <div class="table-wrapper">
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
                            <?php
                            $queryn = "SELECT * FROM patients WHERE pid = '{$appointment['pid']}'";
                            $stmtn = $conn->prepare($queryn);
                            $stmtn->execute();
                            $datan = $stmtn->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <td><?php echo ucwords($datan['name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($appointment['status']); ?>">
                                    <?php echo htmlspecialchars($appointment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (in_array($appointment['status'], ['completed', 'cancelled', 'confirmed'])): ?>
                                    <!-- Disable form if status is Completed or Cancelled or Confirmed -->
                                    <form method="POST" action="" style="display: flex; justify-content:space-around;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['aid']; ?>">
                                        <select name="status" class="form-control" disabled>
                                            <option value="Pending" <?php echo ($appointment['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Confirmed" <?php echo ($appointment['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="Completed" <?php echo ($appointment['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo ($appointment['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary"
                                            disabled>Update</button>
                                    </form>
                                <?php else: ?>
                                    <!-- Enable form for other statuses -->
                                    <form method="POST" action="" style="display: flex; justify-content:space-around;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['aid']; ?>">
                                        <select name="status" class="form-control">
                                            <option value="Pending" <?php echo ($appointment['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Confirmed" <?php echo ($appointment['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="Completed" <?php echo ($appointment['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo ($appointment['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary"
                                            onclick="return confirmUpdate()">Update</button>
                                    </form>
                                <?php endif; ?>
                            </td>

                            <script>
                                function confirmUpdate() {
                                    return confirm('Are you sure you want to update the status of this appointment?');
                                }
                            </script>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>