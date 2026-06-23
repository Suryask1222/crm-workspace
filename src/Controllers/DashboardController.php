<?php
// src/Controllers/DashboardController.php

namespace App\Controllers;

use App\Repositories\LeadRepository;
use App\Repositories\FollowupRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\TaskRepository;
use App\Repositories\LeadActivityRepository;
use App\Core\Session;

class DashboardController extends Controller {
    private LeadRepository $leadRepo;
    private FollowupRepository $followRepo;
    private CustomerRepository $custRepo;
    private TaskRepository $taskRepo;
    private LeadActivityRepository $activityRepo;

    public function __construct() {
        $this->leadRepo = new LeadRepository();
        $this->followRepo = new FollowupRepository();
        $this->custRepo = new CustomerRepository();
        $this->taskRepo = new TaskRepository();
        $this->activityRepo = new LeadActivityRepository();
    }

    public function index(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');
        
        $assignedTo = $isAdmin ? null : $userId;

        try {
            // 1. Metric Calculations
            $totalLeads = $this->leadRepo->getLeadCountTotal($assignedTo);
            $newLeads = $this->leadRepo->getLeadCountByStatus('new', $assignedTo);
            $followupsToday = $this->followRepo->getFollowupsCountToday($assignedTo);
            $convertedLeads = $this->leadRepo->getLeadCountByStatus('converted', $assignedTo);
            $revenueGenerated = $this->custRepo->getTotalRevenue($assignedTo);

            // 2. Fetch Recent Activities (Limit 5)
            $recentActivities = $this->activityRepo->getRecentActivities($assignedTo, 5);

            // 3. Fetch Upcoming Follow-ups (Limit 5)
            $upcomingFollowups = $this->followRepo->getUpcomingFollowups($assignedTo, 5);

            // 4. Fetch Active Tasks (Limit 5)
            $activeTasks = $this->taskRepo->getTasksByStatus('todo', $assignedTo, 5);
            $inProgressTasks = $this->taskRepo->getTasksByStatus('in_progress', $assignedTo, 5);
            $combinedTasks = array_merge($activeTasks, $inProgressTasks);
            // Sort by due date
            usort($combinedTasks, function($a, $b) {
                if (!$a->due_date) return 1;
                if (!$b->due_date) return -1;
                return strcmp($a->due_date, $b->due_date);
            });
            $combinedTasks = array_slice($combinedTasks, 0, 5);

            // 5. Gather Chart Data
            // Lead status funnel
            $statuses = ['new', 'contacted', 'follow_up', 'qualified', 'proposal_sent', 'negotiation', 'converted', 'lost'];
            $funnelData = [];
            foreach ($statuses as $st) {
                $funnelData[] = $this->leadRepo->getLeadCountByStatus($st, $assignedTo);
            }

            // Lead Sources
            $sources = $this->leadRepo->getCountsBySource($assignedTo);
            $sourceLabels = array_column($sources, 'source');
            $sourceCounts = array_map('intval', array_column($sources, 'count'));

            // Monthly Revenue Growth (last 6 months) - Optimized Single Database Call
            $revenueGrowth = $this->custRepo->getMonthlyRevenueForLast6Months($assignedTo);
            $months = array_column($revenueGrowth, 'label');
            $monthlyRevenue = array_column($revenueGrowth, 'revenue');

            // Currency Symbol
            $db = \App\Core\Database::getConnection();
            $stmt = $db->query("SELECT value_value FROM settings WHERE key_name = 'currency_symbol'");
            $currencySymbol = $stmt->fetchColumn() ?: '₹';

            $this->render('dashboard', [
                'totalLeads' => $totalLeads,
                'newLeads' => $newLeads,
                'followupsToday' => $followupsToday,
                'convertedLeads' => $convertedLeads,
                'revenueGenerated' => $revenueGenerated,
                'recentActivities' => $recentActivities,
                'upcomingFollowups' => $upcomingFollowups,
                'activeTasks' => $combinedTasks,
                'funnelData' => $funnelData,
                'sourceLabels' => $sourceLabels,
                'sourceCounts' => $sourceCounts,
                'months' => $months,
                'monthlyRevenue' => $monthlyRevenue,
                'currencySymbol' => $currencySymbol,
                'isAdmin' => $isAdmin
            ]);
        } catch (\Exception $e) {
            echo '<div class="glass-panel" style="padding: 24px; color: var(--danger);">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    public function apiStats(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');
        
        $assignedTo = $isAdmin ? null : $userId;

        try {
            $statusBreakdown = $this->leadRepo->getCountsByStatus($assignedTo);
            $sourceBreakdown = $this->leadRepo->getCountsBySource($assignedTo);

            $this->json([
                'success' => true,
                'status_breakdown' => $statusBreakdown,
                'source_breakdown' => $sourceBreakdown,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Query error: ' . $e->getMessage()]);
        }
    }
}
