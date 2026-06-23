<!-- Action controls -->
<div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
    <button class="btn btn-primary" data-open-modal="add-task-modal"><i class="fa-solid fa-circle-plus"></i> Create Task</button>
</div>

<!-- Kanban Grid -->
<div class="kanban-board">
    <!-- Todo Column -->
    <div class="glass-panel kanban-column" data-status="todo">
        <div class="kanban-header">
            <h4 style="font-weight: 700; font-size: 15px;"><i class="fa-regular fa-clipboard" style="color: var(--info); margin-right: 6px;"></i> To Do</h4>
            <span style="font-size: 11px; background: var(--border-glass); padding: 2px 8px; border-radius: 10px; font-weight: 600;"><?php echo count($todoTasks); ?></span>
        </div>
        <div class="kanban-cards-wrapper" style="min-height: 400px; display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($todoTasks as $task): ?>
                <div class="glass-panel kanban-card" draggable="true" data-task-id="<?php echo $task->id; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <span style="font-size: 10px; font-weight: 700; color: <?php echo $task->priority === 'high' ? 'var(--danger)' : ($task->priority === 'medium' ? 'var(--warning)' : 'var(--success)'); ?>; text-transform: uppercase;">
                            <?php echo htmlspecialchars($task->priority); ?>
                        </span>
                        <?php if ($isAdmin): ?>
                            <button class="btn-icon text-danger" style="padding: 4px; font-size: 11px; border: none; background: transparent;" onclick="deleteTask(<?php echo $task->id; ?>)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; margin-top: 8px;"><?php echo htmlspecialchars($task->title); ?></div>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 6px;"><?php echo htmlspecialchars($task->description ?: 'No notes provided.'); ?></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border-glass); font-size: 11px;">
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-circle-user"></i> <?php echo htmlspecialchars($task->assigned_name); ?></span>
                        <span style="color: var(--accent); font-weight: 600;"><i class="fa-regular fa-clock"></i> <?php echo $task->due_date ? date('M j', strtotime($task->due_date)) : 'No date'; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- In Progress Column -->
    <div class="glass-panel kanban-column" data-status="in_progress">
        <div class="kanban-header">
            <h4 style="font-weight: 700; font-size: 15px;"><i class="fa-solid fa-arrows-spin" style="color: var(--warning); margin-right: 6px;"></i> In Progress</h4>
            <span style="font-size: 11px; background: var(--border-glass); padding: 2px 8px; border-radius: 10px; font-weight: 600;"><?php echo count($progressTasks); ?></span>
        </div>
        <div class="kanban-cards-wrapper" style="min-height: 400px; display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($progressTasks as $task): ?>
                <div class="glass-panel kanban-card" draggable="true" data-task-id="<?php echo $task->id; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <span style="font-size: 10px; font-weight: 700; color: <?php echo $task->priority === 'high' ? 'var(--danger)' : ($task->priority === 'medium' ? 'var(--warning)' : 'var(--success)'); ?>; text-transform: uppercase;">
                            <?php echo htmlspecialchars($task->priority); ?>
                        </span>
                        <?php if ($isAdmin): ?>
                            <button class="btn-icon text-danger" style="padding: 4px; font-size: 11px; border: none; background: transparent;" onclick="deleteTask(<?php echo $task->id; ?>)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; margin-top: 8px;"><?php echo htmlspecialchars($task->title); ?></div>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 6px;"><?php echo htmlspecialchars($task->description ?: 'No notes.'); ?></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border-glass); font-size: 11px;">
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-circle-user"></i> <?php echo htmlspecialchars($task->assigned_name); ?></span>
                        <span style="color: var(--accent); font-weight: 600;"><i class="fa-regular fa-clock"></i> <?php echo $task->due_date ? date('M j', strtotime($task->due_date)) : 'No date'; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Completed Column -->
    <div class="glass-panel kanban-column" data-status="completed">
        <div class="kanban-header">
            <h4 style="font-weight: 700; font-size: 15px;"><i class="fa-regular fa-circle-check" style="color: var(--success); margin-right: 6px;"></i> Completed</h4>
            <span style="font-size: 11px; background: var(--border-glass); padding: 2px 8px; border-radius: 10px; font-weight: 600;"><?php echo count($completedTasks); ?></span>
        </div>
        <div class="kanban-cards-wrapper" style="min-height: 400px; display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($completedTasks as $task): ?>
                <div class="glass-panel kanban-card" draggable="true" data-task-id="<?php echo $task->id; ?>" style="opacity: 0.75;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <span style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                            Completed
                        </span>
                        <?php if ($isAdmin): ?>
                            <button class="btn-icon text-danger" style="padding: 4px; font-size: 11px; border: none; background: transparent;" onclick="deleteTask(<?php echo $task->id; ?>)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; margin-top: 8px; text-decoration: line-through; color: var(--text-secondary);"><?php echo htmlspecialchars($task->title); ?></div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;"><?php echo htmlspecialchars($task->description ?: 'No notes.'); ?></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border-glass); font-size: 11px;">
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-circle-user"></i> <?php echo htmlspecialchars($task->assigned_name); ?></span>
                        <span style="color: var(--success); font-weight: 600;"><i class="fa-solid fa-check"></i> Done</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ==========================================
   MODAL: Create Task
   ========================================== -->
