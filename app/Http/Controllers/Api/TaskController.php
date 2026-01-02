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
        // dd("show");
        $this->authorizeTask($task);
        return new TaskResource($task);
    }

    public function update(Request $request, Task $task)
    {
        // dd("update");
        $this->authorizeTask($task);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
            'priority' => 'sometimes|required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        if($task->user_id === auth()->user()->id)
        {
            $task->update($data);
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => new TaskResource($task),
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete task'
        ], 500);

    }

    public function destroy(Task $task)
    {
        // dd("destroy");
        $this->authorizeTask($task);

        if($task->user_id === auth()->user()->id)
        {
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete task'
        ], 500);
    }

    public function authorizeTask(Task $task)
    {
        abort_if(
            ! auth()->check() || $task->user_id !== auth()->user()->id,
            403
        );
    }
}
