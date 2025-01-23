<?php
require_once 'config/db_config.php';

// Fetch initial tasks from database
function getTasks() {
    global $conn;
    $tasks = array(
        'todo' => array(),
        'doing' => array(),
        'done' => array(),
        'trash' => array()
    );
    
    $sql = "SELECT * FROM tasks ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            if ($status === 'todo') {
                $tasks['todo'][] = $row;
            } else {
                $tasks[$status][] = $row;
            }
        }
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
    <link rel="stylesheet" href="./style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/favicon.svg" type="image/x-icon">
</head>
<body>
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
                    <h4>🌟 To Do</h4>
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
                    <h4>💫 Doing</h4>
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
                    <h4>🏆 Done</h4>
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
                    <h4>❌ Trash</h4>
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

    <footer>
        <p>Inspired By <a href="https://chromewebstore.google.com/detail/docket/kkkciickjhdffaionllgnndmecaenpej" target="_blank">"Uthman Khan"</a></p>
    </footer>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.js'></script>
    <script src="./script.js"></script>
</body>
</html>
