<!-- Metrics Summary Cards -->
<div class="metrics-grid">
    <div class="glass-panel metric-card">
        <div class="metric-info">
            <h3><?php echo $totalLeads; ?></h3>
            <p>Total Leads</p>
        </div>
        <div class="metric-icon primary">
            <i class="fa-solid fa-user-group"></i>
        </div>
    </div>
    
    <div class="glass-panel metric-card">
        <div class="metric-info">
            <h3><?php echo $newLeads; ?></h3>
            <p>New Leads</p>
        </div>
        <div class="metric-icon info">
            <i class="fa-solid fa-user-plus"></i>
        </div>
    </div>

    <div class="glass-panel metric-card">
        <div class="metric-info">
            <h3><?php echo $followupsToday; ?></h3>
            <p>Follow-ups Today</p>
        </div>
        <div class="metric-icon warning">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
    </div>

    <div class="glass-panel metric-card">
        <div class="metric-info">
            <h3><?php echo $convertedLeads; ?></h3>
            <p>Converted Deals</p>
        </div>
        <div class="metric-icon success">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>

    <div class="glass-panel metric-card">
        <div class="metric-info">
            <h3><?php echo htmlspecialchars($currencySymbol) . number_format($revenueGenerated, 2); ?></h3>
            <p>Deal Revenue</p>
        </div>
        <div class="metric-icon success" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">
            <i class="fa-solid fa-money-bill-trend-up"></i>
        </div>
    </div>
</div>

<!-- Charts Panel -->
<div class="charts-grid">
    <div class="glass-panel chart-container">
        <div class="chart-header">
            <span class="chart-title">Revenue Growth Overview</span>
            <span style="font-size: 12px; color: var(--text-secondary);">Last 6 Months</span>
        </div>
        <div style="height: 300px; position: relative;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <div class="glass-panel chart-container">
        <div class="chart-header">
            <span class="chart-title">Lead Sources</span>
        </div>
        <div style="height: 300px; position: relative;">
            <canvas id="sourcesChart"></canvas>
        </div>
    </div>
</div>

<!-- Extra Details Row (Funnel, Tasks, Follow-ups, Activities) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
    
    <!-- Stage Funnel Analytics -->
    <div class="glass-panel" style="padding: 24px;">
        <div class="chart-header">
            <span class="chart-title">Lead Conversion Funnel</span>
        </div>
        <div style="height: 250px; position: relative;">
            <canvas id="funnelChart"></canvas>
        </div>
    </div>

    <!-- Active Tasks Summary -->
    <div class="glass-panel" style="padding: 24px; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <span class="chart-title">Your Active Tasks</span>
            <a href="tasks.php" style="font-size: 12px; color: var(--accent); font-weight: 600; text-decoration: none;">View all</a>
        </div>
        <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 12px;">
            <?php if (count($activeTasks) > 0): ?>
                <?php foreach ($activeTasks as $task): ?>
                    <div style="padding: 12px 16px; border-radius: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($task->title); ?></div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                                Due: <?php echo $task->due_date ? date('M j, Y', strtotime($task->due_date)) : 'No date'; ?>
                            </div>
                        </div>
                        <span class="badge" style="font-size: 10px; padding: 2px 8px; background: <?php echo $task->priority === 'high' ? 'var(--danger-light)' : ($task->priority === 'medium' ? 'var(--warning-light)' : 'var(--success-light)'); ?>; color: <?php echo $task->priority === 'high' ? 'var(--danger)' : ($task->priority === 'medium' ? 'var(--warning)' : 'var(--success)'); ?>;">
                            <?php echo htmlspecialchars($task->priority); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); margin: auto; font-size: 13px;">No active tasks assigned</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Upcoming Followups -->
    <div class="glass-panel" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <span class="chart-title">Upcoming Followups</span>
            <a href="calendar.php" style="font-size: 12px; color: var(--accent); font-weight: 600; text-decoration: none;">Calendar</a>
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php if (count($upcomingFollowups) > 0): ?>
                <?php foreach ($upcomingFollowups as $f): ?>
                    <div style="padding: 12px 16px; border-radius: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($f->title); ?></div>
                            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                Lead: <span style="font-weight: 500;"><?php echo htmlspecialchars($f->lead_name); ?></span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 12px; font-weight: 600; color: var(--accent);"><?php echo date('M j, g:i A', strtotime($f->scheduled_at)); ?></div>
                            <?php if ($isAdmin): ?>
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">Assigned to: <?php echo htmlspecialchars($f->staff_name); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 13px;">No upcoming follow-ups scheduled</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent System / Lead Activities -->
    <div class="glass-panel" style="padding: 24px;">
        <div class="chart-header">
            <span class="chart-title">Recent Work Activity</span>
        </div>
        <div class="timeline">
            <?php if (count($recentActivities) > 0): ?>
                <?php foreach ($recentActivities as $act): ?>
                    <div class="timeline-item">
                        <div class="timeline-header">
                            <span style="font-weight: 600;"><?php echo htmlspecialchars($act->user_name); ?></span>
                            <span><?php echo date('M j, g:i A', strtotime($act->created_at)); ?></span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-primary); margin-top: 2px;">
                            <?php echo htmlspecialchars($act->description); ?>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                            Lead Profile: <a href="lead-detail.php?id=<?php echo $act->lead_id; ?>" style="color: var(--accent); text-decoration: none; font-weight: 500;"><?php echo htmlspecialchars($act->lead_name); ?></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 13px;">No recent work activities recorded</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="assets/js/charts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Line Chart: Revenue Trend
    renderRevenueChart(
        'revenueChart', 
        <?php echo json_encode($months); ?>, 
        <?php echo json_encode($monthlyRevenue); ?>
    );

    // 2. Doughnut Chart: Lead Sources
    renderLeadSourcesChart(
        'sourcesChart', 
        <?php echo json_encode($sourceLabels); ?>, 
        <?php echo json_encode($sourceCounts); ?>
    );

    // 3. Bar Chart: Stage Funnel count
    renderConversionFunnelChart(
        'funnelChart', 
        ['New', 'Contacted', 'Follow-up', 'Qualified', 'Proposal', 'Negotiate', 'Converted', 'Lost'], 
        <?php echo json_encode($funnelData); ?>
    );
});
</script>
