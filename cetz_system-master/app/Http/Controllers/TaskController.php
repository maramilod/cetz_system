<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // عرض كل المهام
    public function index()
    {
        $tasks = Task::with('creator')->orderBy('created_at', 'desc')->get();
        return response()->json($tasks);
    }

    // إضافة مهمة جديدة
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => Auth::id(), // المستخدم الحالي
        ]);

        return response()->json($task, 201);
    }

    // حذف مهمة
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'تم حذف المهمة']);
    }
}
