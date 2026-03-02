@extends('layouts.app')

@section('title', 'Project Gantt Chart')
@section('header', 'Project Gantt Chart: ' . $project->title)

@section('styles')
<link href='https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css' rel='stylesheet'>
<style>
    .gantt-container {
        width: 100%;
        height: 600px;
        overflow-x: auto;
        margin-bottom: 20px;
    }
    .gantt-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .task-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .task-info h5 {
        margin-bottom: 10px;
    }
    .task-details {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
    }
    .task-detail-item {
        margin-bottom: 5px;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gantt Chart: {{ $project->title }}</h5>
            <div>
                <a href="{{ route('pms.projects.show', $project->id) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Project
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="task-info" id="taskInfo" style="display: none;">
            <h5 id="taskTitle"></h5>
            <div class="task-details">
                <div class="task-detail-item">
                    <strong>Start Date:</strong> <span id="taskStartDate"></span>
                </div>
                <div class="task-detail-item">
                    <strong>End Date:</strong> <span id="taskEndDate"></span>
                </div>
                <div class="task-detail-item">
                    <strong>Duration:</strong> <span id="taskDuration"></span>
                </div>
                <div class="task-detail-item">
                    <strong>Assigned To:</strong> <span id="taskAssignedTo"></span>
                </div>
                <div class="task-detail-item">
                    <strong>Status:</strong> <span id="taskStatus"></span>
                </div>
                <div class="task-detail-item">
                    <strong>Priority:</strong> <span id="taskPriority"></span>
                </div>
            </div>
        </div>
        
        <div class="gantt-container">
            <svg id="gantt"></svg>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare tasks data for Gantt chart
    const tasks = [
        // Project as the main task
        {
            id: 'project-{{ $project->id }}',
            name: '{{ $project->title }}',
            start: '{{ $project->start_date->format("Y-m-d") }}',
            end: '{{ $project->end_date->format("Y-m-d") }}',
            progress: {{ $project->completion_percentage }},
            dependencies: '',
            custom_class: 'project-task'
        },
        
        // Milestones
        @foreach($project->milestones as $milestone)
        {
            id: 'milestone-{{ $milestone->id }}',
            name: '{{ $milestone->name }}',
            start: '{{ $milestone->start_date->format("Y-m-d") }}',
            end: '{{ $milestone->end_date->format("Y-m-d") }}',
            progress: {{ $milestone->task_completion_percentage }},
            dependencies: 'project-{{ $project->id }}',
            custom_class: 'milestone-task'
        },
        @endforeach
        
        // Tasks
        @foreach($project->milestones as $milestone)
            @foreach($milestone->tasks as $task)
            {
                id: 'task-{{ $task->id }}',
                name: '{{ $task->name }}',
                start: '{{ $task->start_date->format("Y-m-d") }}',
                end: '{{ $task->end_date->format("Y-m-d") }}',
                progress: {{ $task->status == \App\Models\PMS\Task::STATUS_COMPLETED ? 100 : ($task->status == \App\Models\PMS\Task::STATUS_IN_PROGRESS ? 50 : 0) }},
                dependencies: 'milestone-{{ $milestone->id }}',
                custom_class: 'task-item',
                task_data: {
                    title: '{{ $task->name }}',
                    start_date: '{{ $task->start_date->format("M d, Y") }}',
                    end_date: '{{ $task->end_date->format("M d, Y") }}',
                    duration: '{{ $task->start_date->diffInDays($task->end_date) + 1 }} days',
                    assigned_to: '{{ $task->assignments->map(function($a) { return $a->user->name; })->implode(", ") }}',
                    status: '{{ $task->status_name }}',
                    priority: '{{ $task->priority_name }}',
                    description: '{{ $task->description }}'
                }
            },
            @endforeach
        @endforeach
    ];

    // Initialize Gantt chart
    const gantt = new Gantt("#gantt", tasks, {
        header_height: 50,
        column_width: 30,
        step: 24,
        view_modes: ['Day', 'Week', 'Month'],
        bar_height: 20,
        bar_corner_radius: 3,
        arrow_curve: 5,
        padding: 18,
        view_mode: 'Month',
        date_format: 'YYYY-MM-DD',
        custom_popup_html: null,
        on_click: function (task) {
            if (task.task_data) {
                document.getElementById('taskInfo').style.display = 'block';
                document.getElementById('taskTitle').textContent = task.task_data.title;
                document.getElementById('taskStartDate').textContent = task.task_data.start_date;
                document.getElementById('taskEndDate').textContent = task.task_data.end_date;
                document.getElementById('taskDuration').textContent = task.task_data.duration;
                document.getElementById('taskAssignedTo').textContent = task.task_data.assigned_to || 'Not assigned';
                document.getElementById('taskStatus').textContent = task.task_data.status;
                document.getElementById('taskPriority').textContent = task.task_data.priority;
            } else {
                document.getElementById('taskInfo').style.display = 'none';
            }
        }
    });

    // Add custom styling
    document.querySelectorAll('.project-task').forEach(el => {
        el.style.fill = '#3b82f6';
    });

    document.querySelectorAll('.milestone-task').forEach(el => {
        el.style.fill = '#10b981';
    });

    document.querySelectorAll('.task-item').forEach(el => {
        el.style.fill = '#f59e0b';
    });
});
</script>
@endsection