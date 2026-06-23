<!-- Workflow Stages Indicator Bar -->
<div class="glass-panel" style="padding: 20px; margin-bottom: 24px; overflow-x: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; min-width: 750px; position: relative;">
        <!-- Connector Line behind steps -->
        <div style="position: absolute; left: 5%; top: 50%; width: 90%; height: 2px; background: var(--border-glass); z-index: 1;"></div>
        
        <?php 
        $foundCurrent = false; 
        foreach ($stages as $stageKey => $stageLabel): 
            $isCurrent = ($lead->status === $stageKey);
            $isActive = !$foundCurrent || $isCurrent;
            if ($isCurrent) $foundCurrent = true;
            
            $bg = $isCurrent ? 'var(--accent)' : ($isActive ? 'var(--accent-light)' : 'var(--bg-primary)');
            $color = $isCurrent ? 'white' : ($isActive ? 'var(--accent)' : 'var(--text-muted)');
            $border = $isCurrent ? 'none' : '1px solid var(--border-glass)';
        ?>
            <button onclick="changeLeadStatus('<?php echo $stageKey; ?>')" 
                    style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; border: none; background: none; cursor: pointer; outline: none; width: 100px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: <?php echo $bg; ?>; color: <?php echo $color; ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; border: <?php echo $border; ?>; font-size: 13px; transition: var(--transition);">
                    <?php if ($stageKey === 'converted' && $lead->status === 'converted'): ?>
                        <i class="fa-solid fa-check"></i>
                    <?php elseif ($stageKey === 'lost' && $lead->status === 'lost'): ?>
                        <i class="fa-solid fa-xmark"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-circle" style="font-size: 8px;"></i>
                    <?php endif; ?>
                </div>
                <span style="font-size: 11px; font-weight: <?php echo $isCurrent ? '700' : '500'; ?>; color: <?php echo $isCurrent ? 'var(--text-primary)' : 'var(--text-secondary)'; ?>;">
                    <?php echo $stageLabel; ?>
                </span>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<div class="lead-detail-grid">
    <!-- Left Column: Profile Card & Actions -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Profile info panel -->
        <div class="glass-panel" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 700;"><?php echo htmlspecialchars($lead->name); ?></h2>
                    <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;"><?php echo htmlspecialchars($lead->company ?: 'No Company'); ?></p>
                </div>
                <button class="btn-icon" data-open-modal="edit-lead-modal" title="Edit Profile"><i class="fa-regular fa-pen-to-square"></i></button>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 16px; border-top: 1px solid var(--border-glass); padding-top: 20px;">
                <div>
                    <div style="font-size: 11px; color: var(--text-muted);">Email Address</div>
                    <div style="font-size: 14px; font-weight: 500; margin-top: 4px;">
                        <?php if ($lead->email): ?>
                            <a href="mailto:<?php echo htmlspecialchars($lead->email); ?>" style="color: var(--accent); text-decoration: none;"><?php echo htmlspecialchars($lead->email); ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11px; color: var(--text-muted);">Phone / Mobile</div>
                    <div style="font-size: 14px; font-weight: 500; margin-top: 4px;">
                        <?php if ($lead->phone): ?>
                            <a href="tel:<?php echo htmlspecialchars($lead->phone); ?>" style="color: var(--text-primary); text-decoration: none;"><?php echo htmlspecialchars($lead->phone); ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11px; color: var(--text-muted);">Industry & Address</div>
                    <div style="font-size: 13px; font-weight: 500; margin-top: 4px;">
                        <span style="font-weight: 600;"><?php echo htmlspecialchars($lead->industry ?: '-'); ?></span> <br>
                        <span style="color: var(--text-secondary);"><?php echo htmlspecialchars($lead->address ?: '-'); ?></span>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11px; color: var(--text-muted);">Lead Source & Priority</div>
                    <div style="margin-top: 4px; display: flex; gap: 12px; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($lead->source ?: 'Unknown'); ?></span>
                        <span class="priority-<?php echo $lead->priority; ?>" style="font-size: 13px;">
                            <i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle;"></i> <?php echo ucfirst($lead->priority); ?>
                        </span>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11px; color: var(--text-muted);">Expected Deal Value</div>
                    <div style="font-size: 18px; font-weight: 700; color: var(--accent); margin-top: 4px;">
                        <?php echo htmlspecialchars($currencySymbol) . number_format($lead->expected_value, 2); ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11px; color: var(--text-muted);">Assigned Executive</div>
                    <div style="margin-top: 6px; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); padding: 8px 12px; border-radius: 10px;">
                        <span style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($lead->assigned_name ?: 'Unassigned'); ?></span>
                        <?php if ($isAdmin): ?>
                            <button class="btn" style="padding: 4px 8px; font-size: 11px; background: var(--accent-light); color: var(--accent);" data-open-modal="transfer-modal">Transfer</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Follow-ups planner -->
        <div class="glass-panel" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 15px; font-weight: 700;">Reminders & Follow-ups</h3>
                <button class="btn" style="padding: 6px 12px; font-size: 12px;" data-open-modal="schedule-follow-modal"><i class="fa-solid fa-plus"></i> Schedule</button>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php if (count($followups) > 0): ?>
                    <?php foreach ($followups as $f): 
                        $isOverdue = (strtotime($f['scheduled_at']) < time() && $f['status'] === 'pending');
                    ?>
                        <div style="padding: 12px; border-radius: 10px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: start; gap: 8px;">
                            <div>
                                <div style="font-size: 13px; font-weight: 600; <?php echo $f['status'] === 'completed' ? 'text-decoration: line-through; color: var(--text-muted);' : ''; ?>">
                                    <?php echo htmlspecialchars($f['title']); ?>
                                </div>
                                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;"><?php echo htmlspecialchars($f['description'] ?: 'No notes'); ?></div>
                                <div style="font-size: 10px; font-weight: 600; margin-top: 6px; color: <?php echo $f['status'] === 'completed' ? 'var(--success)' : ($isOverdue ? 'var(--danger)' : 'var(--accent)'); ?>">
                                    <i class="fa-regular fa-clock"></i> <?php echo date('M j, g:i A', strtotime($f['scheduled_at'])); ?> 
                                    <?php echo $isOverdue ? '(OVERDUE)' : ''; ?>
                                </div>
                            </div>
                            
                            <?php if ($f['status'] === 'pending'): ?>
                                <button class="btn" style="padding: 4px 8px; font-size: 10px; background: var(--success-light); color: var(--success);" onclick="completeFollowup(<?php echo $f['id']; ?>)">Done</button>
                            <?php else: ?>
                                <span style="font-size: 11px; color: var(--success); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-check-double"></i> Complete</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-muted); padding: 20px 0; font-size: 12px;">No scheduled calls or appointments.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Right Column: Timeline & Notes Thread -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Notes box -->
        <div class="glass-panel" style="padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 16px;">Internal Communication & Notes</h3>
            <form id="add-note-form">
                <div class="form-group" style="margin-bottom: 12px;">
                    <textarea id="note_content" name="note" class="form-control" rows="3" placeholder="Write internal team updates or call details..." required></textarea>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="is_internal" value="1" checked>
                        <span><i class="fa-solid fa-lock" style="font-size: 10px;"></i> Share as Internal Staff Note</span>
                    </label>
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;"><i class="fa-regular fa-paper-plane"></i> Save Note</button>
                </div>
            </form>
            
            <div class="notes-list" style="margin-top: 24px; border-top: 1px solid var(--border-glass); padding-top: 20px;">
                <?php if (count($notes) > 0): ?>
                    <?php foreach ($notes as $note): ?>
                        <div class="glass-panel note-card" style="background: rgba(255,255,255,0.01);">
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                                <span style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($note->user_name); ?></span>
                                <span style="display: inline-flex; align-items: center; gap: 8px;">
                                    <?php if ($note->is_internal): ?>
                                        <span style="color: var(--warning); font-size: 10px; display: inline-flex; align-items: center; gap: 4px; background: var(--warning-light); padding: 2px 6px; border-radius: 4px;"><i class="fa-solid fa-lock"></i> Internal</span>
                                    <?php endif; ?>
                                    <span><?php echo date('M j, Y, g:i A', strtotime($note->created_at)); ?></span>
                                </span>
                            </div>
                            <div style="font-size: 13px; line-height: 1.5; color: var(--text-primary); white-space: pre-wrap;"><?php echo htmlspecialchars($note->note); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-muted); padding: 20px 0; font-size: 13px;">No notes recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="glass-panel" style="padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 20px;">Lead Activity Timeline</h3>
            <div class="timeline">
                <?php if (count($activities) > 0): ?>
                    <?php foreach ($activities as $act): ?>
                        <div class="timeline-item">
                            <div class="timeline-header">
                                <span style="font-weight: 600;"><?php echo htmlspecialchars($act->user_name); ?></span>
                                <span><?php echo date('M j, Y, g:i A', strtotime($act->created_at)); ?></span>
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                                <?php echo htmlspecialchars($act->description); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-muted); padding: 20px 0; font-size: 13px;">No timeline logs created.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- ==========================================
   MODAL: Edit Lead Profile
   ========================================== -->
