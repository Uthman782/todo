/* Custom Dragula JS */
const drake = dragula([
    document.getElementById("to-do"),
    document.getElementById("doing"),
    document.getElementById("done"),
    document.getElementById("trash")
], {
    removeOnSpill: false
});

// Load tasks when page loads
document.addEventListener('DOMContentLoaded', loadTasks);

// Handle drag and drop events
drake.on('drop', function(el, target, source) {
    const taskId = el.getAttribute('data-id');
    const newStatus = target.id.replace('-', '');
    
    updateTaskStatus(taskId, newStatus);
});

// Load all tasks from the server
async function loadTasks() {
    try {
        console.log('Loading tasks...');
        const response = await fetch('api/tasks.php');
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
        }
        
        const tasks = await response.json();
        console.log('Loaded tasks:', tasks);
        
        // Clear all lists
        document.querySelectorAll('.task-list').forEach(list => list.innerHTML = '');
        
        // Populate lists
        tasks.forEach(task => {
            const listId = task.status === 'todo' ? 'to-do' : task.status;
            const taskElement = createTaskElement(task);
            const targetList = document.getElementById(listId);
            if (targetList) {
                targetList.appendChild(taskElement);
            }
        });
    } catch (error) {
        console.error('Error loading tasks:', error);
        showError('Failed to load tasks. Please refresh the page.');
    }
}

// Create a task element
function createTaskElement(task) {
    const li = document.createElement('li');
    li.className = 'task';
    li.setAttribute('data-id', task.id);
    li.innerHTML = `<p>${escapeHtml(task.title)}</p>`;
    return li;
}

// Escape HTML to prevent XSS
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Show error message
function showError(message) {
    console.error('Error:', message);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    document.body.appendChild(errorDiv);
    setTimeout(() => errorDiv.remove(), 3000);
}

// Add a new task
async function addTask() {
    const input = document.getElementById("taskText");
    const inputTask = input.value.trim();
    if (!inputTask) return;

    try {
        console.log('Adding task:', inputTask);
        const response = await fetch('api/tasks.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title: inputTask,
                status: 'todo'
            })
        });
        console.log('Add task response status:', response.status);

        const data = await response.json();
        console.log('Add task response:', data);

        if (!response.ok) {
            throw new Error(data.error || 'Failed to add task');
        }

        // Add the new task to the todo list immediately
        if (data.task) {
            const taskElement = createTaskElement(data.task);
            const todoList = document.getElementById('to-do');
            if (todoList) {
                todoList.insertBefore(taskElement, todoList.firstChild);
            }
        }

        input.value = "";
    } catch (error) {
        console.error('Error adding task:', error);
        showError('Failed to add task. Please try again.');
    }
}

// Update task status
async function updateTaskStatus(taskId, newStatus) {
    if (!taskId || !newStatus) {
        console.error('Invalid taskId or newStatus:', { taskId, newStatus });
        return;
    }

    try {
        console.log('Updating task:', { taskId, newStatus });
        const response = await fetch('api/tasks.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: taskId,
                status: newStatus
            })
        });
        console.log('Update task response status:', response.status);

        const data = await response.json();
        console.log('Update task response:', data);

        if (!response.ok) {
            throw new Error(data.error || 'Failed to update task');
        }
    } catch (error) {
        console.error('Error updating task:', error);
        showError('Failed to update task. Reloading...');
        loadTasks(); // Reload to original state
    }
}

// Empty trash
async function emptyTrash() {
    try {
        console.log('Emptying trash...');
        const response = await fetch('api/tasks.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        console.log('Empty trash response status:', response.status);

        const data = await response.json();
        console.log('Empty trash response:', data);

        if (!response.ok) {
            throw new Error(data.error || 'Failed to empty trash');
        }

        document.getElementById("trash").innerHTML = "";
    } catch (error) {
        console.error('Error emptying trash:', error);
        showError('Failed to empty trash. Please try again.');
    }
}