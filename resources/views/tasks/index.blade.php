<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Tasks') }}
        </h2>
    </x-slot>

    <div id="alertBox" style="display:none; margin-bottom:10px;"></div>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <button
                    type="button"
                    onclick="openCreate()"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 ms-3" style="    background-color: cadetblue;">
                    ➕ Create Task
                </button>


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
    <form id="taskForm">
        @csrf
        <input type="hidden" id="task_id">
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
                        <tr 
                            data-id="${task.id}"
                            data-title="${task.title}"
                            data-description="${task.description ?? ''}"
                            data-status="${task.status}"
                            data-priority="${task.priority}"
                            data-due_date="${task.due_date ?? ''}"
                            >
                            <td>${task.title}</td>
                            <td>${task.priority}</td>
                            <td>
                                <select onchange="updateStatus(${task.id}, this.value)">
                                    <option value="pending" ${task.status=='pending'?'selected':''}>Pending</option>
                                    <option value="in_progress" ${task.status=='in_progress'?'selected':''}>In Progress</option>
                                    <option value="completed" ${task.status=='completed'?'selected':''}>Completed</option>
                                </select>
                            </td>
                            <td>${task.due_date ?? ''}</td>
                            <td>
                                <button onclick="editTask(${task.id})" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 ms-3">Edit</button>
                                <button onclick="deleteTask(${task.id})" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 ms-3" style="background-color: brown;">Delete</button>
                            </td>
                        </tr>`;
            });

            $('#tasksTable tbody').html(rows);
            $('#loader').hide();
        });
    }

    function deleteTask(id) {
        if (!confirm('Delete task?')) return;

        $.ajax({
            url: `/api/tasks/${id}`,
            type: 'DELETE',

            success: function (res) {
                showMessage('success', res.message);
                loadTasks();
            },

            error: function (xhr) {
                let message = 'Something went wrong';

                if (xhr.status === 403) {
                    message = 'You are not allowed to delete this task';
                } else if (xhr.status === 401) {
                    message = 'Please login again';
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                showMessage('error', message);
            }
        });
    }

    function updateStatus(id, status) {
        $.ajax({
            url: `/api/tasks/${id}`,
            type: 'PUT',
            data: { status },

            success: function (res) {
                showMessage('success', res.message ?? 'Status updated');
                loadTasks();
            },

            error: function (xhr) {
                let message = 'Failed to update status';

                if (xhr.status === 403) {
                    message = 'You are not allowed to update this task';
                } else if (xhr.status === 422) {
                    message = 'Invalid status value';
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                showMessage('error', message);
            }
        });
    }

    function editTask(id) {
        let row = $(`tr[data-id="${id}"]`);

        $('#task_id').val(id);
        $('[name="title"]').val(row.data('title'));
        $('[name="description"]').val(row.data('description'));
        $('[name="status"]').val(row.data('status'));
        $('[name="priority"]').val(row.data('priority'));
        $('[name="due_date"]').val(row.data('due_date'));

        $('#taskModal').show();
    }

    $('#taskForm').submit(function (e) {
    e.preventDefault();

    let id = $('#task_id').val();
    let url = id ? `/api/tasks/${id}` : `/api/tasks`;
    let method = id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        data: $(this).serialize(),

        success: function (res) {
            showMessage('success', res.message ?? 'Saved successfully');
            closeModal();
            loadTasks();
        },

        error: function (xhr) {
            let message = 'Failed to save task';

            if (xhr.status === 422) {
                message = 'Validation error';
            } else if (xhr.responseJSON?.message) {
                message = xhr.responseJSON.message;
            }

            showMessage('error', message);
        }
    });
});


    function openCreate() {
        $('#taskForm')[0].reset();
        $('#task_id').val('');
        $('#taskModal').show();
    }


    function closeModal() {
        $('#taskModal').hide();
    }

    function showMessage(type, message) {
        let color = type === 'success' ? '#16a34a' : '#dc2626';

        $('#alertBox')
            .html(message)
            .css({
                background: color,
                color: '#fff',
                padding: '10px',
                borderRadius: '5px'
            })
            .fadeIn();

        setTimeout(() => {
            $('#alertBox').fadeOut();
        }, 3000);
    }


    $(document).ready(loadTasks);
</script>
