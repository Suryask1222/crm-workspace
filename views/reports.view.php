<!-- views/reports.view.php -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Sales Revenue Trend (This Year) -->
    <div class="glass-panel" style="padding: 24px;">
        <div class="chart-header">
            <span class="chart-title">Current Year Revenue Growth Summary (<?php echo $year; ?>)</span>
        </div>
        <div style="height: 300px; position: relative;">
            <canvas id="yearlySalesChart"></canvas>
        </div>
    </div>

    <!-- Conversion Funnel Analysis Metrics -->
    <div class="glass-panel" style="padding: 24px;">
        <h3 class="chart-title" style="margin-bottom: 20px;">Lead Stage Distributions</h3>
        <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($stageStats as $st): ?>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 500; margin-bottom: 6px;">
                        <span style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $st['status']); ?></span>
                        <span style="font-weight: 600;"><?php echo $st['count']; ?> leads (<?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($st['total_val'], 0); ?>)</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: var(--bg-primary); border-radius: 3px; overflow: hidden;">
                        <?php 
                        $totalCountLeads = array_sum(array_column($stageStats, 'count'));
                        $percent = $totalCountLeads > 0 ? ($st['count'] / $totalCountLeads) * 100 : 0;
                        ?>
                        <div style="width: <?php echo $percent; ?>%; height: 100%; background: var(--accent); border-radius: 3px;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 24px; margin-bottom: 24px;">
    <!-- Sales Staff Leaderboard -->
    <div class="glass-panel" style="padding: 24px;">
        <h3 class="chart-title" style="margin-bottom: 20px;"><i class="fa-solid fa-trophy" style="color: var(--warning); margin-right: 6px;"></i> Sales Representative Performance</h3>
        <div class="table-responsive">
            <table class="crm-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Sales Executive</th>
                        <th>Assigned Leads</th>
                        <th>Conversions</th>
                        <th>Close Ratio</th>
                        <th>Revenue Closed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staffStats as $staff): 
                        $ratio = $staff['assigned_leads'] > 0 ? ($staff['converted_count'] / $staff['assigned_leads']) * 100 : 0;
                    ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($staff['name']); ?></td>
                            <td><?php echo $staff['assigned_leads']; ?></td>
                            <td><?php echo $staff['converted_count']; ?></td>
                            <td><?php echo number_format($ratio, 1); ?>%</td>
                            <td style="font-weight: 700; color: var(--success);"><?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($staff['total_sales'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lead Source Conversion Metrics -->
    <div class="glass-panel" style="padding: 24px;">
        <h3 class="chart-title" style="margin-bottom: 20px;"><i class="fa-solid fa-filter-list" style="color: var(--accent); margin-right: 6px;"></i> Lead Source ROI</h3>
        <div class="table-responsive">
            <table class="crm-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Lead Source</th>
                        <th>Total Inquiries</th>
                        <th>Converted Customers</th>
                        <th>Conversion Rate</th>
                        <th>Pipeline Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sourceStats as $src): 
                        $srcRatio = $src['count'] > 0 ? ($src['converted_count'] / $src['count']) * 100 : 0;
                    ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($src['source']); ?></td>
                            <td><?php echo $src['count']; ?></td>
                            <td><?php echo $src['converted_count']; ?></td>
                            <td><?php echo number_format($srcRatio, 1); ?>%</td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($src['expected_value'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="assets/js/charts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Render yearly revenue trend line
    renderRevenueChart(
        'yearlySalesChart',
        <?php echo json_encode($monthsLabels); ?>,
        <?php echo json_encode($salesValues); ?>
    );
});
</script>
