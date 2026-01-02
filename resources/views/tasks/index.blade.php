<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Tasks') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- <button onclick="openCreate()"> Create Task </button> --}}
                <button type="button" onclick="openCreate()">Create Task</button>


                <table id="tasksTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="loader" style="display:none;"> Loading... </div>

    <div id="taskModal" style="display:none; position:fixed; top:20%; left:50%;
            transform:translateX(-50%); background:#fff;
            padding:20px; border:1px solid #ccc; z-index:9999;">
    <form id="taskForm" action="{{ route('tasks.store') }}" method="POST">
        @csrf
        <input type="text" name="title" placeholder="Title" required><br>

        <textarea name="description" placeholder="Description"></textarea><br>

        <select name="status">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
        </select><br>

        <select name="priority">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select><br>

        <input type="date" name="due_date"><br>

        <button type="submit">Save</button>
        <button type="button" onclick="closeModal()">Cancel</button>
    </form>
</div>
</x-app-layout>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function loadTasks()
    {
        $('#loader').show();
        $.get('api/tasks', function(res) {
            let rows = '';

            res.data.forEach(task => {
                rows += `
                    <tr>
                        <td>${task.title}</td>
                        <td>${task.priority}</td>

                        <td>
                            <select onchange="updateStatus(${task.id}, this.value)">
                                <option ${task.status=='pending'?'selected':''}>pending</option>
                                <option ${task.status=='in_progress'?'selected':''}>in_progress</option>
                                <option ${task.status=='completed'?'selected':''}>completed</option>
                            </select>
                        </td>
                        <td>${task.due_date ?? ''}</td>
                        <td>
                            <button onclick="deleteTask(${task.id})"}>Delete</button>
                        </td>
                    </tr>

                `;
            });

            $('#tasksTable tbody').html(rows);
            $('#loader').hide();
        });
    }

    function deleteTask(id)
    {
        if(!confirm('Delete tasks?')) return;

        $.ajax({
            url: `/api/tasks/${id}`,
            type: 'DELETE',
            succes:loadTasks
        });
    }



    function updateStatus(id, status)
    {

        $.ajax({
            url: `/api/tasks/${id}`,
            type: 'PUT',
            data: { status },
            succes:loadTasks
        });
    }

    function openCreate() {
        $('#taskForm')[0].reset();
        $('#taskModal').show();
    }

    function closeModal() {
        $('#taskModal').hide();
    }

    $(document).ready(loadTasks);
</script>
