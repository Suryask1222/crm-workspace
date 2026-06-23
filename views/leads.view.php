<!-- Action Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <!-- Filters form -->
    <form method="GET" action="leads.php" style="display: flex; gap: 12px; flex-wrap: wrap; flex-grow: 1;">
        <input type="text" name="search" class="form-control" placeholder="Search name, email, company..."
            style="max-width: 250px;" value="<?php echo htmlspecialchars($search); ?>">

        <select name="status" class="form-control" style="max-width: 150px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="new" <?php echo ($status === 'new') ? 'selected' : ''; ?>>New</option>
            <option value="contacted" <?php echo ($status === 'contacted') ? 'selected' : ''; ?>>Contacted</option>
            <option value="follow_up" <?php echo ($status === 'follow_up') ? 'selected' : ''; ?>>Follow-Up</option>
            <option value="qualified" <?php echo ($status === 'qualified') ? 'selected' : ''; ?>>Qualified</option>
            <option value="proposal_sent" <?php echo ($status === 'proposal_sent') ? 'selected' : ''; ?>>Proposal Sent</option>
            <option value="negotiation" <?php echo ($status === 'negotiation') ? 'selected' : ''; ?>>Negotiation</option>
            <option value="converted" <?php echo ($status === 'converted') ? 'selected' : ''; ?>>Converted</option>
            <option value="lost" <?php echo ($status === 'lost') ? 'selected' : ''; ?>>Lost</option>
        </select>

        <select name="priority" class="form-control" style="max-width: 150px;" onchange="this.form.submit()">
            <option value="">All Priorities</option>
            <option value="low" <?php echo ($priority === 'low') ? 'selected' : ''; ?>>Low</option>
            <option value="medium" <?php echo ($priority === 'medium') ? 'selected' : ''; ?>>Medium</option>
            <option value="high" <?php echo ($priority === 'high') ? 'selected' : ''; ?>>High</option>
        </select>

        <?php if ($search || $status || $priority): ?>
            <a href="leads.php" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        <?php endif; ?>
    </form>

    <div style="display: flex; gap: 12px; align-items: center;">
        <button class="btn btn-secondary" data-open-modal="import-modal"><i class="fa-solid fa-file-import"></i> Import</button>
        <a href="api/leads.php?action=export&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>"
            class="btn btn-secondary"><i class="fa-solid fa-file-export"></i> Export</a>
        <button class="btn btn-primary" data-open-modal="add-lead-modal"><i class="fa-solid fa-plus"></i> Add Lead</button>
    </div>
</div>

