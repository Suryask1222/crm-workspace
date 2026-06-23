<?php
// src/Controllers/CustomerController.php

namespace App\Controllers;

use App\Repositories\CustomerRepository;
use App\Core\Request;
use App\Core\Session;

class CustomerController extends Controller {
    private CustomerRepository $custRepo;

    public function __construct() {
        $this->custRepo = new CustomerRepository();
    }

    public function index(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');

        $request = new Request();
        $limit = 10;
        $page = max(1, (int)$request->get('page', 1));
        $offset = ($page - 1) * $limit;

        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');

        $assignedTo = $isAdmin ? null : $userId;

        try {
            $totalRecords = $this->custRepo->getCustomersCount($search, $status, $assignedTo);
            $totalPages = (int)ceil($totalRecords / $limit);
            $customers = $this->custRepo->getCustomers($search, $status, $assignedTo, $limit, $offset);

            $db = \App\Core\Database::getConnection();
            $stmt = $db->query("SELECT value_value FROM settings WHERE key_name = 'currency_symbol'");
            $currencySymbol = $stmt->fetchColumn() ?: '₹';

            $this->render('customers', [
                'customers' => $customers,
                'totalRecords' => $totalRecords,
                'totalPages' => $totalPages,
                'page' => $page,
                'search' => $search,
                'status' => $status,
                'currencySymbol' => $currencySymbol,
                'isAdmin' => $isAdmin,
                'userId' => $userId
            ]);
        } catch (\Exception $e) {
            echo '<div class="glass-panel" style="padding: 24px; color: var(--danger);">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
