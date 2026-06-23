<?php
// src/Controllers/LeadController.php

namespace App\Controllers;

use App\Repositories\LeadRepository;
use App\Repositories\UserRepository;
use App\Repositories\LeadNoteRepository;
use App\Repositories\LeadActivityRepository;
use App\Repositories\FollowupRepository;
use App\Services\LeadService;
use App\Services\CustomerService;
use App\Services\SettingService;
use App\Core\Request;
use App\Core\Session;
use App\Core\Response;

class LeadController extends Controller {
    private LeadRepository $leadRepo;
    private UserRepository $userRepo;
    private LeadNoteRepository $noteRepo;
    private LeadActivityRepository $activityRepo;
    private FollowupRepository $followRepo;
    private LeadService $leadService;
    private CustomerService $custService;
    private SettingService $settingService;

    public function __construct() {
        $this->leadRepo = new LeadRepository();
        $this->userRepo = new UserRepository();
        $this->noteRepo = new LeadNoteRepository();
        $this->activityRepo = new LeadActivityRepository();
        $this->followRepo = new FollowupRepository();
        $this->leadService = new LeadService();
        $this->custService = new CustomerService();
        $this->settingService = new SettingService();
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
        $priority = $request->get('priority', '');

        $assignedTo = $isAdmin ? null : $userId;

        try {
            $totalRecords = $this->leadRepo->getLeadsCount($search, $status, $priority, $assignedTo);
            $totalPages = (int)ceil($totalRecords / $limit);
            $leads = $this->leadRepo->getLeads($search, $status, $priority, $assignedTo, $limit, $offset);
            
            // Get active users list for assignment dropdown
            $users = $this->userRepo->getActiveUsersList();

            $db = \App\Core\Database::getConnection();
            $stmt = $db->query("SELECT value_value FROM settings WHERE key_name = 'currency_symbol'");
            $currencySymbol = $stmt->fetchColumn() ?: '₹';

            $this->render('leads', [
                'leads' => $leads,
                'users' => $users,
                'totalRecords' => $totalRecords,
                'totalPages' => $totalPages,
                'page' => $page,
                'search' => $search,
                'status' => $status,
                'priority' => $priority,
                'currencySymbol' => $currencySymbol,
                'isAdmin' => $isAdmin,
                'userId' => $userId
            ]);
        } catch (\Exception $e) {
            echo '<div class="glass-panel" style="padding: 24px; color: var(--danger);">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    public function detail(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');

        $request = new Request();
        $leadId = (int)$request->get('id', 0);

        if ($leadId <= 0) {
            $this->renderDetailError("Invalid Lead ID.");
            return;
        }

        try {
            $lead = $this->leadRepo->findById($leadId);
            if (!$lead) {
                $this->renderDetailError("Lead not found or has been deleted.");
                return;
            }

            if (!$isAdmin && $lead->assigned_to !== $userId) {
                $this->renderDetailError("Unauthorized. You do not have permissions to access this lead file.");
                return;
            }

            $notes = $this->noteRepo->getLeadNotes($leadId);
            $followups = $this->followRepo->getLeadFollowups($leadId);
            $activities = $this->activityRepo->getLeadActivities($leadId);
            $users = $this->userRepo->getActiveUsersList();

            $db = \App\Core\Database::getConnection();
            $stmt = $db->query("SELECT value_value FROM settings WHERE key_name = 'currency_symbol'");
            $currencySymbol = $stmt->fetchColumn() ?: '₹';

            $this->render('lead-detail', [
                'lead' => $lead,
                'leadId' => $leadId,
                'notes' => $notes,
                'followups' => $followups,
                'activities' => $activities,
                'users' => $users,
                'currencySymbol' => $currencySymbol,
                'isAdmin' => $isAdmin,
                'userId' => $userId
            ]);
        } catch (\Exception $e) {
            $this->renderDetailError("Database Error: " . $e->getMessage());
        }
    }

    private function renderDetailError(string $message): void {
        echo '<div class="glass-panel" style="padding: 24px; color: var(--danger); text-align: center;">' . htmlspecialchars($message) . ' <a href="leads.php" style="color: var(--accent);">Go back to database</a></div>';
        include __DIR__ . '/../../includes/footer.php';
        exit();
    }

    public function handleAPI(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');

        $request = new Request();

        if ($request->isGet()) {
            $action = $request->get('action', '');
            if ($action === 'export') {
                $this->exportCSV($request, $userId, $isAdmin);
            }
            return;
        }

        if ($request->isPost()) {
            $inputs = $request->all();
            $action = $inputs['action'] ?? '';

            if (!$request->validateCSRF()) {
                $this->json(['success' => false, 'message' => 'CSRF verification failed.']);
                return;
            }

            switch ($action) {
                case 'create':
                    $res = $this->leadService->createLead($inputs, $userId);
                    $this->json($res);
                    break;

                case 'update':
                    $id = (int)($inputs['id'] ?? 0);
                    $res = $this->leadService->updateLead($id, $inputs, $userId, $isAdmin);
                    $this->json($res);
                    break;

                case 'delete':
                    $id = (int)($inputs['id'] ?? 0);
                    $res = $this->leadService->deleteLead($id, $userId, $isAdmin);
                    $this->json($res);
                    break;

                case 'update_status':
                    $id = (int)($inputs['id'] ?? 0);
                    $status = $inputs['status'] ?? '';
                    $res = $this->leadService->updateStatus($id, $status, $userId, $isAdmin);
                    $this->json($res);
                    break;

                case 'transfer':
                    $id = (int)($inputs['id'] ?? 0);
                    $assignedTo = !empty($inputs['assigned_to']) ? (int)$inputs['assigned_to'] : null;
                    $res = $this->leadService->transferLead($id, $assignedTo, $userId, $isAdmin);
                    $this->json($res);
                    break;

                case 'convert_to_customer':
                    $leadId = (int)($inputs['lead_id'] ?? 0);
                    $totalPurchases = (float)($inputs['total_purchases'] ?? 0.00);
                    $res = $this->leadService->convertToCustomer($leadId, $totalPurchases, $userId, $isAdmin);
                    $this->json($res);
                    break;

                case 'record_purchase':
                    $id = (int)($inputs['id'] ?? 0);
                    $dealValue = (float)($inputs['deal_value'] ?? 0.00);
                    $status = $inputs['status'] ?? 'active';
                    $res = $this->custService->recordPurchase($id, $dealValue, $status, $userId);
                    $this->json($res);
                    break;

                case 'create_user':
                    $res = $this->settingService->createUser($inputs, $userId, $isAdmin);
                    $this->json($res);
                    break;

                case 'toggle_user_status':
                    $id = (int)($inputs['id'] ?? 0);
                    $status = $inputs['status'] ?? 'active';
                    $res = $this->settingService->toggleUserStatus($id, $status, $userId, $isAdmin);
                    $this->json($res);
                    break;

                default:
                    $this->json(['success' => false, 'message' => 'Action not recognized.']);
                    break;
            }
        }
    }

