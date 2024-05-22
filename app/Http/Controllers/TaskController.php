<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tasks|max:255'
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $task = Task::create($request->all());

        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $task->completed = $request->completed;
        $task->save();

        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function showAll()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }
}
