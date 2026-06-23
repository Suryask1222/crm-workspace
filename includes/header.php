<?php
// includes/header.php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/security.php';

$currentUser = requireLogin();
$db = getDBConnection();

// Fetch current user full name & email
$stmtUserHeader = $db->prepare("SELECT name, email, role_id FROM users WHERE id = ?");
$stmtUserHeader->execute([$_SESSION['user_id']]);
$userData = $stmtUserHeader->fetch();

// Fetch unread notifications count
$stmtNotifCount = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmtNotifCount->execute([$_SESSION['user_id']]);
$unreadCount = $stmtNotifCount->fetchColumn();

// Fetch system settings dynamically
$companyName = 'Nexentora Technologies';
$currencySymbol = '₹';
try {
    $stmtSettings = $db->query("SELECT key_name, value_value FROM settings");
    $sysSettings = [];
    while ($row = $stmtSettings->fetch()) {
        $sysSettings[$row['key_name']] = $row['value_value'];
    }
    if (isset($sysSettings['company_name'])) $companyName = $sysSettings['company_name'];
    if (isset($sysSettings['currency_symbol'])) $currencySymbol = $sysSettings['currency_symbol'];
} catch (PDOException $e) {}
$baseURL = getBaseURL();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($companyName); ?> - CRM</title>
    
    <!-- CSRF Token Meta -->
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    
    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="<?php echo $baseURL; ?>assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    
    <!-- Immediate theme check to prevent flash -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- FullCalendar CDN -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Sidebar Include will sit here -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Container -->
    <main id="main-content">
        
        <!-- Header Navbar -->
        <header class="glass-panel">
            <div class="header-left">
                <button id="sidebar-toggle" class="menu-toggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 style="font-size: 20px; font-weight: 600;"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Workspace'; ?></h1>
            </div>
            
            <div class="header-right">
                <!-- Theme Toggler -->
                <button id="theme-toggle" class="btn-icon" title="Toggle Theme">
                    <i class="fa-solid fa-moon"></i>
                </button>
                
                <!-- Notifications Dropdown Trigger -->
                <div style="position: relative;">
                    <button id="notif-toggle" class="btn-icon" style="position: relative;" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                <?php echo $unreadCount; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    
                    <!-- Notifications Panel -->
                    <div id="notif-dropdown" class="glass-panel" style="position: absolute; right: 0; top: 55px; width: 320px; max-height: 400px; display: none; flex-direction: column; overflow-y: auto; z-index: 1050; padding: 12px 0;">
                        <div style="padding: 0 16px 10px 16px; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; font-size: 14px;">Notifications</span>
                            <button id="mark-all-read" style="background: none; border: none; color: var(--accent); font-size: 12px; cursor: pointer; font-weight: 500;">Mark all read</button>
                        </div>
                        <div id="notif-list-container">
                            <!-- Populated dynamically via JS -->
                            <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">Loading...</div>
                        </div>
                    </div>
                </div>
                
                <!-- User Profile Info -->
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="text-align: right; display: none; display: md-block;" class="user-meta-header">
                        <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($userData['name']); ?></div>
                        <div style="font-size: 11px; color: var(--text-secondary);"><?php echo $currentUser['role'] === 'Admin' ? 'Super Admin' : 'Sales Staff'; ?></div>
                    </div>
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 700; border: 1px solid var(--border-glass);">
                        <?php echo strtoupper(substr($userData['name'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Page Output Starts Below -->
