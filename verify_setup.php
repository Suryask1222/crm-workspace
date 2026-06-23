<?php
// verify_setup.php

header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';

echo "=== Enterprise CRM Setup & Verification Utility ===\n\n";

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'crm_system';

// If credentials file already exists, load it
if (file_exists(DB_CREDENTIALS_FILE)) {
    $creds = include DB_CREDENTIALS_FILE;
    if (is_array($creds)) {
        $host = $creds['host'] ?? $host;
        $dbname = $creds['dbname'] ?? $dbname;
        $username = $creds['username'] ?? $username;
        $password = $creds['password'] ?? $password;
    }
}

echo "1. Checking MySQL Connection... ";
try {
    // Connect without DB first to create database
    $pdo = new PDO("mysql:host={$host}", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "\n[HELP] Please check if MySQL server is running on {$host} and username/password are correct.\n";
    exit(1);
}

echo "2. Creating Database '{$dbname}' if not exists... ";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Reconnect with the selected database
echo "3. Reconnecting to database '{$dbname}'... ";
try {
    $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "4. Executing database.sql schema... ";
$sqlFile = __DIR__ . '/database.sql';
if (!file_exists($sqlFile)) {
    echo "FAILED (database.sql not found at {$sqlFile})\n";
    exit(1);
}

try {
    $sql = file_get_contents($sqlFile);
    // Execute multiple statements
    $db->exec($sql);
    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "5. Seeding Roles & Permissions... ";
try {
    // Seed Roles
    $db->exec("INSERT INTO roles (name, description) VALUES 
        ('Admin', 'Super Admin with full access'),
        ('Staff', 'Sales Executive / Staff')
        ON DUPLICATE KEY UPDATE name=name");

    // Get role IDs
    $adminRoleId = $db->query("SELECT id FROM roles WHERE name = 'Admin'")->fetchColumn();
    $staffRoleId = $db->query("SELECT id FROM roles WHERE name = 'Staff'")->fetchColumn();

    // Seed Permissions
    $permissions = [
        ['manage_users', 'Ability to create, update, delete users'],
        ['view_all_leads', 'View all leads in CRM'],
        ['edit_all_leads', 'Edit details of all leads'],
        ['delete_leads', 'Delete leads from CRM'],
        ['view_reports', 'View visual analytics and reports'],
        ['manage_settings', 'Modify CRM configuration settings']
    ];
    $stmtPerm = $db->prepare("INSERT INTO permissions (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=name");
    foreach ($permissions as $p) {
        $stmtPerm->execute($p);
    }

    // Link Permissions to Admin (All)
    $db->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id) 
               SELECT $adminRoleId, id FROM permissions");

    // Link Permissions to Staff (view_all_leads - let's make it partial, or just view_all_leads if they want, but let's give them what they need)
    $staffPerms = ['view_all_leads'];
    $stmtStaffPerm = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) 
                                   SELECT $staffRoleId, id FROM permissions WHERE name = ?");
    foreach ($staffPerms as $sp) {
        $stmtStaffPerm->execute([$sp]);
    }

    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "6. Seeding Default Users... ";
try {
    $adminPassword = password_hash('Admin123!', PASSWORD_DEFAULT);
    $salesPassword = password_hash('Sales123!', PASSWORD_DEFAULT);

    $stmtUser = $db->prepare("INSERT INTO users (role_id, name, email, password, status) VALUES (?, ?, ?, ?, 'active') ON DUPLICATE KEY UPDATE email=email");
    $stmtUser->execute([$adminRoleId, 'Super Admin', 'admin@crm.com', $adminPassword]);
    $stmtUser->execute([$staffRoleId, 'John Sales Executive', 'sales@crm.com', $salesPassword]);

    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "7. Seeding CRM Settings... ";
try {
    $settings = [
        ['company_name', 'Nexentora Technologies'],
        ['currency', 'INR'],
        ['currency_symbol', '₹'],
        ['timezone', 'UTC'],
        ['smtp_host', 'smtp.mailtrap.io'],
        ['smtp_port', '2525'],
        ['smtp_user', 'placeholder_user'],
        ['smtp_pass', 'placeholder_pass'],
        ['whatsapp_api_url', 'https://api.whatsapp.com/send'],
        ['sms_api_key', 'placeholder_sms_key']
    ];
    $stmtSet = $db->prepare("INSERT INTO settings (key_name, value_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_name=key_name");
    foreach ($settings as $s) {
        $stmtSet->execute($s);
    }
    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "8. Seeding Demo Leads, Tasks, Follow-ups, and Activities... ";
try {
    // Clear leads to avoid duplicate keys in seeding
    $db->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE leads; TRUNCATE TABLE lead_notes; TRUNCATE TABLE lead_activities; TRUNCATE TABLE followups; TRUNCATE TABLE tasks; TRUNCATE TABLE customers; SET FOREIGN_KEY_CHECKS = 1;");

    $adminId = $db->query("SELECT id FROM users WHERE email = 'admin@crm.com'")->fetchColumn();
    $salesId = $db->query("SELECT id FROM users WHERE email = 'sales@crm.com'")->fetchColumn();

    // Insert Leads
    $leads = [
        ['Alice Green', '+15550199', 'alice@greeninc.com', 'Green Energy Solutions', 'Energy', '101 Eco Dr, Austin, TX', 'Website Query', 'high', 'new', $adminId, 45000.00],
        ['Bob Miller', '+15550233', 'bob@millertech.io', 'Miller Tech Group', 'Technology', '404 Cyber Way, San Francisco, CA', 'Referral', 'medium', 'contacted', $salesId, 12000.00],
        ['Charlie Brown', '+15550477', 'charlie@brownlegal.com', 'Brown & Partners', 'Legal', '88 Justice Ave, Boston, MA', 'Cold Call', 'low', 'follow_up', $salesId, 8500.00],
        ['Diana Prince', '+15550788', 'diana@amazonindustries.com', 'Amazonia Logistics', 'Logistics', '50 Temples St, Washington, DC', 'LinkedIn', 'high', 'qualified', $salesId, 95000.00],
        ['Edward Stark', '+15550999', 'ned@winterfellcorp.com', 'Winterfell Metal Works', 'Manufacturing', '1 Great Keep Road, Seattle, WA', 'Trade Show', 'high', 'proposal_sent', $adminId, 150000.00],
        ['Fiona Gallagher', '+15551122', 'fiona@southsidepub.com', 'Southside Hospitality', 'Food & Beverage', '2119 N Damen Ave, Chicago, IL', 'Google Search', 'medium', 'negotiation', $salesId, 5000.00],
        ['George Wayne', '+15552233', 'george@wayneenterprises.com', 'Wayne Holdings', 'Finance', '1007 Mountain Drive, Gotham', 'Partner', 'high', 'converted', $adminId, 250000.00],
        ['Harry Potter', '+15553344', 'harry@hogwartsexpress.com', 'Hogwarts Retail', 'Education', 'Platform 9 3/4, London', 'Webinar', 'low', 'lost', $salesId, 3200.00]
    ];

    $stmtLead = $db->prepare("INSERT INTO leads (name, phone, email, company, industry, address, source, priority, status, assigned_to, expected_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($leads as $l) {
        $stmtLead->execute($l);
    }

    // Get Lead IDs
    $leadIds = $db->query("SELECT id, name, status, company, phone, email FROM leads")->fetchAll();
    
    // Seed Notes, Activities, and Follow-ups
    $stmtNote = $db->prepare("INSERT INTO lead_notes (lead_id, user_id, note, is_internal) VALUES (?, ?, ?, ?)");
    $stmtAct = $db->prepare("INSERT INTO lead_activities (lead_id, user_id, activity_type, description) VALUES (?, ?, ?, ?)");
    $stmtFollow = $db->prepare("INSERT INTO followups (lead_id, user_id, title, description, scheduled_at, status) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($leadIds as $lead) {
        $lid = $lead['id'];
        
        // General created activities
        $stmtAct->execute([$lid, $adminId, 'lead_assigned', 'Lead uploaded and initialized in the CRM CRM system.']);

        if ($lead['name'] == 'Alice Green') {
            $stmtNote->execute([$lid, $adminId, 'Interested in solar panel systems for their warehouse.', 1]);
            $stmtAct->execute([$lid, $adminId, 'note_added', 'Added internal profile notes about solar panels requirement.']);
        }
        
        if ($lead['name'] == 'Bob Miller') {
            $stmtNote->execute([$lid, $salesId, 'Had initial call. They requested a price list.', 0]);
            $stmtAct->execute([$lid, $salesId, 'status_changed', 'Changed status to Contacted.']);
            
            // Followup today
            $stmtFollow->execute([$lid, $salesId, 'Send Price List', 'Send pricing catalog and follow up via email.', date('Y-m-d H:i:s', strtotime('now + 2 hours')), 'pending']);
        }

        if ($lead['name'] == 'Charlie Brown') {
            $stmtNote->execute([$lid, $salesId, 'Follow-up scheduled to review legal document processing tools.', 1]);
            // Followup tomorrow
            $stmtFollow->execute([$lid, $salesId, 'Call to discuss specs', 'Review legal processing specs.', date('Y-m-d H:i:s', strtotime('tomorrow + 10 hours')), 'pending']);
        }

        if ($lead['name'] == 'Diana Prince') {
            $stmtNote->execute([$lid, $salesId, 'Qualified lead. Deciding factor is speed of delivery.', 0]);
            $stmtFollow->execute([$lid, $salesId, 'Demo scheduling', 'Set up product demo with their engineering team.', date('Y-m-d 14:00:00', strtotime('now + 3 days')), 'pending']);
        }

        if ($lead['name'] == 'Edward Stark') {
            $stmtNote->execute([$lid, $adminId, 'Proposal of $150k sent. Stated they need approval from CFO.', 1]);
            $stmtFollow->execute([$lid, $adminId, 'Check Proposal Status', 'Call Ned to verify if CFO reviewed the proposal.', date('Y-m-d H:i:s', strtotime('now - 1 day')), 'missed']); // Seed a missed follow-up!
        }

        if ($lead['name'] == 'George Wayne') {
            // Seed a converted customer
            $stmtAct->execute([$lid, $adminId, 'status_changed', 'Lead successfully converted to Active Customer!']);
            $db->exec("INSERT INTO customers (lead_id, name, phone, email, company, total_purchases, purchase_count, status) 
                       VALUES ({$lid}, '{$lead['name']}', '{$lead['phone']}', '{$lead['email']}', '{$lead['company']}', 250000.00, 2, 'active')");
        }
    }

    // Seed Tasks
    $stmtTask = $db->prepare("INSERT INTO tasks (title, description, assigned_to, created_by, priority, status, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtTask->execute(['Upload Q3 target file', 'Prepare Excel template mapping for mass leads.', $adminId, $adminId, 'high', 'in_progress', date('Y-m-d', strtotime('now + 2 days'))]);
    $stmtTask->execute(['Call Fiona Wayne', 'Discuss Gotham financial portfolio changes.', $salesId, $adminId, 'medium', 'todo', date('Y-m-d', strtotime('now + 1 day'))]);
    $stmtTask->execute(['Complete introductory briefing', 'Log onto training portal to complete sales guidelines module.', $salesId, $salesId, 'low', 'completed', date('Y-m-d', strtotime('now - 2 days'))]);

    // Seed Admin Notifications
    $stmtNotif = $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, ?, ?)");
    $stmtNotif->execute([$adminId, 'New Lead Assigned', 'Alice Green query has been assigned to your workspace.', 'info', 0]);
    $stmtNotif->execute([$salesId, 'Follow-Up Overdue', 'Missed follow-up with Edward Stark. Please reschedule.', 'warning', 0]);
    $stmtNotif->execute([$salesId, 'New Lead Assigned', 'Diana Prince query is waiting for your response.', 'info', 1]);

    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Setup Completed Successfully! ===\n";
echo "Default Logins Ready:\n";
echo "1. Super Admin: admin@crm.com | Admin123!\n";
echo "2. Sales Executive: sales@crm.com | Sales123!\n";
echo "\nTo modify connection credentials, create the file: config/db_credentials.php returning an array:\n";
echo "<?php return ['host' => 'localhost', 'dbname' => 'crm_system', 'username' => 'root', 'password' => ''];\n";
exit(0);