<div id="add-task-modal" class="modal-overlay">
    <div class="glass-panel modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Create Work Task</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="add-task-form">
            <div class="form-group">
                <label for="task_title">Task Title *</label>
                <input type="text" id="task_title" name="title" class="form-control" placeholder="e.g. Follow up on proposal details" required>
            </div>

            <div class="form-group">
                <label for="task_desc">Task Description</label>
                <textarea id="task_desc" name="description" class="form-control" rows="3" placeholder="Include task specs or agenda goals..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="task_priority">Priority</label>
                    <select id="task_priority" name="priority" class="form-control">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="task_due">Due Date</label>
                    <input type="date" id="task_due" name="due_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="task_assign">Assign Member *</label>
                <select id="task_assign" name="assigned_to" class="form-control" required>
                    <option value="">Choose Staff User</option>
                    <?php foreach ($usersList as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($u['id'] == $userId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Drag & Drop
    makeTasksDraggable((taskId, targetStatus) => {
        // Send AJAX request to update task status
        sendAJAX('api/tasks.php', {
            action: 'update_status',
            id: taskId,
            status: targetStatus
        }, (res) => {
            if (res.success) {
                showToast('Task Updated', `Task status changed to ${targetStatus.replace('_', ' ')}`, 'success');
                setTimeout(() => { window.location.reload(); }, 1200);
            } else {
                showToast('Error', res.message || 'Could not move task', 'danger');
                setTimeout(() => { window.location.reload(); }, 1000);
            }
        });
    });

    // 2. Submit Task Creation form
    const taskForm = document.getElementById('add-task-form');
    if (taskForm) {
        taskForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(taskForm);
            const payload = { action: 'create' };
            formData.forEach((val, key) => { payload[key] = val; });

            sendAJAX('api/tasks.php', payload, (res) => {
                if (res.success) {
                    showToast('Success', 'New task assigned successfully!', 'success');
                    document.getElementById('add-task-modal').classList.remove('active');
                    taskForm.reset();
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    showToast('Failed', res.message || 'Could not create task', 'danger');
                }
            });
        });
    }
});

// 3. Delete Task (Admin Only)
function deleteTask(id) {
    showConfirm(
        'Delete Work Task',
        'Are you sure you want to delete this task?',
        () => {
            sendAJAX('api/tasks.php', { action: 'delete', id: id }, (res) => {
                if (res.success) {
                    showToast('Deleted', 'Task has been removed', 'success');
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    showToast('Failed', res.message || 'Action restricted', 'danger');
                }
            });
        },
        null,
        {
            isDanger: true,
            okText: 'Delete',
            iconHtml: '<i class="fa-solid fa-trash-can"></i>',
            iconColor: 'var(--danger)'
        }
    );
}
</script>
