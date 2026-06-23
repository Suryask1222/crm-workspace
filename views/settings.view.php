<!-- views/settings.view.php -->
<?php if ($successMsg): ?>
    <div style="background: var(--success-light); color: var(--success); padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 500; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.1);">
        <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($successMsg); ?>
    </div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div style="background: var(--danger-light); color: var(--danger); padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 500; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.1);">
        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($errorMsg); ?>
    </div>
<?php endif; ?>

<!-- Tabs Navigation Header -->
<div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--border-glass); padding-bottom: 12px; overflow-x: auto;">
    <button class="btn btn-primary tab-btn" onclick="switchSettingsTab('crm-tab')">CRM Configuration</button>
    <button class="btn btn-secondary tab-btn" onclick="switchSettingsTab('users-tab')">User Management</button>
    <button class="btn btn-secondary tab-btn" onclick="switchSettingsTab('audit-tab')">Security Logs</button>
</div>

<!-- ==========================================
   TAB 1: CRM Config Parameters
   ========================================== -->
<div id="crm-tab" class="settings-content-block">
    <div class="glass-panel" style="padding: 24px;">
        <h3 class="chart-title" style="margin-bottom: 20px;"><i class="fa-solid fa-screwdriver-wrench" style="color: var(--accent); margin-right: 6px;"></i> CRM Parameter Variables</h3>
        <form method="POST" action="settings.php">
            <input type="hidden" name="action" value="save_settings">
            
            <h4 style="font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--accent); margin-bottom: 16px;">General Parameters</h4>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label for="company_name">Company Brand Name</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="currency">Currency Code</label>
                    <input type="text" id="currency" name="currency" class="form-control" value="<?php echo htmlspecialchars($settings['currency'] ?? 'USD'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="currency_symbol">Currency Symbol</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" class="form-control" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>" required>
                </div>
            </div>

            <h4 style="font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--accent); margin-bottom: 16px;">Mail/SMTP Configuration</h4>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1.5fr 1.5fr; gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label for="smtp_host">SMTP Server Host</label>
                    <input type="text" id="smtp_host" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_port">SMTP Port</label>
                    <input type="text" id="smtp_port" name="smtp_port" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_user">SMTP User/Auth Email</label>
                    <input type="text" id="smtp_user" name="smtp_user" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_pass">SMTP Auth Password</label>
                    <input type="password" id="smtp_pass" name="smtp_pass" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>">
                </div>
            </div>

            <h4 style="font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--accent); margin-bottom: 16px;">API Gateway Integrations (Ready)</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label for="whatsapp_url">WhatsApp Gateway Endpoint</label>
                    <input type="text" id="whatsapp_url" name="whatsapp_api_url" class="form-control" value="<?php echo htmlspecialchars($settings['whatsapp_api_url'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="sms_key">SMS API Gateway Secret Key</label>
                    <input type="text" id="sms_key" name="sms_api_key" class="form-control" value="<?php echo htmlspecialchars($settings['sms_api_key'] ?? ''); ?>">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Commit Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
   TAB 2: CRM User Manager
   ========================================== -->
