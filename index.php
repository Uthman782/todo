<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

require_once 'config/db_config.php';

// Fetch initial tasks from database
function getTasks() {
    global $conn;
    $user_id = $_SESSION['user_id'];
    $tasks = array(
        'todo' => array(),
        'doing' => array(),
        'done' => array(),
        'trash' => array()
    );
    
    $sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            $tasks[$status][] = $row;
        }
        $stmt->close();
    }
    return $tasks;
}

$allTasks = getTasks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily ToDo</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/style1.css">
    <style>
        .user-menu {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .logout-btn {
            background-color: #ff4444;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            border: none;
        }
        .logout-btn:hover {
            background-color: #fa7070;
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="user-menu">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <header>
        <h1>Daily ToDo</h1>
    </header>
    
    <div class="add-task-container">
        <input type="text" maxlength="12" id="taskText" placeholder="New Activity..." onkeydown="if (event.keyCode == 13) document.getElementById('add').click()">
        <button id="add" class="button add-button" onclick="addTask()">Add</button>
    </div>

    <div class="main-container">
        <ul class="columns">
            <li class="column to-do-column">
                <div class="column-header">
                    <h4> To Do</h4>
                </div>
                <ul class="task-list" id="to-do">
                    <?php foreach ($allTasks['todo'] as $task): ?>
                    <li class="task" data-id="<?php echo htmlspecialchars($task['id']); ?>">
                        <p><?php echo htmlspecialchars($task['title']); ?></p>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <li class="column doing-column">
                <div class="column-header">
                    <h4> Doing</h4>
                </div>
                <ul class="task-list" id="doing">
                    <?php foreach ($allTasks['doing'] as $task): ?>
                    <li class="task" data-id="<?php echo htmlspecialchars($task['id']); ?>">
                        <p><?php echo htmlspecialchars($task['title']); ?></p>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <li class="column done-column">
                <div class="column-header">
                    <h4> Done</h4>
                </div>
                <ul class="task-list" id="done">
                    <?php foreach ($allTasks['done'] as $task): ?>
                    <li class="task" data-id="<?php echo htmlspecialchars($task['id']); ?>">
                        <p><?php echo htmlspecialchars($task['title']); ?></p>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <li class="column trash-column">
                <div class="column-header">
                    <h4> Trash</h4>
                </div>
                <ul class="task-list" id="trash">
                    <?php foreach ($allTasks['trash'] as $task): ?>
                    <li class="task" data-id="<?php echo htmlspecialchars($task['id']); ?>">
                        <p><?php echo htmlspecialchars($task['title']); ?></p>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="column-button">
                    <button class="button delete-button" onclick="emptyTrash()">Delete</button>
                </div>
            </li>
        </ul>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>Daily ToDo</h3>
                    <p>Organize your tasks, boost your productivity</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="settings.php">Settings</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
                <div class="footer-social">
                    <h4>Connect With Us</h4>
                    <div class="social-icons">
                        <a href="https://facebook.com" target="_blank" class="social-icon facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com" target="_blank" class="social-icon twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" class="social-icon linkedin">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="social-icon instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Daily ToDo. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.js'></script>
    <script src="assets/script.js"></script>
</body>
</html>
