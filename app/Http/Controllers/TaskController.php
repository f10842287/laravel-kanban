<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class TaskController extends Controller
{
    public function index()
    {
        return Task::all();
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|in:todo,doing,review,done',
                'priority' => 'nullable|string|in:high,normal,low',
                'label' => 'nullable|string|in:feature,design,bug,testing',
                'due_date' => 'nullable|date',
            ]);
    
            $task = Task::create($validated);
            return response()->json($task, 201);
        } catch (Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:high,normal,low',
            'label' => 'nullable|string|in:feature,design,bug,testing',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);
        return response()->json($task);
    }



    public function destroy(Task $task)
    {
        $task->delete();
        return response()->noContent();
    }

    public function updateStatus(Task $task, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,doing,review,done',
        ]);

        $task->status = $validated['status'];
        $task->save();

        return response()->json($task);
    }

}


