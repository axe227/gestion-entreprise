<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        Task::where('user_id', $userId)
            ->where('due_date', '<', now())
            ->where('statut', '!=', 'completed')
            ->update(['statut' => 'overdue']);

        return response()->json(
            Task::where('user_id', $userId)->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required',
            'due_date' => 'required|date',
        ]);

        $task = Task::create([
            'user_id'     => auth()->id(), // ✅
            'title'       => $request->title,
            'description' => $request->description,
            'due_date'    => $request->due_date,
            'statut'      => $request->statut ?? 'pending',
        ]);

        return response()->json($task);
    }

    public function show($id)
    {
        return response()->json(
            Task::where('user_id', auth()->id())->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($id);
        $task->update($request->all());
        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function complete($id)
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($id);
        $task->update(['statut' => 'completed']);
        return response()->json($task);
    }

    public function alerts()
    {
        $userId = auth()->id();
        $today  = now();
        $soon   = now()->addDays(3);

        $overdue = Task::where('user_id', $userId)
            ->where('due_date', '<', $today)
            ->where('statut', '!=', 'completed')
            ->get();

        $upcoming = Task::where('user_id', $userId)
            ->whereBetween('due_date', [$today, $soon])
            ->where('statut', '!=', 'completed')
            ->get();

        return response()->json(['overdue' => $overdue, 'upcoming' => $upcoming]);
    }
}