<div id="users-tab" class="settings-content-block" style="display: none;">
    <div class="glass-panel" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 class="chart-title"><i class="fa-solid fa-users-gear" style="color: var(--accent); margin-right: 6px;"></i> Systems User Directory</h3>
            <button class="btn btn-primary" data-open-modal="add-user-modal"><i class="fa-solid fa-user-plus"></i> Add User</button>
        </div>
        <div class="table-responsive">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Login Email</th>
                        <th>Assigned Role</th>
                        <th>User Status</th>
                        <th>Created Date</th>
                        <th style="text-align: right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usersList as $u): ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="badge" style="background: <?php echo $u['role_name'] === 'Admin' ? 'var(--accent-light)' : 'var(--info-light)'; ?>; color: <?php echo $u['role_name'] === 'Admin' ? 'var(--accent)' : 'var(--info)'; ?>;">
                                    <?php echo $u['role_name']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: <?php echo $u['status'] === 'active' ? 'var(--success-light)' : 'var(--danger-light)'; ?>; color: <?php echo $u['status'] === 'active' ? 'var(--success)' : 'var(--danger)'; ?>;">
                                    <?php echo $u['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <button class="btn" style="padding: 4px 8px; font-size: 11px; background: var(--bg-card); border-color: var(--border-glass);" onclick="toggleUserStatus(<?php echo $u['id']; ?>, '<?php echo $u['status'] === 'active' ? 'inactive' : 'active'; ?>')">
                                        Toggle State
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
   TAB 3: Audits & Login activities
   ========================================== -->
<div id="audit-tab" class="settings-content-block" style="display: none;">
    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px;">
        <!-- Audit Logs -->
        <div class="glass-panel" style="padding: 24px;">
            <h3 class="chart-title" style="margin-bottom: 20px;"><i class="fa-solid fa-list-timeline" style="color: var(--warning); margin-right: 6px;"></i> CRM Action Audit Trails</h3>
            <div class="table-responsive">
                <table class="crm-table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Staff Member</th>
                            <th>Target Record</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($auditLogs) > 0): ?>
                            <?php foreach ($auditLogs as $a): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; text-transform: uppercase;"><?php echo str_replace('_', ' ', $a['action']); ?></div>
                                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">Table: <?php echo $a['table_name']; ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($a['user_name'] ?: 'System'); ?></td>
                                    <td>ID: <?php echo $a['record_id'] ?: '-'; ?></td>
                                    <td><?php echo date('M j, g:i A', strtotime($a['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px 0;">No audit changes logged.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Login access log -->
        <div class="glass-panel" style="padding: 24px;">
            <h3 class="chart-title" style="margin-bottom: 20px;"><i class="fa-solid fa-shield-halved" style="color: var(--danger); margin-right: 6px;"></i> Security Login Monitoring</h3>
            <div class="table-responsive">
                <table class="crm-table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>User Email</th>
                            <th>IP Address</th>
                            <th>Login Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($loginLogs) > 0): ?>
                            <?php foreach ($loginLogs as $l): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($l['user_name'] ?: 'Unknown'); ?></div>
                                        <div style="font-size: 10px; color: var(--text-muted);"><?php echo htmlspecialchars($l['user_email'] ?: '-'); ?></div>
                                    </td>
                                    <td><?php echo $l['ip_address']; ?></td>
                                    <td><?php echo date('M j, g:i A', strtotime($l['login_time'])); ?></td>
                                    <td>
                                        <span class="badge" style="background: <?php echo $l['status'] === 'success' ? 'var(--success-light)' : 'var(--danger-light)'; ?>; color: <?php echo $l['status'] === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; font-size: 10px; padding: 2px 6px;">
                                            <?php echo $l['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px 0;">No login activities recorded.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
   MODAL: Add System User
   ========================================== -->
<div id="add-user-modal" class="modal-overlay">
    <div class="glass-panel modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Register CRM User Account</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="add-user-form">
            <div class="form-group">
                <label for="new_user_name">Full Name *</label>
                <input type="text" id="new_user_name" name="name" class="form-control" placeholder="John Sales Executive" required>
            </div>

            <div class="form-group">
                <label for="new_user_email">Work Email *</label>
                <input type="email" id="new_user_email" name="email" class="form-control" placeholder="john@company.com" required>
            </div>

            <div class="form-group">
                <label for="new_user_pass">Account Password *</label>
                <input type="password" id="new_user_pass" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label for="new_user_role">Access Role *</label>
                <select id="new_user_role" name="role_id" class="form-control" required>
                    <option value="">-- Select Role --</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo $r['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Register Account</button>
            </div>
        </form>
    </div>
</div>

<script>
// Switch settings sub-tabs
function switchSettingsTab(tabId) {
    // Hide all
    document.querySelectorAll('.settings-content-block').forEach(b => {
        b.style.display = 'none';
    });
    // Set all tab triggers to secondary
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.className = 'btn btn-secondary tab-btn';
    });
    
    // Show active tab
    const targetBlock = document.getElementById(tabId);
    if (targetBlock) {
        targetBlock.style.display = 'block';
    }
    
    // Highlight button
    const activeBtn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.outerHTML.includes(tabId));
    if (activeBtn) {
        activeBtn.className = 'btn btn-primary tab-btn';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Submit Add User form
    const userForm = document.getElementById('add-user-form');
    if (userForm) {
        userForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(userForm);
            
            const payload = {
                action: 'create_user',
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password'),
                role_id: formData.get('role_id')
            };

            sendAJAX('api/leads.php', payload, (res) => {
                if (res.success) {
                    showToast('Created', 'New user account registered!', 'success');
                    document.getElementById('add-user-modal').classList.remove('active');
                    userForm.reset();
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    showToast('Failed', res.message || 'Registration failed', 'danger');
                }
            });
        });
    }
});

// Toggle status active/inactive
function toggleUserStatus(id, status) {
    sendAJAX('api/leads.php', { action: 'toggle_user_status', id: id, status: status }, (res) => {
        if (res.success) {
            showToast('Account Status Modified', `User is now ${status}`, 'success');
            setTimeout(() => { window.location.reload(); }, 800);
        } else {
            showToast('Failed', 'Status not modified', 'danger');
        }
    });
}
</script>
