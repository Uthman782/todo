<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM tasks WHERE status != 'trash' ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $tasks = [];
        
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        echo json_encode($tasks);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $conn->real_escape_string($data['title']);
        $status = $conn->real_escape_string($data['status'] ?? 'todo');

        $sql = "INSERT INTO tasks (title, status) VALUES ('$title', '$status')";
        if ($conn->query($sql)) {
            echo json_encode(['id' => $conn->insert_id, 'message' => 'Task created successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to create task']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $conn->real_escape_string($data['id']);
        $status = $conn->real_escape_string($data['status']);

        $sql = "UPDATE tasks SET status = '$status' WHERE id = $id";
        if ($conn->query($sql)) {
            echo json_encode(['message' => 'Task updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to update task']);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $sql = "DELETE FROM tasks WHERE status = 'trash'";
        if ($conn->query($sql)) {
            echo json_encode(['message' => 'Trash emptied successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to empty trash']);
        }
        break;
}

$conn->close();
?>
