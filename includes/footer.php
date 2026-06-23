<?php
// includes/footer.php
?>
    </main> <!-- End of Main Content -->

    <!-- Reusable Custom Confirmation Modal -->
    <div id="confirm-modal" class="modal-overlay">
        <div class="glass-panel modal-content" style="max-width: 400px; text-align: center; padding: 28px;">
            <div id="confirm-icon-container" style="font-size: 44px; color: var(--warning); margin-bottom: 16px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 id="confirm-title" style="font-weight: 700; font-size: 18px; margin-bottom: 12px; color: var(--text-primary);">Confirm Action</h3>
            <p id="confirm-message" style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 24px;">Are you sure you want to proceed?</p>
            <div style="display: flex; justify-content: center; gap: 12px;">
                <button id="confirm-cancel-btn" class="btn btn-secondary" style="padding: 8px 16px;">Cancel</button>
                <button id="confirm-ok-btn" class="btn btn-primary" style="padding: 8px 16px; background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Global Toast Alert Container -->
    <div id="toast-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 350px; width: 100%;"></div>

    <!-- Core App JS -->
    <script src="<?php echo getBaseURL(); ?>assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/app.js'); ?>"></script>
</body>
</html>