<div id="edit-lead-modal" class="modal-overlay">
    <div class="glass-panel modal-content">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Edit Lead Information</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="edit-lead-form">
            <input type="hidden" name="id" value="<?php echo $leadId; ?>">
            <div class="form-group">
                <label for="edit_name">Contact Person *</label>
                <input type="text" id="edit_name" name="name" class="form-control" value="<?php echo htmlspecialchars($lead->name); ?>" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="edit_phone">Mobile/Phone</label>
                    <input type="text" id="edit_phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($lead->phone ?: ''); ?>">
                </div>
                <div class="form-group">
                    <label for="edit_email">Email Address</label>
                    <input type="email" id="edit_email" name="email" class="form-control" value="<?php echo htmlspecialchars($lead->email ?: ''); ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="edit_company">Company Name</label>
                    <input type="text" id="edit_company" name="company" class="form-control" value="<?php echo htmlspecialchars($lead->company ?: ''); ?>">
                </div>
                <div class="form-group">
                    <label for="edit_industry">Industry</label>
                    <input type="text" id="edit_industry" name="industry" class="form-control" value="<?php echo htmlspecialchars($lead->industry ?: ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="edit_address">Full Address</label>
                <input type="text" id="edit_address" name="address" class="form-control" value="<?php echo htmlspecialchars($lead->address ?: ''); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="edit_source">Lead Source</label>
                    <input type="text" id="edit_source" name="source" class="form-control" value="<?php echo htmlspecialchars($lead->source ?: ''); ?>">
                </div>
                <div class="form-group">
                    <label for="edit_expected">Expected Value (<?php echo htmlspecialchars($currencySymbol); ?>)</label>
                    <input type="number" step="0.01" id="edit_expected" name="expected_value" class="form-control" value="<?php echo $lead->expected_value; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="edit_priority">Priority</label>
                <select id="edit_priority" name="priority" class="form-control">
                    <option value="low" <?php echo $lead->priority === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $lead->priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $lead->priority === 'high' ? 'selected' : ''; ?>>High</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
   MODAL: Assign Lead (Transfer Owner)
   ========================================== -->
