<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

$messages = [];
$errors   = [];
$installed = false;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    $errors[] = 'MySQL connection failed: ' . $conn->connect_error;
} else {
    // Create database
    if ($conn->query("CREATE DATABASE IF NOT EXISTS `employee_management` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        $messages[] = ['ok', "Database <code>employee_management</code> ready."];
    } else {
        $errors[] = 'Failed to create database: ' . $conn->error;
    }

    $conn->select_db('employee_management');

    // Users table
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `username`   VARCHAR(50)  UNIQUE NOT NULL,
        `password`   VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql)) {
        $messages[] = ['ok', 'Users table ready.'];
    } else {
        $errors[] = 'Failed to create users table: ' . $conn->error;
    }

    // Employees table
    $sql = "CREATE TABLE IF NOT EXISTS `employees` (
        `id`           INT AUTO_INCREMENT PRIMARY KEY,
        `eid`          VARCHAR(20)  UNIQUE NOT NULL,
        `name`         VARCHAR(100) NOT NULL,
        `designation`  VARCHAR(100) DEFAULT NULL,
        `division`     VARCHAR(100) DEFAULT NULL,
        `sub_division` VARCHAR(100) DEFAULT NULL,
        `email`        VARCHAR(100) DEFAULT NULL,
        `cell_number`  VARCHAR(20)  DEFAULT NULL,
        `status`       ENUM('active','inactive') DEFAULT 'active',
        `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql)) {
        $messages[] = ['ok', 'Employees table ready.'];
    } else {
        $errors[] = 'Failed to create employees table: ' . $conn->error;
    }

    // Default operator
    $hashed = password_hash('op123', PASSWORD_BCRYPT);
    $uname  = 'operator';
    $stmt   = $conn->prepare("INSERT IGNORE INTO `users` (username, password) VALUES (?, ?)");
    $stmt->bind_param('ss', $uname, $hashed);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $messages[] = ['ok', "Default operator created — username: <code>operator</code> &nbsp;|&nbsp; password: <code>op123</code>"];
        } else {
            $messages[] = ['info', 'Default operator already exists (skipped).'];
        }
    } else {
        $errors[] = 'Failed to insert operator: ' . $stmt->error;
    }
    $stmt->close();

    // Sample employees
    $samples = [
        ['EMP001','Ahmed Rahman','Deputy Director','Administration','Management','ahmed.rahman@iccd.gov.bd','01711000001','active'],
        ['EMP002','Fatima Begum','Assistant Director','HR','Recruitment','fatima.begum@iccd.gov.bd','01811000002','active'],
        ['EMP003','Karim Hossain','Senior Engineer','Engineering','Infrastructure','karim.hossain@iccd.gov.bd','01911000003','active'],
        ['EMP004','Nasreen Akter','Data Analyst','IT','Data Management','nasreen.akter@iccd.gov.bd','01711000004','active'],
        ['EMP005','Rahim Uddin','Finance Officer','Finance','Accounts','rahim.uddin@iccd.gov.bd','01811000005','active'],
        ['EMP006','Sadia Islam','Project Coordinator','Project Management','Planning','sadia.islam@iccd.gov.bd','01911000006','active'],
        ['EMP007','Tariq Mahmud','Network Administrator','IT','Infrastructure','tariq.mahmud@iccd.gov.bd','01711000007','active'],
        ['EMP008','Umme Kulsum','Procurement Officer','Procurement','Supply Chain','umme.kulsum@iccd.gov.bd','01811000008','inactive'],
        ['EMP009','Wahid Sarker','Legal Advisor','Legal','Compliance','wahid.sarker@iccd.gov.bd','01911000009','active'],
        ['EMP010','Yasmin Khanam','Training Coordinator','HR','Training','yasmin.khanam@iccd.gov.bd','01711000010','inactive'],
        ['EMP011','Zubair Ahmed','Budget Analyst','Finance','Planning','zubair.ahmed@iccd.gov.bd','01811000011','active'],
        ['EMP012','Rokeya Sultana','Administrative Officer','Administration','Operations','rokeya.sultana@iccd.gov.bd','01911000012','active'],
    ];

    $stmt = $conn->prepare(
        "INSERT IGNORE INTO `employees` (eid,name,designation,division,sub_division,email,cell_number,status)
         VALUES (?,?,?,?,?,?,?,?)"
    );
    $inserted = 0;
    foreach ($samples as $s) {
        $stmt->bind_param('ssssssss', $s[0],$s[1],$s[2],$s[3],$s[4],$s[5],$s[6],$s[7]);
        if ($stmt->execute() && $stmt->affected_rows > 0) $inserted++;
    }
    $stmt->close();

    if ($inserted > 0) {
        $messages[] = ['ok', "{$inserted} sample employee records inserted."];
    } else {
        $messages[] = ['info', 'Sample employees already exist (skipped).'];
    }

    $conn->close();

    if (empty($errors)) {
        $installed = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install – Employee Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f2347 0%, #1a3a6c 50%, #2563a8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            max-width: 560px;
            width: 100%;
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #1a3a6c, #2563a8);
            color: white;
            padding: 28px 32px;
            border: none;
        }
        .card-header h2 { font-size: 1.4rem; font-weight: 700; margin: 0 0 4px; }
        .card-header p  { margin: 0; opacity: 0.8; font-size: 0.875rem; }
        .card-body { padding: 28px 32px; }
        .step {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid #f2f2f2;
            font-size: 0.9rem;
        }
        .step:last-child { border-bottom: none; }
        .step-icon { flex-shrink: 0; font-size: 1rem; margin-top: 1px; }
        .icon-ok   { color: #28a745; }
        .icon-info { color: #0d6efd; }
        code { background: #f0f4f8; padding: 1px 5px; border-radius: 4px; font-size: 0.85em; }
        .btn-go {
            background: linear-gradient(135deg, #1a3a6c, #2563a8);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            font-size: 1rem;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: opacity .2s;
        }
        .btn-go:hover { opacity: 0.88; color: white; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <div style="font-size:2.2rem;margin-bottom:10px;"><i class="bi bi-gear-wide-connected"></i></div>
        <h2>System Installation</h2>
        <p>Employee Management System &ndash; ICCD Officer&rsquo;s Portal</p>
    </div>
    <div class="card-body">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-4">
                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Installation failed:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-3 text-muted" style="font-size:.85rem;">
                    Make sure XAMPP MySQL is running and the credentials in <code>config.php</code> are correct.
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($messages)): ?>
            <div class="mb-4">
                <?php foreach ($messages as [$type, $msg]): ?>
                    <div class="step">
                        <span class="step-icon <?= $type === 'ok' ? 'icon-ok' : 'icon-info' ?>">
                            <i class="bi <?= $type === 'ok' ? 'bi-check-circle-fill' : 'bi-info-circle-fill' ?>"></i>
                        </span>
                        <span><?= $msg ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($installed): ?>
            <div class="alert alert-success mb-4 d-flex align-items-start gap-2">
                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0"></i>
                <div>
                    <strong>Installation complete!</strong><br>
                    Default operator — username: <code>operator</code> &nbsp;/&nbsp; password: <code>op123</code>
                </div>
            </div>
            <a href="index.php" class="btn-go">
                <i class="bi bi-arrow-right-circle-fill me-2"></i>Go to Employee Management System
            </a>
        <?php else: ?>
            <a href="install.php" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-clockwise me-1"></i>Retry Installation
            </a>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
