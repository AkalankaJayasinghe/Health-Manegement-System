<?php
// Include database connection
include('dbconnection.php');

// Start session to track logged-in user
session_start();

// Check if user is logged in and is a patient
if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Fetch patient details from the patient table
$user_id = $_SESSION['patient_id'];
$query = "SELECT * FROM patients WHERE pid = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch doctor names from the doctor table
$doctor_query = "SELECT did, name FROM doctors";
$stmt = $conn->prepare($doctor_query);
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Insert new appointment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_appointment'])) {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Insert appointment into the database
    $insert_query = "INSERT INTO appointments (pid, did, appointment_date, appointment_time, status) 
                     VALUES (:user_id, :doctor_id, :appointment_date, :appointment_time, 'Pending')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->bindParam(':appointment_date', $appointment_date);
    $stmt->bindParam(':appointment_time', $appointment_time);
    $stmt->execute();
    $appointment_success = "Appointment successfully added!";
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fetch all appointments for the user
$query = "SELECT * FROM appointments WHERE pid = :user_id ORDER BY appointment_date ASC";
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
    <link rel="stylesheet" href="styles.css"> <!-- Link external CSS file for consistency -->
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

        .sidebar a:hover {
            background-color: #45a049;
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
            /* Adjust height as needed */
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
        <a href="patientDashboard.php">Dashboard</a>
        <a href="appointmentPatient.php">Appointments</a>
        <a href="patientProfile.php">Profile</a>
        <form action="" method="POST">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>
    <div class="main-content">
        <div class="header">
            <h2>Hello, <?php echo ucfirst($user['name']) . ' !'; ?></h2>
        </div>
        <h3 style="margin-bottom: 40px;">Make an Appointment</h3>

        <?php if (isset($appointment_success)): ?>
            <div class="success-message"><?php echo $appointment_success; ?></div>
        <?php endif; ?>

        <div style="display: flex; justify-content:space-around">
            <form method="POST" action="">
                <div class="appointment-form">
                    <div class="form-group">
                        <label for="doctor_name">Doctor's Name</label>
                        <select id="doctor_names" name="doctor_id" required>
                            <option value="">Select Doctor</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['did']; ?>"> <!-- Corrected 'did' here -->
                                    <?php echo ucfirst($doctor['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="appointment_date">Appointment Date</label>
                        <input type="date" id="appointment_date" name="appointment_date" required>
                    </div>
                    <div class="form-group">
                        <label for="appointment_time">Appointment Time</label>
                        <input type="time" id="appointment_time" name="appointment_time" required>
                        <span id="timeError" class="error-message" style="width: 200px;"></span>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_appointment" style="margin:20px 10px">Add Appointment</button>
                    </div>
                </div>
            </form>

            <img src="appointment.avif" width="40%" style="border-radius: 10px;" alt="">
        </div>
    </div>

    <script>
        // Ensure appointment date is at least today's date
        let today = new Date().toISOString().split('T')[0];
        document.getElementById("appointment_date").setAttribute("min", today);

        // Handle time validation and adjustment
        document.getElementById("appointment_time").addEventListener("change", function () {
            let errorSpan = document.getElementById("timeError");
            let now = new Date();
            let minutes = now.getMinutes();
            let roundedMinutes = Math.ceil(minutes / 30) * 30;
            if (roundedMinutes === 60) {
                now.setHours(now.getHours() + 1);
                roundedMinutes = 0;
            }
            now.setMinutes(roundedMinutes);

            let [inputHours, inputMinutes] = this.value.split(":").map(Number);
            let selectedTime = new Date();
            selectedTime.setHours(inputHours, inputMinutes, 0);

            // Prevent selecting past times
            if (selectedTime < now) {
                this.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
                errorSpan.textContent = `Adjusted to the next available time: ${this.value}`;
                return;
            }

            let adjustedMinutes = Math.round(inputMinutes / 30) * 30;
            if (adjustedMinutes === 60) {
                inputHours += 1;
                adjustedMinutes = 0;
            }

            this.value = `${String(inputHours).padStart(2, '0')}:${String(adjustedMinutes).padStart(2, '0')}`;
            errorSpan.textContent = ""; // Clear error message
        });

        // Set minimum selectable time dynamically
        function setMinSelectableTime() {
            let now = new Date();
            let minutes = now.getMinutes();
            let roundedMinutes = Math.ceil(minutes / 30) * 30;
            if (roundedMinutes === 60) {
                now.setHours(now.getHours() + 1);
                roundedMinutes = 0;
            }
            now.setMinutes(roundedMinutes);
            let formattedMinTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            document.getElementById("appointment_time").setAttribute("min", formattedMinTime);
        }

        setMinSelectableTime(); // Set min time on page load
    </script>
</body>

</html>