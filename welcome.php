<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to HealthCare Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f8fb;  /* Light, calm blue */
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.welcome-container {
    text-align: center;
    background-color: #ffffff;  /* White background for clarity */
    padding: 40px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 100%;
}

h1 {
    font-size: 2.5em;
    color: #0073e6;  /* Trustworthy blue */
    margin-bottom: 20px;
}

p {
    font-size: 1.2em;
    color: #333;
    margin-bottom: 30px;
    line-height: 1.6;
}

.buttons-container .btn {
    display: inline-block;
    margin: 10px;
    padding: 15px 35px;
    background-color: #4CAF50;  /* Green for health */
    color: white;
    text-decoration: none;
    font-size: 1.2em;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    text-transform: uppercase;
}

.buttons-container .btn:hover {
    background-color: #45a049;  /* Slightly darker green on hover */
}

@media screen and (max-width: 600px) {
    .welcome-container {
        padding: 20px 15px;
    }

    h1 {
        font-size: 2em;
    }

    p {
        font-size: 1.1em;
    }

    .buttons-container .btn {
        padding: 12px 30px;
        font-size: 1em;
    }
}

</style>
<body>
    <div class="welcome-container">
        <h1>Welcome to HealthCare Portal</h1>
        <p>Your health, our priority. Please log in or register to get started.</p>
        <div class="buttons-container">
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn">Register</a>
        </div>
    </div>
</body>
</html>
