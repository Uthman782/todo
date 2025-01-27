<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db_config.php';

// Debug database connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die(json_encode(['error' => 'Database connection failed']));
}

error_log("Database connected successfully");

// Verify database and tables exist
$result = $conn->query("SELECT DATABASE()");
$row = $result->fetch_row();
error_log("Current database: " . $row[0]);

$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}
error_log("Tables in database: " . implode(", ", $tables));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

error_log("Request Method: " . $method);
error_log("User ID: " . $user_id);

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM tasks WHERE user_id = ? AND status != 'trash' ORDER BY created_at DESC";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $tasks = [];
            
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }
            error_log("Tasks fetched: " . count($tasks));
            echo json_encode($tasks);
            $stmt->close();
        } else {
            error_log("MySQL Error in GET: " . $conn->error);
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch tasks']);
        }
        break;

    case 'POST':
        $input = file_get_contents('php://input');
        error_log("Raw POST input: " . $input);
        
        $data = json_decode($input, true);
        error_log("Decoded POST data: " . print_r($data, true));
        
        if (!isset($data['title']) || trim($data['title']) === '') {
            error_log("Title is missing or empty");
            http_response_code(400);
            echo json_encode(['error' => 'Title is required']);
            exit;
        }

        $title = trim($data['title']);
        $status = isset($data['status']) ? trim($data['status']) : 'todo';

        error_log("Inserting task - Title: " . $title . ", Status: " . $status . ", User ID: " . $user_id);

        // First, verify the tasks table structure
        $result = $conn->query("DESCRIBE tasks");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'] . " (" . $row['Type'] . ")";
        }
        error_log("Tasks table structure: " . implode(", ", $columns));

        $sql = "INSERT INTO tasks (user_id, title, status) VALUES (?, ?, ?)";
        error_log("SQL Query: " . $sql);
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $user_id, $title, $status);
            if ($stmt->execute()) {
                $task_id = $conn->insert_id;
                error_log("Task inserted successfully with ID: " . $task_id);
                
                // Verify the inserted data
                $verify_sql = "SELECT * FROM tasks WHERE id = ?";
                $verify_stmt = $conn->prepare($verify_sql);
                $verify_stmt->bind_param("i", $task_id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $inserted_task = $verify_result->fetch_assoc();
                error_log("Verified inserted task: " . print_r($inserted_task, true));
                
                echo json_encode([
                    'id' => $task_id,
                    'message' => 'Task created successfully',
                    'task' => [
                        'id' => $task_id,
                        'title' => $title,
                        'status' => $status,
                        'user_id' => $user_id
                    ]
                ]);
            } else {
                error_log("MySQL Error in POST execute: " . $stmt->error);
                http_response_code(400);
                echo json_encode(['error' => 'Failed to create task']);
            }
            $stmt->close();
        } else {
            error_log("MySQL Error in POST prepare: " . $conn->error);
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare statement']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("PUT Data: " . print_r($data, true));
        
        if (!isset($data['id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID and status are required']);
            exit;
        }

        $id = (int)$data['id'];
        $status = trim($data['status']);

        $sql = "UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $status, $id, $user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['message' => 'Task updated successfully']);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Task not found or not owned by user']);
                }
            } else {
                error_log("MySQL Error in PUT execute: " . $stmt->error);
                http_response_code(400);
                echo json_encode(['error' => 'Failed to update task']);
            }
            $stmt->close();
        } else {
            error_log("MySQL Error in PUT prepare: " . $conn->error);
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare statement']);
        }
        break;

    case 'DELETE':
        $sql = "DELETE FROM tasks WHERE status = 'trash' AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['message' => 'Trash emptied successfully']);
                } else {
                    echo json_encode(['message' => 'No tasks in trash']);
                }
            } else {
                error_log("MySQL Error in DELETE execute: " . $stmt->error);
                http_response_code(400);
                echo json_encode(['error' => 'Failed to empty trash']);
            }
            $stmt->close();
        } else {
            error_log("MySQL Error in DELETE prepare: " . $conn->error);
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare statement']);
        }
        break;
}

$conn->close();
?>
