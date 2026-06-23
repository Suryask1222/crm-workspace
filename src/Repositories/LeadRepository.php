<?php
// src/Repositories/LeadRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Lead;
use PDO;

class LeadRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    private function buildWhereClause(string $search, string $status, string $priority, ?int $assignedTo): array {
        $where = [];
        $params = [];

        if ($assignedTo !== null) {
            $where[] = "l.assigned_to = ?";
            $params[] = $assignedTo;
        }
        if ($search !== '') {
            $where[] = "(l.name LIKE ? OR l.company LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
            $wildcard = "%{$search}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }
        if ($status !== '') {
            $where[] = "l.status = ?";
            $params[] = $status;
        }
        if ($priority !== '') {
            $where[] = "l.priority = ?";
            $params[] = $priority;
        }

        $whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        return [$whereSQL, $params];
    }

    public function getLeadsCount(string $search, string $status, string $priority, ?int $assignedTo): int {
        list($whereSQL, $params) = $this->buildWhereClause($search, $status, $priority, $assignedTo);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads l {$whereSQL}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getLeads(string $search, string $status, string $priority, ?int $assignedTo, int $limit, int $offset): array {
        list($whereSQL, $params) = $this->buildWhereClause($search, $status, $priority, $assignedTo);
        $sql = "
            SELECT l.*, u.name AS assigned_name 
            FROM leads l
            LEFT JOIN users u ON l.assigned_to = u.id
            {$whereSQL}
            ORDER BY l.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $leads = [];
        while ($row = $stmt->fetch()) {
            $leads[] = $this->mapRowToModel($row);
        }
        return $leads;
    }

    public function getLeadsForExport(string $search, string $status, string $priority, ?int $assignedTo): array {
        list($whereSQL, $params) = $this->buildWhereClause($search, $status, $priority, $assignedTo);
        $stmt = $this->db->prepare("
            SELECT l.id, l.name, l.phone, l.email, l.company, l.industry, l.source, l.priority, l.status, l.expected_value, l.created_at, u.name AS assigned_name
            FROM leads l
            LEFT JOIN users u ON l.assigned_to = u.id
            {$whereSQL}
            ORDER BY l.created_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?Lead {
        $stmt = $this->db->prepare("
            SELECT l.*, u.name AS assigned_name, u.email AS assigned_email 
            FROM leads l
            LEFT JOIN users u ON l.assigned_to = u.id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->mapRowToModel($row);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO leads (name, phone, email, company, industry, address, source, priority, status, assigned_to, expected_value) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['company'] ?? null,
            $data['industry'] ?? null,
            $data['address'] ?? null,
            $data['source'] ?? null,
            $data['priority'] ?? 'medium',
            $data['status'] ?? 'new',
            $data['assigned_to'] ?? null,
            $data['expected_value'] ?? 0.00
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE leads 
            SET name = ?, phone = ?, email = ?, company = ?, industry = ?, address = ?, source = ?, priority = ?, expected_value = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['email'],
            $data['company'],
            $data['industry'],
            $data['address'],
            $data['source'],
            $data['priority'],
            $data['expected_value'],
            $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM leads WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE leads SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function transfer(int $id, ?int $assignedTo): bool {
        $stmt = $this->db->prepare("UPDATE leads SET assigned_to = ? WHERE id = ?");
        return $stmt->execute([$assignedTo, $id]);
    }

    public function getLeadCountTotal(?int $assignedTo): int {
        if ($assignedTo !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to = ?");
            $stmt->execute([$assignedTo]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM leads");
        }
        return (int)$stmt->fetchColumn();
    }

    public function getLeadCountByStatus(string $status, ?int $assignedTo): int {
        if ($assignedTo !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to = ? AND status = ?");
            $stmt->execute([$assignedTo, $status]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE status = ?");
            $stmt->execute([$status]);
        }
        return (int)$stmt->fetchColumn();
    }

    public function getCountsByStatus(?int $assignedTo): array {
        if ($assignedTo !== null) {
            $stmt = $this->db->prepare("SELECT status, COUNT(*) AS count FROM leads WHERE assigned_to = ? GROUP BY status");
            $stmt->execute([$assignedTo]);
        } else {
            $stmt = $this->db->query("SELECT status, COUNT(*) AS count FROM leads GROUP BY status");
        }
        return $stmt->fetchAll();
    }

    public function getCountsBySource(?int $assignedTo): array {
        if ($assignedTo !== null) {
            $stmt = $this->db->prepare("SELECT source, COUNT(*) AS count FROM leads WHERE assigned_to = ? AND source IS NOT NULL AND source != '' GROUP BY source");
            $stmt->execute([$assignedTo]);
        } else {
            $stmt = $this->db->query("SELECT source, COUNT(*) AS count FROM leads WHERE source IS NOT NULL AND source != '' GROUP BY source");
        }
        return $stmt->fetchAll();
    }

    public function getStageStats(): array {
        $stmt = $this->db->query("
            SELECT status, COUNT(*) AS count, SUM(expected_value) AS total_val 
            FROM leads 
            GROUP BY status
        ");
        return $stmt->fetchAll();
    }

    public function getSourceStats(): array {
        $stmt = $this->db->query("
            SELECT source, COUNT(*) AS count, 
                   SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) AS converted_count,
                   SUM(expected_value) AS expected_value
            FROM leads 
            GROUP BY source
            HAVING source IS NOT NULL AND source != ''
        ");
        return $stmt->fetchAll();
    }

    private function mapRowToModel(array $row): Lead {
        $lead = new Lead();
        $lead->id = (int)$row['id'];
        $lead->name = $row['name'];
        $lead->phone = $row['phone'] ?? null;
        $lead->email = $row['email'] ?? null;
        $lead->company = $row['company'] ?? null;
        $lead->industry = $row['industry'] ?? null;
        $lead->address = $row['address'] ?? null;
        $lead->source = $row['source'] ?? null;
        $lead->priority = $row['priority'];
        $lead->status = $row['status'];
        $lead->assigned_to = $row['assigned_to'] !== null ? (int)$row['assigned_to'] : null;
        $lead->expected_value = (float)$row['expected_value'];
        $lead->created_at = $row['created_at'];
        $lead->updated_at = $row['updated_at'];
        $lead->assigned_name = $row['assigned_name'] ?? null;
        $lead->assigned_email = $row['assigned_email'] ?? null;
        return $lead;
    }
}
