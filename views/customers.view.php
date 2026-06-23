<!-- Action Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <!-- Filters form -->
    <form method="GET" action="customers.php" style="display: flex; gap: 12px; flex-wrap: wrap; flex-grow: 1;">
        <input type="text" name="search" class="form-control" placeholder="Search customer, company..." style="max-width: 250px;" value="<?php echo htmlspecialchars($search); ?>">
        
        <select name="status" class="form-control" style="max-width: 150px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="active" <?php echo ($status === 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo ($status === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
        </select>

        <?php if ($search || $status): ?>
            <a href="customers.php" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Main Customer List -->
<div class="glass-panel" style="padding: 24px; margin-bottom: 24px;">
    <div class="table-responsive">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Company</th>
                    <th>Email & Phone</th>
                    <th>Account Status</th>
                    <th>Purchase Frequency</th>
                    <th>Total Deal Value</th>
                    <th>Lead Executive</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($c->name); ?></div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Added: <?php echo date('M j, Y', strtotime($c->created_at)); ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($c->company ?: '-'); ?></div>
                            </td>
                            <td>
                                <div style="font-size: 13px;"><i class="fa-regular fa-envelope" style="width: 14px;"></i> <?php echo htmlspecialchars($c->email ?: '-'); ?></div>
                                <div style="font-size: 13px; margin-top: 4px;"><i class="fa-solid fa-phone" style="width: 14px;"></i> <?php echo htmlspecialchars($c->phone ?: '-'); ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background: <?php echo $c->status === 'active' ? 'var(--success-light)' : 'var(--danger-light)'; ?>; color: <?php echo $c->status === 'active' ? 'var(--success)' : 'var(--danger)'; ?>;">
                                    <?php echo htmlspecialchars($c->status); ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 14px; text-align: center; max-width: 80px;">
                                    <?php echo $c->purchase_count; ?> <span style="font-size: 10px; font-weight: 500; color: var(--text-secondary);">deals</span>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; font-size: 15px; color: var(--success);"><?php echo htmlspecialchars($currencySymbol) . number_format($c->total_purchases, 2); ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 500; font-size: 13px;"><?php echo htmlspecialchars($c->assigned_name ?: 'General CRM'); ?></div>
                            </td>
                            <td style="text-align: right;">
                                <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" onclick="openRecordPurchaseModal(<?php echo $c->id; ?>, '<?php echo htmlspecialchars(addslashes($c->name)); ?>')">
                                    <i class="fa-solid fa-cart-plus"></i> Record Sale
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px 0;">No converted active customer profiles available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 13px; color: var(--text-secondary);">
                Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $totalRecords; ?> customers)
            </div>
            <div style="display: flex; gap: 8px;">
                <?php if ($page > 1): ?>
                    <a href="customers.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="btn btn-secondary"><i class="fa-solid fa-chevron-left"></i> Prev</a>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="customers.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="btn btn-secondary">Next <i class="fa-solid fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ==========================================
   MODAL: Record Customer Purchase
   ========================================== -->
<div id="purchase-modal" class="modal-overlay">
    <div class="glass-panel modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 style="font-weight: 700; font-size: 18px;">Record New Customer Purchase</h3>
            <button class="btn-icon" data-close-modal><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="purchase-form">
            <input type="hidden" name="customer_id" id="purchase_cust_id">
            <div style="margin-bottom: 20px; font-size: 13px; color: var(--text-secondary);">
                Record a subsequent sales agreement value for <strong id="purchase_cust_name"></strong>.
            </div>

            <div class="form-group">
                <label for="purchase_val">Deal Contract Value (<?php echo htmlspecialchars($currencySymbol); ?>) *</label>
                <input type="number" step="0.01" id="purchase_val" name="deal_value" class="form-control" placeholder="1500.00" required>
            </div>

            <div class="form-group">
                <label for="cust_status">Account Status</label>
                <select id="cust_status" name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn btn-primary">Log Agreement</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRecordPurchaseModal(id, name) {
    const modal = document.getElementById('purchase-modal');
    document.getElementById('purchase_cust_id').value = id;
    document.getElementById('purchase_cust_name').textContent = name;
    
    if (modal) {
        modal.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const purchaseForm = document.getElementById('purchase-form');
    if (purchaseForm) {
        purchaseForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(purchaseForm);
            
            const payload = {
                action: 'record_purchase',
                id: formData.get('customer_id'),
                deal_value: formData.get('deal_value'),
                status: formData.get('status')
            };

            sendAJAX('api/leads.php', payload, (res) => {
                if (res.success) {
                    showToast('Purchase Logged', 'Agreement deal added to database', 'success');
                    document.getElementById('purchase-modal').classList.remove('active');
                    purchaseForm.reset();
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    showToast('Failed', res.message || 'Log request failed', 'danger');
                }
            });
        });
    }
});
</script>
