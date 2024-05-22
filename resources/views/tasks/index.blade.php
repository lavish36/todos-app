<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        h1, h2 {
            color: #333;
        }

        #taskForm {
            margin-bottom: 20px;
        }

        #taskInput {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: calc(100% - 100px);
        }

        #addTaskBtn {
            padding: 10px;
            border: none;
            background-color: #28a745;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            margin-left: 100px;
        }

        #addTaskBtn:hover {
            background-color: #218838;
        }

        #showAll {
            padding: 10px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        #showAll:hover {
            background-color: #0069d9;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            padding: 10px;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        li span {
            flex-grow: 1;
            margin-left: 10px;
        }

        li button {
            border: none;
            background-color: #dc3545;
            color: white;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
        }

        li button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div>
        <h1>To-dos Tasks</h1>

        <form id="taskForm">
            @csrf
            <input type="text" name="name" id="taskInput" placeholder="Enter task">
            <button type="submit" id="addTaskBtn">Add Task</button>
        </form>

        <button id="showAll">Show All Tasks</button>

        <h2>Incomplete Tasks</h2>
        <ul id="taskList"></ul>

        <h2>Completed Tasks</h2>
        <ul id="completedTaskList"></ul>
    </div>

    <script>
        async function fetchTasks() {
            try {
                const response = await fetch('/tasks/show/all', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                if (!response.ok) {
                    throw new Error('Some Error');
                }
                const data = await response.json();
                populateTaskLists(data);
            } catch (error) {
                console.error('Error In Fetch Task', error);
            }
        }

        function populateTaskLists(tasks) {
            let taskList = document.getElementById('taskList');
            let completedTaskList = document.getElementById('completedTaskList');
            taskList.innerHTML = '';
            completedTaskList.innerHTML = '';

            tasks.forEach(task => {
                let taskItem = document.createElement('li');
                taskItem.setAttribute('id', `task_${task.id}`);
                taskItem.innerHTML = `
                    <input type="checkbox" ${task.completed ? 'checked' : ''} 
                            onclick="updateTask(${task.id}, ${!task.completed})">
                    <span>${task.name}</span>
                    <button onclick="deleteTask(${task.id})">Delete</button>
                `;

                if (task.completed) {
                    completedTaskList.appendChild(taskItem);
                } else {
                    taskList.appendChild(taskItem);
                }
            });
        }

        async function updateTask(id, completed) {
            try {
                const response = await fetch(`/tasks/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ completed: completed })
                });
                if (!response.ok) {
                    throw new Error('Some Error');
                }
                const data = await response.json();
                if (data.message === 'Task updated successfully') {
                    let taskElement = document.getElementById(`task_${id}`);
                    taskElement.remove();

                    let newTaskElement = document.createElement('li');
                    newTaskElement.setAttribute('id', `task_${id}`);
                    newTaskElement.innerHTML = `
                        <input type="checkbox" ${completed ? 'checked' : ''} onclick="updateTask(${id}, ${!completed})">
                        <span>${data.task.name}</span>
                        <button onclick="deleteTask(${id})">Delete</button>
                    `;

                    if (completed) {
                        document.getElementById('completedTaskList').appendChild(newTaskElement);
                    } else {
                        document.getElementById('taskList').appendChild(newTaskElement);
                    }
                } else {
                    console.error('Failed to update task');
                }
            } catch (error) {
                console.error('Error In Update Task', error);
            }
        }

        async function deleteTask(id) {
            if (confirm('Are you sure to delete this task?')) {
                try {
                    const response = await fetch(`/tasks/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    if (!response.ok) {
                        throw new Error('Some Error');
                    }
                    document.getElementById(`task_${id}`).remove();
                } catch (error) {
                    console.error('Error In Delete Task', error);
                }
            }
        }

        document.getElementById('taskForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            let taskName = document.getElementById('taskInput').value.trim();
            if (taskName !== '') {
                try {
                    const response = await fetch('/tasks', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ name: taskName })
                    });
                    if (response.status == 400) {
                        const errorData = await response.json();
                        console.log(errorData);
                        if (errorData.name) {
                            alert(errorData.name);
                        }
                        throw new Error('Some Error');
                    }
                    const data = await response.json();
                    let taskList = document.getElementById('taskList');
                    let newTaskItem = document.createElement('li');
                    newTaskItem.setAttribute('id', `task_${data.id}`);
                    newTaskItem.innerHTML = `
                        <input type="checkbox" onclick="updateTask(${data.id}, true)">
                        <span>${data.name}</span>
                        <button onclick="deleteTask(${data.id})">Delete</button>
                    `;
                    taskList.appendChild(newTaskItem);
                    document.getElementById('taskInput').value = '';
                } catch (error) {
                    console.error('Error adding task:', error);
                }
            }
        });

        document.getElementById('showAll').addEventListener('click', function() {
            fetchTasks();
        });

        fetchTasks();
    </script>
</body>
</html>
