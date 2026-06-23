<?php
// src/Repositories/CustomerRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Customer;
use PDO;

class CustomerRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    private function buildWhereClause(string $search, string $status, ?int $assignedTo): array {
        $where = [];
        $params = [];

        if ($assignedTo !== null) {
            $where[] = "(l.assigned_to = ? OR c.lead_id IS NULL)";
            $params[] = $assignedTo;
        }
        if ($search !== '') {
            $where[] = "(c.name LIKE ? OR c.company LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
            $wildcard = "%{$search}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }
        if ($status !== '') {
            $where[] = "c.status = ?";
            $params[] = $status;
        }

        $whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        return [$whereSQL, $params];
    }

    public function getCustomersCount(string $search, string $status, ?int $assignedTo): int {
        list($whereSQL, $params) = $this->buildWhereClause($search, $status, $assignedTo);
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT c.id) 
            FROM customers c
            LEFT JOIN leads l ON c.lead_id = l.id
            {$whereSQL}
        ");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getCustomers(string $search, string $status, ?int $assignedTo, int $limit, int $offset): array {
        list($whereSQL, $params) = $this->buildWhereClause($search, $status, $assignedTo);
        $sql = "
            SELECT c.*, l.assigned_to, u.name AS assigned_name
            FROM customers c
            LEFT JOIN leads l ON c.lead_id = l.id
            LEFT JOIN users u ON l.assigned_to = u.id
            {$whereSQL}
            ORDER BY c.total_purchases DESC, c.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $customers = [];
        while ($row = $stmt->fetch()) {
            $customers[] = $this->mapRowToModel($row);
        }
        return $customers;
    }

    public function findById(int $id): ?Customer {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->mapRowToModel($row);
    }

    public function createFromLead(int $leadId, string $name, ?string $phone, ?string $email, ?string $company, float $totalPurchases): int {
        $stmt = $this->db->prepare("
            INSERT INTO customers (lead_id, name, phone, email, company, total_purchases, purchase_count, status) 
            VALUES (?, ?, ?, ?, ?, ?, 1, 'active')
        ");
        $stmt->execute([$leadId, $name, $phone, $email, $company, $totalPurchases]);
        return (int)$this->db->lastInsertId();
    }

    public function recordPurchase(int $id, float $dealValue, string $status): bool {
        $stmt = $this->db->prepare("UPDATE customers SET total_purchases = total_purchases + ?, purchase_count = purchase_count + 1, status = ? WHERE id = ?");
        return $stmt->execute([$dealValue, $status, $id]);
    }

    public function getTotalRevenue(?int $assignedTo): float {
        if ($assignedTo !== null) {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(c.total_purchases), 0) 
                FROM customers c
                JOIN leads l ON c.lead_id = l.id
                WHERE l.assigned_to = ? AND c.status = 'active'
            ");
            $stmt->execute([$assignedTo]);
        } else {
            $stmt = $this->db->query("SELECT COALESCE(SUM(total_purchases), 0) FROM customers WHERE status = 'active'");
        }
        return (float)$stmt->fetchColumn();
    }

    /**
     * Optimized single database query to fetch last 6 months total revenue trends
     * Resolves the legacy procedural N+1 query loop.
     */
    public function getMonthlyRevenueForLast6Months(?int $assignedTo): array {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-$i months"));
            $monthLabel = date('M Y', strtotime("-$i months"));
            $months[$monthKey] = [
                'label' => $monthLabel,
                'revenue' => 0.0
            ];
        }

        $startDate = date('Y-m-01 00:00:00', strtotime("-5 months"));

        if ($assignedTo !== null) {
            $sql = "
                SELECT DATE_FORMAT(c.created_at, '%Y-%m') AS month_key, SUM(c.total_purchases) AS total
                FROM customers c
                JOIN leads l ON c.lead_id = l.id
                WHERE l.assigned_to = ? AND c.created_at >= ? AND c.status = 'active'
                GROUP BY month_key
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$assignedTo, $startDate]);
        } else {
            $sql = "
                SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, SUM(total_purchases) AS total
                FROM customers
                WHERE created_at >= ? AND status = 'active'
                GROUP BY month_key
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate]);
        }

        while ($row = $stmt->fetch()) {
            $key = $row['month_key'];
            if (isset($months[$key])) {
                $months[$key]['revenue'] = (float)$row['total'];
            }
        }

        return array_values($months);
    }

    public function getYearlySalesStats(int $year): array {
        $stmt = $this->db->prepare("
            SELECT MONTH(created_at) AS month, SUM(total_purchases) AS total_sales
            FROM customers
            WHERE YEAR(created_at) = ? AND status = 'active'
            GROUP BY MONTH(created_at)
            ORDER BY month ASC
        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }

    public function getLeaderboard(): array {
        $stmt = $this->db->query("
            SELECT u.id, u.name, 
                   COUNT(l.id) AS assigned_leads,
                   SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) AS converted_count,
                   COALESCE(SUM(c.total_purchases), 0) AS total_sales
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to
            LEFT JOIN customers c ON l.id = c.lead_id AND c.status = 'active'
            WHERE u.role_id = (SELECT id FROM roles WHERE name = 'Staff')
            GROUP BY u.id, u.name
            ORDER BY total_sales DESC
        ");
        return $stmt->fetchAll();
    }

    private function mapRowToModel(array $row): Customer {
        $c = new Customer();
        $c->id = (int)$row['id'];
        $c->lead_id = $row['lead_id'] !== null ? (int)$row['lead_id'] : null;
        $c->name = $row['name'];
        $c->phone = $row['phone'] ?? null;
        $c->email = $row['email'] ?? null;
        $c->company = $row['company'] ?? null;
        $c->total_purchases = (float)$row['total_purchases'];
        $c->purchase_count = (int)$row['purchase_count'];
        $c->status = $row['status'];
        $c->created_at = $row['created_at'];
        $c->assigned_name = $row['assigned_name'] ?? null;
        return $c;
    }
}
