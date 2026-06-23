<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar" class="glass-panel">
    <div class="logo">
        <i class="fa-solid fa-rocket-launch" style="font-size: 24px; background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
        <span style="font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($companyName); ?>"><?php echo htmlspecialchars($companyName); ?></span>
    </div>

    <!-- Navigation List -->
    <ul class="nav-links">
        <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'leads.php' || $current_page == 'lead-detail.php') ? 'active' : ''; ?>">
            <a href="leads.php">
                <i class="fa-solid fa-user-group"></i>
                <span>Leads</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'tasks.php') ? 'active' : ''; ?>">
            <a href="tasks.php">
                <i class="fa-solid fa-list-check"></i>
                <span>Tasks</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'calendar.php') ? 'active' : ''; ?>">
            <a href="calendar.php">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Calendar</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'customers.php') ? 'active' : ''; ?>">
            <a href="customers.php">
                <i class="fa-solid fa-briefcase"></i>
                <span>Customers</span>
            </a>
        </li>
        
        <!-- Admin-Only Links -->
        <?php if ($_SESSION['user_role'] === 'Admin'): ?>
            <li class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <a href="reports.php">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <a href="settings.php">
                    <i class="fa-solid fa-gears"></i>
                    <span>Settings</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Footer of Sidebar -->
    <div class="sidebar-footer">
        <ul class="nav-links">
            <li>
                <a href="logout.php" style="color: var(--danger); background: transparent;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