<!-- Main Leads Listing -->
<div class="glass-panel" style="padding: 24px; margin-bottom: 24px;">
    <!-- Desktop Grid View (Tables) -->
    <div class="desktop-only-view">
        <div class="table-responsive">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Lead Name</th>
                        <th>Company</th>
                        <th>Email & Phone</th>
                        <th>Source</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Expected Deal</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($leads) > 0): ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <a href="lead-detail.php?id=<?php echo $lead->id; ?>"
                                        style="font-weight: 600; text-decoration: none; color: var(--text-primary); transition: var(--transition);">
                                        <?php echo htmlspecialchars($lead->name); ?>
                                    </a>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Created:
                                        <?php echo date('M j, Y', strtotime($lead->created_at)); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($lead->company ?: '-'); ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                                        <?php echo htmlspecialchars($lead->industry ?: '-'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px;"><i class="fa-regular fa-envelope" style="width: 14px;"></i>
                                        <?php echo htmlspecialchars($lead->email ?: '-'); ?></div>
                                    <div style="font-size: 13px; margin-top: 4px;"><i class="fa-solid fa-phone"
                                            style="width: 14px;"></i> <?php echo htmlspecialchars($lead->phone ?: '-'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 13px;"><?php echo htmlspecialchars($lead->source ?: '-'); ?></span>
                                </td>
                                <td>
                                    <span class="priority-<?php echo $lead->priority; ?>">
                                        <i class="fa-solid fa-circle" style="font-size: 8px; margin-right: 4px; vertical-align: middle;"></i>
                                        <?php echo ucfirst($lead->priority); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $lead->status; ?>">
                                        <?php echo str_replace('_', ' ', $lead->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 500; font-size: 13px;">
                                        <?php echo htmlspecialchars($lead->assigned_name ?: 'Unassigned'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 14px;">
                                        <?php echo htmlspecialchars($currencySymbol) . number_format($lead->expected_value, 2); ?>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 8px;">
                                        <a href="lead-detail.php?id=<?php echo $lead->id; ?>" class="btn-icon" title="View Detail"><i class="fa-solid fa-eye"></i></a>
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-icon text-danger" title="Delete Lead"
                                                onclick="confirmDeleteLead(<?php echo $lead->id; ?>)">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px 0;">No leads found matching current search.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Grid View (Saves from Horizontal Scrolling) -->
    <div class="mobile-only-view" style="flex-direction: column; gap: 14px;">
        <?php if (count($leads) > 0): ?>
            <?php foreach ($leads as $lead): ?>
                <div class="glass-panel"
                    style="padding: 16px; border: 1px solid var(--border-glass); border-radius: 12px; display: flex; flex-direction: column; gap: 10px; background: rgba(255, 255, 255, 0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <a href="lead-detail.php?id=<?php echo $lead->id; ?>"
                                style="font-weight: 700; text-decoration: none; color: var(--text-primary); font-size: 15px;">
                                <?php echo htmlspecialchars($lead->name); ?>
                            </a>
                            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">
                                <?php echo htmlspecialchars($lead->company ?: 'Personal'); ?>
                            </div>
                        </div>
                        <span class="badge badge-<?php echo $lead->status; ?>" style="font-size: 10px; padding: 2px 6px;">
                            <?php echo str_replace('_', ' ', $lead->status); ?>
                        </span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px; font-size: 12px; border-top: 1px solid var(--border-glass); padding-top: 8px;">
                        <?php if ($lead->phone): ?>
                            <div><i class="fa-solid fa-phone" style="width: 14px; color: var(--accent);"></i> <a
                                    href="tel:<?php echo htmlspecialchars($lead->phone); ?>"
                                    style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($lead->phone); ?></a>
                            </div>
                        <?php endif; ?>
                        <?php if ($lead->email): ?>
                            <div><i class="fa-regular fa-envelope" style="width: 14px; color: var(--accent);"></i> <a
                                    href="mailto:<?php echo htmlspecialchars($lead->email); ?>"
                                    style="color: var(--accent); text-decoration: none;"><?php echo htmlspecialchars($lead->email); ?></a>
                            </div>
                        <?php endif; ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; font-size: 12px;">
                            <span>Expected Deal: <strong style="color: var(--accent);"><?php echo htmlspecialchars($currencySymbol) . number_format($lead->expected_value, 2); ?></strong></span>
                            <span class="priority-<?php echo $lead->priority; ?>"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle;"></i> <?php echo ucfirst($lead->priority); ?></span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-muted); background: rgba(0,0,0,0.02); padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-glass);">
                        <span>Owner: <strong><?php echo htmlspecialchars($lead->assigned_name ?: 'Unassigned'); ?></strong></span>
                        <div style="display: inline-flex; gap: 8px;">
                            <a href="lead-detail.php?id=<?php echo $lead->id; ?>" class="btn-icon" style="padding: 4px 8px; font-size: 11px;" title="View Detail"><i class="fa-solid fa-eye"></i> View</a>
                            <?php if ($isAdmin): ?>
                                <button class="btn-icon text-danger" style="padding: 4px 6px; font-size: 11px; border: none; background: transparent;" title="Delete Lead" onclick="confirmDeleteLead(<?php echo $lead->id; ?>)">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; color: var(--text-muted); padding: 20px 0;">No leads found matching current search.</div>
        <?php endif; ?>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 13px; color: var(--text-secondary);">
                Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $totalRecords; ?> total leads)
            </div>
            <div style="display: flex; gap: 8px;">
                <?php if ($page > 1): ?>
                    <a href="leads.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>"
                        class="btn btn-secondary"><i class="fa-solid fa-chevron-left"></i> Prev</a>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="leads.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>"
                        class="btn btn-secondary">Next <i class="fa-solid fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ==========================================
   MODAL: Add New Lead
   ========================================== -->
<div id="add-lead-modal" class="modal-overlay">
    <div class="glass-panel modal-content">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Add New Lead Prospect</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="add-lead-form">
            <div class="form-group">
                <label for="lead_name">Contact Person *</label>
                <input type="text" id="lead_name" name="name" class="form-control" placeholder="Client Name" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="lead_phone">Mobile/Phone</label>
                    <input type="text" id="lead_phone" name="phone" class="form-control" placeholder="+15550000">
                </div>
                <div class="form-group">
                    <label for="lead_email">Email Address</label>
                    <input type="email" id="lead_email" name="email" class="form-control" placeholder="name@company.com">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="lead_company">Company Name</label>
                    <input type="text" id="lead_company" name="company" class="form-control" placeholder="Acme Corp">
                </div>
                <div class="form-group">
                    <label for="lead_industry">Industry</label>
                    <input type="text" id="lead_industry" name="industry" class="form-control" placeholder="Technology">
                </div>
            </div>

            <div class="form-group">
                <label for="lead_address">Full Address</label>
                <input type="text" id="lead_address" name="address" class="form-control" placeholder="123 Corporate Ave, SF">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="lead_source">Lead Source</label>
                    <input type="text" id="lead_source" name="source" class="form-control" placeholder="e.g. Website, Referral">
                </div>
                <div class="form-group">
                    <label for="lead_expected">Expected Value (<?php echo htmlspecialchars($currencySymbol); ?>)</label>
                    <input type="number" step="0.01" id="lead_expected" name="expected_value" class="form-control" placeholder="5000.00" value="0.00">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="lead_priority">Priority</label>
                    <select id="lead_priority" name="priority" class="form-control">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lead_assigned">Assign Executive</label>
                    <select id="lead_assigned" name="assigned_to" class="form-control">
                        <option value="">Unassigned</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo ($u['id'] == $userId) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Create Lead</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
   MODAL: Import leads from CSV
   ========================================== -->
<div id="import-modal" class="modal-overlay">
    <div class="glass-panel modal-content">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Bulk CSV Lead Import</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="import-csv-form" enctype="multipart/form-data">
            <div style="background: rgba(255,255,255,0.02); border: 1px dashed var(--border-glass); padding: 24px; text-align: center; border-radius: 12px; margin-bottom: 20px;">
                <i class="fa-solid fa-file-csv" style="font-size: 40px; color: var(--accent); margin-bottom: 12px;"></i>
                <div style="font-size: 13px; font-weight: 600; margin-bottom: 4px;">Choose CSV file to upload</div>
                <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 16px;">Make sure your file columns match the templates format.</div>

                <input type="file" id="csv_file" name="csv_file" accept=".csv" required style="font-size: 12px;">
            </div>

            <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 24px;">
                <i class="fa-solid fa-circle-info" style="margin-right: 4px; color: var(--info);"></i>
                Column Headers Required: <br>
                <code style="background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 4px;">Name, Phone, Email, Company, Industry, Source, Priority, Expected Value</code>
                <br><br>
                <a href="api/import.php?action=template" style="color: var(--accent); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-download"></i> Download CSV Import Template</a>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Process Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Submit Add Lead Form via AJAX
        const addLeadForm = document.getElementById('add-lead-form');
        if (addLeadForm) {
            addLeadForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(addLeadForm);

                // Build simple object
                const payload = {
                    action: 'create'
                };
                formData.forEach((val, key) => { payload[key] = val; });

                sendAJAX('api/leads.php', payload, (res) => {
                    if (res.success) {
                        showToast('Success', 'Lead successfully created!', 'success');
                        document.getElementById('add-lead-modal').classList.remove('active');
                        addLeadForm.reset();
                        setTimeout(() => { window.location.reload(); }, 1000);
                    } else {
                        showToast('Failed', res.message || 'Could not add lead', 'danger');
                    }
                });
            });
        }

        // 2. Submit CSV Import Form via AJAX
        const importForm = document.getElementById('import-csv-form');
        if (importForm) {
            importForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const fileInput = document.getElementById('csv_file');
                if (fileInput.files.length === 0) {
                    showToast('Error', 'Please select a file to import.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('csv_file', fileInput.files[0]);
                formData.append('action', 'import_csv');

                sendAJAX('api/import.php', formData, (res) => {
                    if (res.success) {
                        showToast('Import Complete', `${res.inserted} leads imported successfully!`, 'success');
                        document.getElementById('import-modal').classList.remove('active');
                        importForm.reset();
                        setTimeout(() => { window.location.reload(); }, 1500);
                    } else {
                        showToast('Upload Failed', res.message || 'Validation error', 'danger');
                    }
                });
            });
        }
    });

    // 3. Confirm Delete Lead (Admin Only)
    function confirmDeleteLead(id) {
        showConfirm(
            'Delete Lead',
            'Are you sure you want to permanently delete this lead prospect? This action will remove all history, notes, and tasks.',
            () => {
                sendAJAX('api/leads.php', { action: 'delete', id: id }, (res) => {
                    if (res.success) {
                        showToast('Lead Deleted', 'The lead has been removed.', 'success');
                        setTimeout(() => { window.location.reload(); }, 800);
                    } else {
                        showToast('Action Failed', res.message || 'Could not complete deletion', 'danger');
                    }
                });
            },
            null,
            {
                isDanger: true,
                okText: 'Delete Prospect',
                iconHtml: '<i class="fa-solid fa-trash-can"></i>',
                iconColor: 'var(--danger)'
            }
        );
    }
</script>
