<!-- Calendar View Container -->
<div class="glass-panel" style="padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 13px; color: var(--text-secondary);">
            <i class="fa-solid fa-circle-info" style="color: var(--info); margin-right: 4px;"></i> Click on any calendar day block to schedule a call/followup.
        </div>
        <button class="btn btn-primary" data-open-modal="schedule-cal-modal"><i class="fa-solid fa-plus"></i> Schedule Followup</button>
    </div>
    
    <!-- FullCalendar Target -->
    <div id="calendar" style="min-height: 600px; color: var(--text-primary);"></div >
</div>

<!-- ==========================================
   MODAL: Schedule Followup (Calendar Context)
   ========================================== -->
<div id="schedule-cal-modal" class="modal-overlay">
    <div class="glass-panel modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Schedule Follow-up Agenda</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="schedule-cal-form">
            <div class="form-group">
                <label for="cal_lead">Select Lead Prospect *</label>
                <select id="cal_lead" name="lead_id" class="form-control" required>
                    <option value="">-- Choose Lead --</option>
                    <?php foreach ($leads as $l): ?>
                        <option value="<?php echo $l->id; ?>"><?php echo htmlspecialchars($l->name); ?> (<?php echo htmlspecialchars($l->company ?: 'Personal'); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="cal_title">Agenda Title *</label>
                <input type="text" id="cal_title" name="title" class="form-control" placeholder="e.g. Discuss invoice specifications" required>
            </div>

            <div class="form-group">
                <label for="cal_desc">Agenda Notes</label>
                <textarea id="cal_desc" name="description" class="form-control" rows="2" placeholder="Topics to cover..."></textarea>
            </div>

            <div class="form-group">
                <label for="cal_date">Scheduled Date & Time *</label>
                <input type="datetime-local" id="cal_date" name="scheduled_at" class="form-control" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Book Schedule</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    // Detect if dark mode is active to style calendar border/headers
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        themeSystem: 'standard',
        events: 'api/followups.php?action=get_events', // AJAX source
        editable: false,
        selectable: true,
        height: 'auto',
        
        // Custom styling callbacks
        eventDidMount: function(info) {
            const status = info.event.extendedProps.status;
            if (status === 'completed') {
                info.el.style.backgroundColor = 'var(--success-light)';
                info.el.style.borderColor = 'var(--success)';
                info.el.style.color = 'var(--success)';
                info.el.style.textDecoration = 'line-through';
            } else if (status === 'missed') {
                info.el.style.backgroundColor = 'var(--danger-light)';
                info.el.style.borderColor = 'var(--danger)';
                info.el.style.color = 'var(--danger)';
            } else {
                info.el.style.backgroundColor = 'var(--accent-light)';
                info.el.style.borderColor = 'var(--accent)';
                info.el.style.color = 'var(--accent)';
            }
            info.el.style.borderRadius = '6px';
            info.el.style.padding = '2px 6px';
            info.el.style.fontWeight = '500';
            info.el.style.fontSize = '12px';
        },

        select: function(info) {
            const dateStr = info.startStr;
            const modal = document.getElementById('schedule-cal-modal');
            const dateInput = document.getElementById('cal_date');
            
            if (dateInput) {
                dateInput.value = dateStr + "T09:00";
            }
            
            if (modal) {
                modal.classList.add('active');
            }
        },

        eventClick: function(info) {
            const leadId = info.event.extendedProps.lead_id;
            if (leadId) {
                showConfirm(
                    'View Lead Profile',
                    `Go to lead profile for "${info.event.title}"?`,
                    () => {
                        window.location.href = `lead-detail.php?id=${leadId}`;
                    },
                    null,
                    {
                        isDanger: false,
                        okText: 'Go to Profile',
                        iconHtml: '<i class="fa-solid fa-circle-info"></i>',
                        iconColor: 'var(--info)'
                    }
                );
            }
        }
    });

    calendar.render();

    // Re-render when theme changes to align boundaries nicely
    const themeBtn = document.getElementById('theme-toggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            setTimeout(() => { calendar.render(); }, 300);
        });
    }

    // Submit Calendar Schedule form
    const calForm = document.getElementById('schedule-cal-form');
    if (calForm) {
        calForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(calForm);
            const payload = { action: 'create' };
            formData.forEach((val, key) => { payload[key] = val; });

            sendAJAX('api/followups.php', payload, (res) => {
                if (res.success) {
                    showToast('Scheduled', 'Remind agenda successfully created', 'success');
                    document.getElementById('schedule-cal-modal').classList.remove('active');
                    calForm.reset();
                    calendar.refetchEvents();
                } else {
                    showToast('Failed', res.message || 'Could not schedule reminder', 'danger');
                }
            });
        });
    }
});
</script>
<style>
/* Smooth adjustment overrides for FullCalendar in glassmorphic cards */
.fc .fc-theme-standard td, .fc .fc-theme-standard th {
    border: 1px solid var(--border-glass);
}
.fc .fc-toolbar-title {
    font-size: 16px !important;
    font-weight: 700;
}
.fc .fc-button-primary {
    background: var(--bg-card) !important;
    border: 1px solid var(--border-glass) !important;
    color: var(--text-primary) !important;
    box-shadow: none !important;
    text-transform: capitalize;
    font-size: 13px;
    font-weight: 600;
}
.fc .fc-button-primary:hover {
    background: var(--accent-light) !important;
    color: var(--accent) !important;
}
.fc .fc-button-active {
    background: var(--accent-gradient) !important;
    color: white !important;
    border: none !important;
}
.fc-theme-standard .fc-scrollgrid {
    border: 1px solid var(--border-glass);
    border-radius: var(--border-radius);
    overflow: hidden;
}
.fc .fc-day-other {
    opacity: 0.35;
}
</style>
