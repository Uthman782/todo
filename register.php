<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    error_log("Registration attempt for username: " . $username);
    
    // Validate username
    if (strlen($username) < 3) {
        error_log("Username too short: " . $username);
        $register_err = "Username must be at least 3 characters long.";
    } else {
        // Check if username exists
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                error_log("Username check query failed: " . $stmt->error);
                $register_err = "An error occurred. Please try again.";
            } else {
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    error_log("Username already exists: " . $username);
                    $register_err = "Username already taken.";
                }
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare username check statement: " . $conn->error);
            $register_err = "An error occurred. Please try again.";
        }
    }
    
    // Validate password
    if (strlen($password) < 6) {
        error_log("Password too short for user: " . $username);
        $register_err = "Password must be at least 6 characters long.";
    }
    
    // Validate confirm password
    if ($password != $confirm_password) {
        error_log("Passwords do not match for user: " . $username);
        $register_err = "Passwords do not match.";
    }
    
    // If no errors, proceed with registration
    if (empty($register_err)) {
        error_log("Attempting to create user: " . $username);
        
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ss", $username, $hashed_password);
            
            if ($stmt->execute()) {
                error_log("User created successfully: " . $username);
                header("location: login.php");
                exit;
            } else {
                error_log("Failed to create user: " . $stmt->error);
                $register_err = "Something went wrong. Please try again.";
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare user creation statement: " . $conn->error);
            $register_err = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Daily ToDo</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/favicon.svg" type="image/x-icon">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: #0e1726;
            border-radius: 10px;
            border: 1px solid #1b2e4b;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #1b2e4b;
            border-radius: 5px;
            background: #1b2e4b;
            color: #fff;
        }
        .error {
            color: #ff4444;
            margin-bottom: 1rem;
        }
        .auth-links {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Daily ToDo</h1>
    </header>
    
    <div class="auth-container">
        <h2 style="text-align: center; margin-bottom: 2rem;">Register</h2>
        
        <?php if (!empty($register_err)) echo "<div class='error'>$register_err</div>"; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required minlength="3">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            <div class="form-group">
                <button type="submit" class="button add-button" style="width: 100%">Register</button>
            </div>
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </form>
    </div>

    <footer>
        <p>Inspired By <a href="https://github.com/Uthman782" target="_blank">"Uthman Khan"</a></p>
    </footer>
</body>
</html>