    public function import(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');

        $request = new Request();

        if ($request->isGet()) {
            $action = $request->get('action', '');
            if ($action === 'template') {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="crm_leads_import_template.csv"');
                
                $output = fopen('php://output', 'w');
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
                
                fputcsv($output, ['Name', 'Phone', 'Email', 'Company', 'Industry', 'Source', 'Priority', 'Expected Value']);
                fputcsv($output, ['John Smith', '+15550239', 'john.smith@example.com', 'Smith Consulting', 'Consulting', 'Google Adwords', 'high', '15000.00']);
                fputcsv($output, ['Alice Watson', '+15551928', 'alice@watsoncorp.io', 'Watson Solutions', 'Technology', 'Website Form', 'medium', '6500.00']);
                fclose($output);
                exit();
            }
            return;
        }

        if ($request->isPost()) {
            if (!$request->validateCSRF()) {
                $this->json(['success' => false, 'message' => 'CSRF validation failed.']);
                return;
            }

            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $this->json(['success' => false, 'message' => 'Invalid or missing file upload.']);
                return;
            }

            $fileTmpPath = $_FILES['csv_file']['tmp_name'];
            $fileName = $_FILES['csv_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension !== 'csv') {
                $this->json(['success' => false, 'message' => 'Uploaded file must be a standard CSV sheet.']);
                return;
            }

            $res = $this->leadService->importCSV($fileTmpPath, $userId, $isAdmin);
            if ($res['success']) {
                $this->json(['success' => true, 'imported_count' => $res['inserted']]);
            } else {
                $this->json($res);
            }
        }
    }

    public function handleNoteAPI(): void {
        $user = $this->requireLogin();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'Admin');

        $request = new Request();

        if ($request->isPost()) {
            $inputs = $request->all();
            $action = $inputs['action'] ?? '';

            if (!$request->validateCSRF()) {
                $this->json(['success' => false, 'message' => 'Security token invalid.']);
                return;
            }

            if ($action === 'create') {
                $leadId = (int)($inputs['lead_id'] ?? 0);
                $note = trim($inputs['note'] ?? '');
                $isInternal = isset($inputs['is_internal']) ? (int)$inputs['is_internal'] : 1;

                if ($leadId <= 0 || empty($note)) {
                    $this->json(['success' => false, 'message' => 'Note contents cannot be empty.']);
                    return;
                }

                try {
                    $lead = $this->leadRepo->findById($leadId);
                    if (!$lead) {
                        $this->json(['success' => false, 'message' => 'Lead not found.']);
                        return;
                    }

                    if (!$isAdmin && $lead->assigned_to !== $userId) {
                        $this->json(['success' => false, 'message' => 'Restricted workspace access.']);
                        return;
                    }

                    $newNoteId = $this->noteRepo->create($leadId, $userId, $note, $isInternal);
                    
                    $db = \App\Core\Database::getConnection();
                    logAudit($db, $userId, 'add_note', 'lead_notes', $newNoteId, null, ['lead_id' => $leadId, 'is_internal' => $isInternal]);
                    logLeadActivity($db, $leadId, $userId, 'note_added', "Added a new " . ($isInternal ? 'internal staff' : 'public') . " note summary.");

                    $this->json(['success' => true]);
                } catch (\Exception $e) {
                    $this->json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
                return;
            }
        }

        $this->json(['success' => false, 'message' => 'Method or Action not recognized.']);
    }

    private function exportCSV(Request $request, int $userId, bool $isAdmin): void {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');
        $priority = $request->get('priority', '');
        
        $assignedTo = $isAdmin ? null : $userId;

        try {
            $results = $this->leadRepo->getLeadsForExport($search, $status, $priority, $assignedTo);

            // Clean headers for download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="crm_leads_export_' . date('Ymd_His') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

            fputcsv($output, ['Lead ID', 'Name', 'Phone', 'Email', 'Company', 'Industry', 'Source', 'Priority', 'Status', 'Expected Value', 'Assigned Executive', 'Creation Date']);
            
            foreach ($results as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['phone'],
                    $row['email'],
                    $row['company'],
                    $row['industry'],
                    $row['source'],
                    ucfirst($row['priority']),
                    str_replace('_', ' ', $row['status']),
                    $row['expected_value'],
                    $row['assigned_name'] ?: 'Unassigned',
                    $row['created_at']
                ]);
            }
            fclose($output);
            exit();
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
            exit();
        }
    }
}
