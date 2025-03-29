<?php
// Include the database connection file
include('dbconnection.php');

// Initialize an empty message variable
$message = "";

// Check if the form is submitted
if (isset($_POST['register'])) {
    // Sanitize and validate form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $password_confirm = htmlspecialchars($_POST['password_confirmation']);
    $user_type = htmlspecialchars($_POST['user_type']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $dob = htmlspecialchars($_POST['dob']);
    $gender = htmlspecialchars($_POST['gender']);

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($password_confirm) || empty($user_type) || empty($phone) || empty($address) || empty($dob) || empty($gender)) {
        $message = "<div style='background-color: #f44336; color: white; padding: 15px; margin: 20px auto; border-radius: 5px;'>
                        <strong>Error!</strong> All fields are required. Please fill in all the fields.
                    </div>";
    } elseif ($password !== $password_confirm) {
        $message = "<div style='background-color: #f44336; color: white; padding: 15px; margin: 20px auto; border-radius: 5px;'>
                        <strong>Error!</strong> Passwords do not match. Please make sure both passwords are identical.
                    </div>";
    } elseif (!in_array($user_type, ['patient', 'doctor'])) {
        $message = "<div style='background-color: #f44336; color: white; padding: 15px; margin: 20px auto; border-radius: 5px;'>
                        <strong>Error!</strong> Invalid user type selected.
                    </div>";
    } elseif ($dob > date('Y-m-d')) {
        $message = "<div style='background-color: #f44336; color: white; padding: 15px; margin: 20px auto; border-radius: 5px;'>
                    <strong>Error!</strong> Date of birth cannot be in the future.
                </div>";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $message = "<div style='background-color: #f44336; color: white; padding: 15px; margin: 20px auto; border-radius: 5px;'>
                    <strong>Error!</strong> Phone number must be exactly 10 digits.
                </div>";
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Determine the table based on user type
        $table = ($user_type === 'patient') ? 'patients' : 'doctors';

        // Prepare SQL query to insert user data into the appropriate table
        $sql = "INSERT INTO $table (name, email, password, phone, address, dob, gender) 
                VALUES (:name, :email, :password, :phone, :address, :dob, :gender)";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':gender', $gender);

            // Execute the query
            $stmt->execute();

            // Success message
            $message = "<div style='background-color: #4CAF50; color: white; padding: 15px; margin-top: 20px; border-radius: 5px;'>
                            <strong>Success!</strong> Registration successful for $user_type. Redirecting to login...
                        </div>";

            // Redirect to login after 2 seconds
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                  </script>";
        } catch (PDOException $e) {
            $message = "<div style='background-color: #f44336; color: white; padding: 15px; margin-top: 20px; border-radius: 5px;'>
                            <strong>Error!</strong> Registration failed. Please try again later.
                        </div>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HealthCare Portal</title>
    <style>
        {
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

        .register-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }

        .register-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .card-header {
            color: black;
            text-align: center;
            padding: 20px;
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

        .form-text {
            font-size: 0.9em;
            color: #777;
            margin-top: 5px;
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

        .btn:hover {
            opacity: 0.9;
        }

        @media screen and (max-width: 600px) {
            .register-card {
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
    <div class="register-container">
        <div class="register-card">
            <div class="card-header">
                <h1>Register</h1>
            </div>

            <div class="card-body">
                <form method="POST" action="">
                    <!-- Display success or error message below the form -->
                    <?php if (!empty($message)) {
                        echo $message;
                    } ?>
                    <!-- Name Field -->
                    <div class="form-group">
                        <label for="name">Name</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-person"></i>
                            </span>
                            <input id="name" type="text" class="form-control" name="name" placeholder="Enter your name">
                        </div>
                    </div>

                    <!-- Email Field -->
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

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input id="password" type="password" class="form-control" name="password"
                                placeholder="Enter a strong password">
                        </div>
                        <small class="form-text">Your password must be at least 8 characters long.</small>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group">
                        <label for="password-confirm">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-check-circle"></i>
                            </span>
                            <input id="password-confirm" type="password" class="form-control"
                                name="password_confirmation" placeholder="Re-enter your password">
                        </div>
                    </div>

                    <!-- User Type Field -->
                    <div class="form-group">
                        <label for="user_type">Register As</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <select id="user_type" class="form-control" name="user_type">
                                <option value="">Select User Type</option>
                                <option value="patient">Patient</option>
                                <option value="doctor">Doctor</option>
                            </select>
                        </div>
                    </div>

                    <!-- Phone Field -->
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-phone"></i>
                            </span>
                            <input id="phone" type="tel" class="form-control" name="phone"
                                placeholder="Enter your phone number">
                        </div>
                    </div>

                    <!-- Address Field -->
                    <div class="form-group">
                        <label for="address">Address</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-house"></i>
                            </span>
                            <input id="address" type="text" class="form-control" name="address"
                                placeholder="Enter your address">
                        </div>
                    </div>

                    <!-- Date of Birth Field -->
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-calendar"></i>
                            </span>
                            <input id="dob" type="date" class="form-control" name="dob">
                        </div>
                    </div>

                    <!-- Gender Field -->
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="bi bi-gender-ambiguous"></i> <!-- Use a valid gender icon -->
                            </span>
                            <select id="gender" class="form-control" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <div class="form-actions">
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                        <a class="btn btn-link" href="login.php">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>