<?php if ($isAdmin): ?>
<div id="transfer-modal" class="modal-overlay">
    <div class="glass-panel modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Transfer Lead Ownership</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="transfer-lead-form">
            <input type="hidden" name="id" value="<?php echo $leadId; ?>">
            <div class="form-group">
                <label for="transfer_assign">Choose Sales Executive</label>
                <select id="transfer_assign" name="assigned_to" class="form-control" required>
                    <option value="">Unassigned</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($u['id'] == $lead->assigned_to) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Owner</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ==========================================
   MODAL: Schedule Followup
   ========================================== -->
<div id="schedule-follow-modal" class="modal-overlay">
    <div class="glass-panel modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Schedule Follow-up Agenda</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="schedule-follow-form">
            <input type="hidden" name="lead_id" value="<?php echo $leadId; ?>">
            <div class="form-group">
                <label for="follow_title">Agenda Title *</label>
                <input type="text" id="follow_title" name="title" class="form-control" placeholder="e.g. Call to discuss proposal specs" required>
            </div>

            <div class="form-group">
                <label for="follow_desc">Agenda Notes</label>
                <textarea id="follow_desc" name="description" class="form-control" rows="2" placeholder="Detail notes about key topics..."></textarea>
            </div>

            <div class="form-group">
                <label for="follow_date">Scheduled Date & Time *</label>
                <input type="datetime-local" id="follow_date" name="scheduled_at" class="form-control" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Book Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
   MODAL: Customer Conversion Prompt
   ========================================== -->
