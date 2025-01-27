<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    error_log("Login attempt for username: " . $username);
    
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            error_log("Login query execution failed: " . $stmt->error);
            $login_err = "An error occurred. Please try again.";
        } else {
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    error_log("Login successful for user: " . $username);
                    header("location: index.php");
                    exit;
                } else {
                    error_log("Invalid password for user: " . $username);
                    $login_err = "Invalid username or password.";
                }
            } else {
                error_log("No user found with username: " . $username);
                $login_err = "Invalid username or password.";
            }
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare login statement: " . $conn->error);
        $login_err = "An error occurred. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Daily ToDo</title>
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
        <h2 style="text-align: center; margin-bottom: 2rem;">Login</h2>
        
        <?php if (!empty($login_err)) echo "<div class='error'>$login_err</div>"; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="button add-button" style="width: 100%">Login</button>
            </div>
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </form>
    </div>

    <footer>
        <p>Inspired By <a href="https://github.com/Uthman782" target="_blank">"Uthman Khan"</a></p>
    </footer>
</body>
</html>
