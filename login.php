<?php

include('dbconnection.php');

// Initialize error message variable
$error_message = "";

// Check if the form is submitted
if (isset($_POST['login'])) {
    // Sanitize and validate form data
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $user_type = htmlspecialchars($_POST['user_type']); // Get user type
    $remember = isset($_POST['remember']) ? true : false;

    // Validate input
    if (empty($email) || empty($password) || empty($user_type)) {
        $error_message = "<strong>Error!</strong> Please fill in all fields including user type.";
    } else {
        // Determine the table based on user type
        $table = ($user_type === 'patient') ? 'patients' : 'doctors';

        // Prepare SQL query to fetch user data based on email from the appropriate table
        $sql = "SELECT * FROM $table WHERE email = :email";

        try {
            // Prepare the statement
            $stmt = $conn->prepare($sql);
            // Bind parameters to the prepared statement
            $stmt->bindParam(':email', $email);

            // Execute the query
            $stmt->execute();

            // Check if user exists
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verify the password
                if (password_verify($password, $user['password'])) {
                    // Start session and store user info in session variables
                    echo 'ok';
                    session_start();

                    // Set session based on user type
                    if ($user_type === 'patient') {
                        $_SESSION['patient_id'] = $user['pid'];
                        // If 'remember me' is checked, set a cookie (optional)
                        if ($remember) {
                            setcookie('user_id', $user['pid'], time() + (60 * 60 * 24 * 30), '/'); // 30 days
                        }
                        header('Location: patientDashboard.php');
                    } elseif ($user_type === 'doctor') {
                        $_SESSION['doctor_id'] = $user['did'];
                        // If 'remember me' is checked, set a cookie (optional)
                        if ($remember) {
                            setcookie('user_id', $user['did'], time() + (60 * 60 * 24 * 30), '/'); // 30 days
                        }
                        header('Location: doctorDashboard.php');
                    }

                    // If 'remember me' is checked, set a cookie (optional)
                    if ($remember) {
                        setcookie('user_id', $user['pid'], time() + (60 * 60 * 24 * 30), '/'); // 30 days
                    }

                    exit;
                } else {
                    $error_message = "<strong>Error!</strong> Incorrect password. Please try again.";
                }
            } else {
                $error_message = "<strong>Error!</strong> No user found with this email for the selected user type.";
            }
        } catch (PDOException $e) {
            // Handle any errors
            $error_message = "<strong>Error!</strong> There was an issue with the login. Please try again later.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HealthCare Portal</title>
    <style>
        /* Reset default margin and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f8fb;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }

        .login-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .card-header {
            color: black;
            font-size: 40px;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
        }

        h4 {
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 1.1em;
            margin-bottom: 8px;
            display: block;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group-icon {
            background-color: #f0f0f0;
            padding: 10px;
            border-right: 1px solid #ddd;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-left: 10px;
        }

        .form-check {

            margin: 20px 130px;
            display: flex;
        }

        .form-check-label {
            font-size: 1em;
        }

        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 12px;
            font-size: 1.1em;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            width: 100%;
            border: none;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }

        .btn-link {
            background: none;
            color: #0073e6;
            text-decoration: none;
            text-align: center;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .error-message {
            background-color: #f44336;
            color: white;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            text-align: center;
        }

        @media screen and (max-width: 600px) {
            .login-card {
                padding: 20px;
            }

            .form-control {
                padding: 8px;
            }

            .btn {
                font-size: 1em;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <h5>Login</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="">
                    <!-- Display Error Messages -->
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message">
                            <?php echo $error_message; ?>
                        </div><br>
                    <?php endif; ?>
                    <!-- Email Input -->
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input id="email" type="email" class="form-control" name="email"
                                placeholder="Enter your email">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input id="password" type="password" class="form-control" name="password"
                                placeholder="Enter your password">
                        </div>
                    </div>

                    <!-- User Type Dropdown -->
                    <div class="form-group">
                        <label for="user_type">User Type</label>
                        <select id="user_type" name="user_type" class="form-control">
                            <option value="doctor">Doctor</option>
                            <option value="patient">Patient</option>
                        </select>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember" style="margin: 3px 10px;">Remember Me</label>
                    </div>

                    <!-- Buttons -->
                    <div class="form-actions">
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                        <a class="btn btn-link" href="register.php">You don't have account</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>

</html>