<div id="convert-customer-modal" class="modal-overlay">
    <div class="glass-panel modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Convert Lead to Active Customer</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="convert-customer-form">
            <input type="hidden" name="lead_id" value="<?php echo $leadId; ?>">
            <div style="margin-bottom: 20px; font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                Awesome! You are converting <strong><?php echo htmlspecialchars($lead->name); ?></strong> from lead prospect to active customer. Please enter final contract purchase details below.
            </div>

            <div class="form-group">
                <label for="contract_value">Purchase Deal Size (<?php echo htmlspecialchars($currencySymbol); ?>) *</label>
                <input type="number" step="0.01" id="contract_value" name="total_purchases" class="form-control" value="<?php echo $lead->expected_value; ?>" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Purchase</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Submit Edit Profile form
    const editForm = document.getElementById('edit-lead-form');
    if (editForm) {
        editForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            const payload = { action: 'update' };
            formData.forEach((val, key) => { payload[key] = val; });

            sendAJAX('api/leads.php', payload, (res) => {
                if (res.success) {
                    showToast('Saved', 'Lead information updated', 'success');
                    document.getElementById('edit-lead-modal').classList.remove('active');
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    showToast('Failed', res.message || 'Update failed', 'danger');
                }
            });
        });
    }

    // 2. Submit Note Form
    const noteForm = document.getElementById('add-note-form');
    if (noteForm) {
        noteForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(noteForm);
            const payload = {
                action: 'create',
                lead_id: <?php echo $leadId; ?>,
                note: formData.get('note'),
                is_internal: formData.get('is_internal') ? 1 : 0
            };

            sendAJAX('api/notes.php', payload, (res) => {
                if (res.success) {
                    showToast('Success', 'Note added successfully', 'success');
                    noteForm.reset();
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    showToast('Failed', res.message || 'Could not save note', 'danger');
                }
            });
        });
    }

    // 3. Submit Follow-up Agenda
    const followForm = document.getElementById('schedule-follow-form');
    if (followForm) {
        followForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(followForm);
            const payload = { action: 'create' };
            formData.forEach((val, key) => { payload[key] = val; });

            sendAJAX('api/followups.php', payload, (res) => {
                if (res.success) {
                    showToast('Scheduled', 'Remind agenda successfully created', 'success');
                    document.getElementById('schedule-follow-modal').classList.remove('active');
                    followForm.reset();
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    showToast('Failed', res.message || 'Could not schedule reminder', 'danger');
                }
            });
        });
    }

    // 4. Submit Lead Transfer Form (Admin Only)
    const transferForm = document.getElementById('transfer-lead-form');
    if (transferForm) {
        transferForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(transferForm);
            const payload = { action: 'transfer' };
            formData.forEach((val, key) => { payload[key] = val; });

            sendAJAX('api/leads.php', payload, (res) => {
                if (res.success) {
                    showToast('Assigned', 'Lead ownership transferred successfully', 'success');
                    document.getElementById('transfer-modal').classList.remove('active');
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    showToast('Failed', res.message || 'Transfer failed', 'danger');
                }
            });
        });
    }

    // 5. Submit Customer Conversion Form
    const convertForm = document.getElementById('convert-customer-form');
    if (convertForm) {
        convertForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(convertForm);
            const payload = {
                action: 'convert_to_customer',
                lead_id: formData.get('lead_id'),
                total_purchases: formData.get('total_purchases')
            };

            sendAJAX('api/leads.php', payload, (res) => {
                if (res.success) {
                    showToast('Success', 'Lead successfully converted to Customer status!', 'success');
                    document.getElementById('convert-customer-modal').classList.remove('active');
                    setTimeout(() => { window.location.href = 'customers.php'; }, 1000);
                } else {
                    showToast('Failed', res.message || 'Conversion failed', 'danger');
                }
            });
        });
    }
});

// 6. Complete Follow-up inline click
function completeFollowup(id) {
    sendAJAX('api/followups.php', { action: 'complete', id: id }, (res) => {
        if (res.success) {
            showToast('Followup Completed', 'Event status flagged as complete.', 'success');
            setTimeout(() => { window.location.reload(); }, 800);
        } else {
            showToast('Failed', 'Status not modified', 'danger');
        }
    });
}

// 7. Change Lead Status Workflow
function changeLeadStatus(status) {
    if (status === 'converted') {
        // Trigger customer conversion modal
        document.getElementById('convert-customer-modal').classList.add('active');
        return;
    }

    sendAJAX('api/leads.php', { action: 'update_status', id: <?php echo $leadId; ?>, status: status }, (res) => {
        if (res.success) {
            showToast('Workflow Updated', `Lead status is now ${status.replace('_', ' ')}`, 'success');
            setTimeout(() => { window.location.reload(); }, 800);
        } else {
            showToast('Action failed', res.message || 'Could not alter status', 'danger');
        }
    });
}
</script>
