<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
Use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = auth()->user()
            ->tasks()
            ->latest()
            ->paginate(10);

        return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task = auth()->user()->tasks()->create($data);

        return back();
    }

    public function show(Task $task)
    {
        dd("show");
        $this->authorizeTask($task);
        return new TaskResource($task);
    }

    public function update(Request $request, Task $task)
    {
        dd("update");
        $this->authorizeTask($task);

        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $tasks->update($data);
        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        dd("destroy");
        $this->authorizeTask($task);
        $this->delete();
        
        return response()->json([
            'message' => 'Deleted'
        ]);
    }

    public function authorizeTask(Task $task)
    {
        abort_if($task->user_id !== auth()->id(), 403);
    }
}
