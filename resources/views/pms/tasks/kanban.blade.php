@extends('layouts/layoutMaster')

@section('title', 'KANBAN Task')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Drag & Drop
    document.querySelectorAll(".kanban-column").forEach(function (el) {
        new Sortable(el, {
    group: "kanban",
    animation: 150,
    filter: ".completed-task",   // don't allow drag on these
    onMove: function (evt) {
        // Prevent dropping into other columns if source task is completed
        if (evt.dragged.classList.contains("completed-task")) {
            return false;
        }
    },
    onAdd: function (evt) {
        let taskId = evt.item.dataset.id;
        let newStatus = evt.to.dataset.status;

        // prevent moving a completed task
        if (evt.item.classList.contains("completed-task") && newStatus !== "complete") {
            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]); // move it back
            return;
        }

        fetch(`/pms/projects/{{ $project->id }}/kanban/tasks/${taskId}/update-status`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert("Error updating status");
            }
        });
    }
});
    });

    // Filters
    function reloadKanban() {
        let milestoneId = document.getElementById("milestone-filter").value;
        let priority = document.getElementById("priority-filter").value;
        let due = document.getElementById("due-filter").value;

        let url = "{{ route('pms.projects.kanban.data', $project->id) }}";
        url += `?milestone_id=${milestoneId}&priority=${priority}&due=${due}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                document.querySelectorAll(".kanban-column").forEach(col => col.innerHTML = "");
                for (let status in data.tasks) {
                    data.tasks[status].forEach(task => {
                        document.querySelector(`[data-status=${status}]`).innerHTML += `
                            <div class="task card mb-2 p-2" data-id="${task.id}">
                                <strong>${task.name}</strong>
                                <div><small>Milestone: ${task.milestone?.title ?? '-'}</small></div>
                                <div><small>Priority: ${task.priority ?? '-'}</small></div>
                                <div>
                                    <small>
                                        Due: ${task.end_date ?? '-'}
                                        <span class="${task.end_date && new Date(task.end_date) < new Date() ? 'text-danger' : 'text-success'}">
                                            (${task.due_human})
                                        </span>
                                    </small>
                                </div>
                            </div>
                        `;
                    });
                }
            });
    }

    {{--  document.getElementById("milestone-filter").addEventListener("change", reloadKanban);
    document.getElementById("priority-filter").addEventListener("change", reloadKanban);
    document.getElementById("due-filter").addEventListener("change", reloadKanban);  --}}


     document.querySelectorAll(".task").forEach(task => {
        loadComments(task.dataset.id);
    });

    function loadComments(taskId) {
        fetch(`/pms/projects/{{ $project->id }}/tasks/${taskId}/comments`)
            .then(res => res.json())
            .then(data => {
                let list = document.getElementById(`comments-list-${taskId}`);
                list.innerHTML = "";
                data.forEach(c => {
                    list.innerHTML += renderComment(taskId, c);
                });
            });
    }

    function renderComment(taskId, c) {
    return `
        <li data-comment-id="${c.id}">
            <b>${c.user.name}</b>:
            <span class="comment-text">${c.comment}</span>
            <small class="text-muted"> • ${c.updated_at !== c.created_at ? 'edited ' + c.updated_at : c.created_at}</small>

            <span class="comment-edit d-none">
                <input type="text" class="form-control form-control-sm edit-input" value="${c.comment}">
                <button class="btn btn-success btn-sm btn-save">Save</button>
                <button class="btn btn-secondary btn-sm btn-cancel">Cancel</button>
            </span>
            {{--  <button class="btn btn-link btn-sm text-primary btn-edit">Edit</button>  --}}
            {{--  <button class="btn btn-link btn-sm text-danger btn-delete">Delete</button>  --}}
        </li>`;
}

    // --- ADD COMMENT ---
    document.querySelectorAll(".add-comment-form").forEach(form => {
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            let taskId = this.dataset.taskId;
            let input = this.querySelector("input[name=comment]");
            let comment = input.value;

            fetch(`/pms/projects/{{ $project->id }}/tasks/${taskId}/comments`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ comment })
            })
            .then(res => res.json())
            .then(data => {
                if (data.id) {
                    document.getElementById(`comments-list-${taskId}`).innerHTML += renderComment(taskId, data);
                    input.value = "";
                }
            });
        });
    });

    // --- DELEGATED EVENTS (edit/delete/save) ---
    document.body.addEventListener("click", function(e) {
        let li = e.target.closest("li[data-comment-id]");
        if (!li) return;
        let taskId = li.closest(".task").dataset.id;
        let commentId = li.dataset.commentId;

        // Edit
        if (e.target.classList.contains("btn-edit")) {
            // disable adding new until edit done
            li.querySelector(".comment-text").classList.add("d-none");
            li.querySelector(".comment-edit").classList.remove("d-none");
            li.querySelector(".btn-edit").disabled = true;
        }

        // Cancel edit
        if (e.target.classList.contains("btn-cancel")) {
            li.querySelector(".comment-edit").classList.add("d-none");
            li.querySelector(".comment-text").classList.remove("d-none");
            li.querySelector(".btn-edit").disabled = false;
        }

        // Save edit
        if (e.target.classList.contains("btn-save")) {
            let newText = li.querySelector(".edit-input").value;
            fetch(`/pms/projects/{{ $project->id }}/tasks/${taskId}/comments/${commentId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ comment: newText })
            })
            .then(res => res.json())
            .then(data => {
                if (data.id) {
                    li.querySelector(".comment-text").innerText = data.comment;
                    li.querySelector(".comment-edit").classList.add("d-none");
                    li.querySelector(".comment-text").classList.remove("d-none");
                    li.querySelector(".btn-edit").disabled = false;
                }
            });
        }

        // Delete
        if (e.target.classList.contains("btn-delete")) {
            if (!confirm("Delete this comment?")) return;
            fetch(`/pms/projects/{{ $project->id }}/tasks/${taskId}/comments/${commentId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) li.remove();
            });
        }
    });


});
</script>
@endsection

@section('content')
<div class="container-fluid">
  <h4>{{ $project->title }} - Kanban</h4>

  <!-- Filters -->
  {{-- <div class="d-flex gap-2 mb-3">
    <select id="milestone-filter" class="form-select w-auto">
      <option value="">All Milestones</option>
      @foreach($milestones as $m)
      <option value="{{ $m->id }}">{{ $m->name }}</option>
      @endforeach
    </select>

    <select id="priority-filter" class="form-select w-auto">
      <option value="">All Priorities</option>
      @foreach($priorities as $p)
      <option value="{{ $p }}">{{ $p }}</option>
      @endforeach
    </select>

    <select id="due-filter" class="form-select w-auto">
      <option value="">All Due Dates</option>
      <option value="today">Due Today</option>
      <option value="week">This Week</option>
      <option value="overdue">Overdue</option>
    </select>
  </div> --}}

  <!-- Kanban Board -->
  <div class="row kanban-board">
    @foreach (['todo' => 'To Do', 'start' => 'In Progress', 'complete' => 'Completed'] as $status => $label)
    <div class="col-md-4">
      <div class="card">
        <div class="card-header"><span class="badge bg-label-dark p-2">{{ $label }}</span></div>
        <div class="card-body kanban-column" data-status="{{ $status }}">
          @foreach ($tasks[$status] ?? [] as $task)
          <div class="task card mb-2 p-2 {{ $status == 'complete' ? 'completed-task' : '' }}" data-id="{{ $task->id }}">
            <strong>{{ $task->name }}</strong>
            <div><small>Milestone: {{ $task->milestone->name ?? '-' }}</small></div>
            <div><small>Priority: <span class="badge bg-{{ $task->priority_badge_color }}">
                  {{ $task->priority_name }}</span></small></div>
            <div>
              <small>
                Due: {{ $task->end_date ? $task->end_date->format('d M Y H:i') : '-' }}
                <span class="{{ $task->end_date && $task->end_date->isPast() ? 'text-danger' : 'text-success' }}">
                  ({{ $task->end_date ? $task->end_date->diffForHumans() : 'No Due Date' }})
                </span>
              </small>
            </div>

            <div class="time-tracking mt-2">
              @if($task->started_at)
              <div><small>Started: {{ $task->started_at->format('d-M-Y H:i') }}</small></div>
              @endif
              @if($task->completed_at)
              <div><small>Completed: {{ $task->completed_at->format('d M Y H:i') }}</small></div>
              <div><small>Total Time: {{ $task->total_minutes }} minutes</small></div>
              @endif
            </div>


            <!-- Comments -->
            <div class="comments mt-2">
              <ul class="comments-list list-unstyled" id="comments-list-{{ $task->id }}">
                {{-- AJAX loaded --}}
              </ul>
              <form class="add-comment-form mt-2" data-task-id="{{ $task->id }}">
                @csrf
                <div class="input-group input-group-sm">
                  <input type="text" name="comment" class="form-control" placeholder="Add comment...">
                  <button class="btn btn-primary btn-sm">Add</button>
                </div>
              </form>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endforeach
  </div>


  @endsection