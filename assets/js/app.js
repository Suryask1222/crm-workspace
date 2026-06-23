// assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSidebar();
    initNotifications();
    initGlobalForms();
});

/* ==========================================
   1. Theme Management (Light / Dark Mode)
   ========================================== */
function initTheme() {
    const themeToggle = document.getElementById('theme-toggle');
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    // Apply initial theme
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }
}

function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;
    
    const icon = themeToggle.querySelector('i');
    if (theme === 'dark') {
        icon.className = 'fa-solid fa-sun';
    } else {
        icon.className = 'fa-solid fa-moon';
    }
}

/* ==========================================
   2. Responsive Sidebar Drawer
   ========================================== */
function initSidebar() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });

        // Close sidebar if clicked outside (on mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }
}

/* ==========================================
   3. Notifications Panel & Polling
   ========================================== */
function initNotifications() {
    const notifToggle = document.getElementById('notif-toggle');
    const notifDropdown = document.getElementById('notif-dropdown');
    const notifListContainer = document.getElementById('notif-list-container');
    const markAllRead = document.getElementById('mark-all-read');

    if (notifToggle && notifDropdown) {
        notifToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = notifDropdown.style.display === 'flex';
            notifDropdown.style.display = isVisible ? 'none' : 'flex';
            if (!isVisible) {
                fetchNotifications();
            }
        });

        document.addEventListener('click', (e) => {
            if (notifDropdown.style.display === 'flex' && !notifDropdown.contains(e.target) && e.target !== notifToggle) {
                notifDropdown.style.display = 'none';
            }
        });
    }

    if (markAllRead) {
        markAllRead.addEventListener('click', () => {
            sendAJAX('api/notifications.php', { action: 'mark_all_read' }, (res) => {
                if (res.success) {
                    showToast('Success', 'All notifications marked as read', 'success');
                    fetchNotifications();
                    // Update badge if present
                    const badge = notifToggle.querySelector('span');
                    if (badge) badge.remove();
                }
            });
        });
    }

    function fetchNotifications() {
        if (!notifListContainer) return;
        
        fetch('api/notifications.php?action=get_all')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.notifications.length > 0) {
                    notifListContainer.innerHTML = '';
                    data.notifications.forEach(notif => {
                        const unreadClass = notif.is_read == 0 ? 'unread' : '';
                        const dateStr = formatDateTime(notif.created_at);
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = `notification-menu-item ${unreadClass}`;
                        item.innerHTML = `
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 600; font-size: 13px;">${escapeHTML(notif.title)}</div>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">${escapeHTML(notif.message)}</div>
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">${dateStr}</div>
                            </div>
                        `;
                        
                        // Mark single read on hover/click
                        item.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (notif.is_read == 0) {
                                sendAJAX('api/notifications.php', { action: 'mark_read', id: notif.id }, (r) => {
                                    if (r.success) {
                                        fetchNotifications();
                                        // Simple subtraction check on headers if needed
                                    }
                                });
                            }
                        });
                        
                        notifListContainer.appendChild(item);
                    });
                } else {
                    notifListContainer.innerHTML = `
                        <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                            No new notifications
                        </div>`;
                }
            })
            .catch(() => {
                notifListContainer.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: var(--danger); font-size: 13px;">
                        Failed to fetch notifications
                    </div>`;
            });
    }

    // Real-time notification check: Check for new unread notifications every 30 seconds
    setInterval(() => {
        fetch('api/notifications.php?action=check_new')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.new_count > 0) {
                    // Update header badge or flash icon
                    let badge = notifToggle.querySelector('span');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.style = "position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700;";
                        notifToggle.appendChild(badge);
                    }
                    badge.textContent = data.new_count;
                }
            }).catch(()=>{});
    }, 30000);
}

/* ==========================================
   4. Reusable Toast Notification System
   ========================================== */
function showToast(title, message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'glass-panel';
    toast.style = `
        padding: 16px;
        border-radius: 12px;
        display: flex;
        gap: 12px;
        align-items: center;
        box-shadow: var(--card-shadow);
        border-left: 4px solid var(--accent);
        transform: translateX(120%);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    `;

    // Border color matches types
    if (type === 'success') {
        toast.style.borderLeftColor = 'var(--success)';
    } else if (type === 'warning') {
        toast.style.borderLeftColor = 'var(--warning)';
    } else if (type === 'danger') {
        toast.style.borderLeftColor = 'var(--danger)';
    }

    // Icon matching
    let iconClass = 'fa-circle-info text-primary';
    if (type === 'success') iconClass = 'fa-circle-check';
    if (type === 'warning') iconClass = 'fa-triangle-exclamation';
    if (type === 'danger') iconClass = 'fa-circle-xmark';

    toast.innerHTML = `
        <div style="font-size: 20px; color: ${type === 'success' ? 'var(--success)' : type === 'warning' ? 'var(--warning)' : type === 'danger' ? 'var(--danger)' : 'var(--accent)'}">
            <i class="fa-solid ${iconClass}"></i>
        </div>
        <div style="flex-grow: 1;">
            <div style="font-weight: 600; font-size: 13px;">${escapeHTML(title)}</div>
            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">${escapeHTML(message)}</div>
        </div>
        <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
    `;

    container.appendChild(toast);

    // Slide-in effect
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 50);

    // Slide-out and destroy after 4 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 4000);
}

/* ==========================================
   5. Global Forms and Modal Toggle Helper
   ========================================== */
function initGlobalForms() {
    // Modal controls helper
    const openTriggers = document.querySelectorAll('[data-open-modal]');
    const closeTriggers = document.querySelectorAll('[data-close-modal]');

    openTriggers.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-open-modal');
            const modal = document.getElementById(targetId);
            if (modal) modal.classList.add('active');
        });
    });

    closeTriggers.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-overlay');
            if (modal) modal.classList.remove('active');
        });
    });

    // Handle generic clicks on modal overlay background to close
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });
}

/* ==========================================
   6. Core AJAX Helpers
   ========================================== */
function sendAJAX(url, payload, callback) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Add CSRF to payload if it's an object/FormData
    let body;
    let headers = {};

    if (payload instanceof FormData) {
        payload.append('csrf_token', csrfToken);
        body = payload;
    } else {
        payload.csrf_token = csrfToken;
        body = JSON.stringify(payload);
        headers['Content-Type'] = 'application/json';
    }

    fetch(url, {
        method: 'POST',
        headers: headers,
        body: body
    })
    .then(res => {
        if (!res.ok) throw new Error('Network error');
        return res.json();
    })
    .then(data => {
        callback(data);
    })
    .catch(err => {
        console.error(err);
        showToast('System Error', 'An error occurred while processing the request', 'danger');
    });
}

// Utility formatting
function formatDateTime(sqlDate) {
    if (!sqlDate) return '';
    const date = new Date(sqlDate.replace(' ', 'T'));
    return date.toLocaleDateString(undefined, { 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
}

// Drag & Drop tasks initialization exports (called in tasks.php scripts)
function makeTasksDraggable(onStateChangeCallback) {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column');
    let draggedCard = null;

    cards.forEach(card => {
        card.addEventListener('dragstart', (e) => {
            draggedCard = card;
            e.dataTransfer.setData('text/plain', card.getAttribute('data-task-id'));
            setTimeout(() => {
                card.style.display = 'none';
            }, 0);
        });

        card.addEventListener('dragend', () => {
            setTimeout(() => {
                if (draggedCard) draggedCard.style.display = 'block';
                draggedCard = null;
            }, 0);
        });
    });

    columns.forEach(col => {
        col.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        col.addEventListener('drop', (e) => {
            e.preventDefault();
            const taskId = e.dataTransfer.getData('text/plain');
            const targetStatus = col.getAttribute('data-status');
            
            if (draggedCard && taskId) {
                col.querySelector('.kanban-cards-wrapper').appendChild(draggedCard);
                // Trigger AJAX callback to update task state
                onStateChangeCallback(taskId, targetStatus);
            }
        });
    });
}

/* ==========================================
   7. Reusable Custom Alert & Confirm Modals
   ========================================== */
let confirmModalCleanup = null;

function showConfirm(title, message, onConfirmCallback, onCancelCallback = null, options = {}) {
    const modal = document.getElementById('confirm-modal');
    const titleEl = document.getElementById('confirm-title');
    const messageEl = document.getElementById('confirm-message');
    const okBtn = document.getElementById('confirm-ok-btn');
    const cancelBtn = document.getElementById('confirm-cancel-btn');
    const iconContainer = document.getElementById('confirm-icon-container');
    
    if (!modal || !titleEl || !messageEl || !okBtn || !cancelBtn) return;
    
    // Clean up any existing listeners/state to prevent memory leaks
    if (confirmModalCleanup) {
        confirmModalCleanup();
    }
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    // Configure buttons
    cancelBtn.style.display = 'inline-block';
    cancelBtn.textContent = options.cancelText || 'Cancel';
    okBtn.textContent = options.okText || 'Confirm';
    
    // Configure button colors/styles
    if (options.isDanger !== false) {
        okBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
        okBtn.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.2)';
    } else {
        okBtn.style.background = 'var(--accent-gradient)';
        okBtn.style.boxShadow = '0 4px 12px var(--accent-light)';
    }
    
    if (iconContainer) {
        iconContainer.innerHTML = options.iconHtml || '<i class="fa-solid fa-triangle-exclamation"></i>';
        iconContainer.style.color = options.iconColor || 'var(--warning)';
    }
    
    modal.classList.add('active');
    
    const handleConfirm = () => {
        modal.classList.remove('active');
        cleanup();
        if (onConfirmCallback) onConfirmCallback();
    };
    
    const handleCancel = () => {
        modal.classList.remove('active');
        cleanup();
        if (onCancelCallback) onCancelCallback();
    };
    
    const handleOutsideClick = (e) => {
        if (e.target === modal) {
            handleCancel();
        }
    };
    
    const cleanup = () => {
        okBtn.removeEventListener('click', handleConfirm);
        cancelBtn.removeEventListener('click', handleCancel);
        modal.removeEventListener('click', handleOutsideClick);
        confirmModalCleanup = null;
    };
    
    okBtn.addEventListener('click', handleConfirm);
    cancelBtn.addEventListener('click', handleCancel);
    modal.addEventListener('click', handleOutsideClick);
    
    confirmModalCleanup = cleanup;
}

function showAlert(title, message, onOkCallback = null, options = {}) {
    const modal = document.getElementById('confirm-modal');
    const titleEl = document.getElementById('confirm-title');
    const messageEl = document.getElementById('confirm-message');
    const okBtn = document.getElementById('confirm-ok-btn');
    const cancelBtn = document.getElementById('confirm-cancel-btn');
    const iconContainer = document.getElementById('confirm-icon-container');
    
    if (!modal || !titleEl || !messageEl || !okBtn || !cancelBtn) return;
    
    if (confirmModalCleanup) {
        confirmModalCleanup();
    }
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    cancelBtn.style.display = 'none';
    okBtn.textContent = options.okText || 'OK';
    okBtn.style.background = 'var(--accent-gradient)';
    okBtn.style.boxShadow = '0 4px 12px var(--accent-light)';
    
    if (iconContainer) {
        iconContainer.innerHTML = options.iconHtml || '<i class="fa-solid fa-circle-info"></i>';
        iconContainer.style.color = options.iconColor || 'var(--info)';
    }
    
    modal.classList.add('active');
    
    const handleOk = () => {
        modal.classList.remove('active');
        cleanup();
        if (onOkCallback) onOkCallback();
    };
    
    const handleOutsideClick = (e) => {
        if (e.target === modal) {
            handleOk();
        }
    };
    
    const cleanup = () => {
        okBtn.removeEventListener('click', handleOk);
        modal.removeEventListener('click', handleOutsideClick);
        confirmModalCleanup = null;
    };
    
    okBtn.addEventListener('click', handleOk);
    modal.addEventListener('click', handleOutsideClick);
    
    confirmModalCleanup = cleanup;
}
