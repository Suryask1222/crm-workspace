<?php
// src/Controllers/ReportController.php

namespace App\Controllers;

use App\Repositories\LeadRepository;
use App\Repositories\CustomerRepository;
use App\Core\Session;

class ReportController extends Controller {
    private LeadRepository $leadRepo;
    private CustomerRepository $custRepo;

    public function __construct() {
        $this->leadRepo = new LeadRepository();
        $this->custRepo = new CustomerRepository();
    }

    public function index(): void {
        $this->requireAdmin();

        try {
            $stageStats = $this->leadRepo->getStageStats();
            $sourceStats = $this->leadRepo->getSourceStats();
            $staffStats = $this->custRepo->getLeaderboard();

            $year = date('Y');
            $monthlyStats = $this->custRepo->getYearlySalesStats($year);

            $monthsLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $salesValues = array_fill(0, 12, 0.00);
            foreach ($monthlyStats as $row) {
                $salesValues[$row['month'] - 1] = (float)$row['total_sales'];
            }

            $db = \App\Core\Database::getConnection();
            $stmt = $db->query("SELECT value_value FROM settings WHERE key_name = 'currency_symbol'");
            $currencySymbol = $stmt->fetchColumn() ?: '₹';

            $this->render('reports', [
                'stageStats' => $stageStats,
                'sourceStats' => $sourceStats,
                'staffStats' => $staffStats,
                'monthsLabels' => $monthsLabels,
                'salesValues' => $salesValues,
                'year' => $year,
                'currencySymbol' => $currencySymbol
            ]);
        } catch (\Exception $e) {
            echo '<div class="glass-panel" style="padding: 24px; color: var(--danger);">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
