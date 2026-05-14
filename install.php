<?php
define('DB_HOST', 'localhost');
// define('DB_HOST', '127.0.0.1:3309');
define('DB_USER', 'root');
define('DB_PASS', '');

$messages  = [];
$errors    = [];
$installed = false;

function seedEmail(string $e): string {
    return (strpos($e, '@') === false) ? $e . '@ucb.com.bd' : $e;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    $errors[] = 'MySQL connection failed: ' . $conn->connect_error;
} else {

    /* ── 1. Database ── */
    if ($conn->query("CREATE DATABASE IF NOT EXISTS `employee_management`
                      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        $messages[] = ['ok', "Database <code>employee_management</code> ready."];
    } else {
        $errors[] = 'Failed to create database: ' . $conn->error;
    }

    $conn->select_db('employee_management');
    $conn->set_charset('utf8mb4');

    /* ── 2. Users table ── */
    $conn->query("CREATE TABLE IF NOT EXISTS `users` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `username`   VARCHAR(50)  UNIQUE NOT NULL,
        `password`   VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = ['ok', 'Users table ready.'];

    /* ── 3. Employees table (DROP + CREATE with extension_no) ── */
    $conn->query("DROP TABLE IF EXISTS `employees`");
    $sql = "CREATE TABLE `employees` (
        `id`           INT AUTO_INCREMENT PRIMARY KEY,
        `eid`          VARCHAR(20)  NOT NULL,
        `name`         VARCHAR(100) NOT NULL,
        `designation`  VARCHAR(100) NOT NULL DEFAULT '',
        `division`     VARCHAR(100) NOT NULL DEFAULT '',
        `sub_division` VARCHAR(100) NOT NULL DEFAULT '',
        `role`         VARCHAR(100) NOT NULL DEFAULT '',
        `email`        VARCHAR(100) NOT NULL DEFAULT '',
        `cell_number`  VARCHAR(20)  NOT NULL DEFAULT '',
        `extension_no` VARCHAR(20)  NULL DEFAULT NULL,
        `status`       ENUM('active','inactive') DEFAULT 'active',
        `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_eid` (`eid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql)) {
        $messages[] = ['ok', 'Employees table created with <code>extension_no</code> column.'];
    } else {
        $errors[] = 'Failed to create employees table: ' . $conn->error;
    }

    /* ── 4. Default operator ── */
    $hashed = password_hash('op123', PASSWORD_BCRYPT);
    $uname  = 'operator';
    $stmt   = $conn->prepare("INSERT IGNORE INTO `users` (username, password) VALUES (?, ?)");
    $stmt->bind_param('ss', $uname, $hashed);
    if ($stmt->execute()) {
        $messages[] = ($stmt->affected_rows > 0)
            ? ['ok',   "Operator created — username: <code>operator</code> &nbsp;|&nbsp; password: <code>op123</code>"]
            : ['info', 'Default operator already exists (skipped).'];
    } else {
        $errors[] = 'Failed to insert operator: ' . $stmt->error;
    }
    $stmt->close();

    /* ── 5. Seed data ──────────────────────────────────────────────────────────
     * Columns: [eid, name, designation, ext_no, division, sub_division, role, email, cell]
     * ext_no  → stored as-is; '' becomes NULL
     * email   → @ucb.com.bd appended unless already contains @
     * cell    → '0' prefix added here
     * ──────────────────────────────────────────────────────────────────────── */
    $employees = [
        ['3201',  'Mohammod Monwar Hossain',       'EVP',    '7301',  'ICC & Audit', '',                    'Head of ICC & Audit', 'monwar.hossain',        '1715210540'],
        ['4587',  'Md. Saifullah',                 'SVP',    '7315',  'Audit',       'Audit',               'Audit Head',          'saifullah.m',           '1711131600'],
        ['419',   'Sazzad Yussouf',                'SVP',    '7307',  'Compliance',  'Head',                'Compliance Head',     'sazzad.yussouf',        '1711394686'],
        ['4566',  'Md. Anwar Hossain',             'FVP',    '7377',  'Audit',       'RBIA',                'Team Lead',           'md.anwarhossain',       '1552331409'],
        ['3385',  'Md. Faruk Hossain',             'FVP',    '',      'Audit',       'RBIA',                'Team Lead',           'faruk.hossain',         '1312520777'],
        ['5380',  'Md. Jahangir Hossain',          'FVP',    '7366',  'Audit',       'RBIA',                'Team Lead',           'j.hossain',             '1911697005'],
        ['3773',  'Md. Shahidul Islam Molla',      'FVP',    '73114', 'Audit',       'RBIA',                'Team Lead',           'shahidul.molla',        '1818349714'],
        ['4555',  'Mohammad Kalim Uddin Mozumder', 'FVP',    '7333',  'Audit',       'RBIA',                'Functional Head',     'mohammad.mozumder',     '1751868776'],
        ['5486',  'Rashedur Rahman',               'FVP',    '7331',  'Audit',       'RBIA-Monitoring',     'Functional Head',     'rashedur.rahman',       '1711445014'],
        ['3277',  'Mohammad Ashraf Uddin Bhuiyan', 'FVP',    '7316',  'Audit',       'Special',             'Functional Head',     'ashraf.bhuiyan',        '1717132272'],
        ['6618',  'Arif Kibria',                   'FVP',    '7356',  'Compliance',  'BB',                  'Functional Head',     'arif.kibria',           '1713493993'],
        ['3291',  'Ehsan Uddin Ahmed',             'FVP',    '',      'Compliance',  'BB',                  'Team Member',         'ehsan.ahmed',           '1730031470'],
        ['4418',  'Mohammad Ashraful Alam',        'FVP',    '7348',  'Monitoring',  'Monitoring',          'Functional Head',     'mashraful.alam',        '1746836475'],
        ['5438',  'Md. Amirul Islam',              'VP',     '7354',  'Audit',       'FX',                  'Functional Head',     'md.amirul',             '1711454488'],
        ['5361',  'Md. Rakibur Rahman',            'VP',     '7332',  'Audit',       'IT',                  'Functional Head',     'rakibur.rahman',        '1625690881'],
        ['5447',  'Kazi Rakib Hossan',             'VP',     '7314',  'Audit',       'RBIA-Monitoring',     'Team Member',         'kazi.hossan',           '1717299008'],
        ['2458',  'Ziaul Hasan Iftiar Mahbub',     'VP',     '7362',  'Audit',       'RBIA-Monitoring',     'Team Member',         'ziaul.hasan',           '1811456755'],
        ['3535',  'Abdul Ahad',                    'VP',     '7371',  'Audit',       'RBIA',                'Team Lead',           'aahad.russel',          '1913780097'],
        ['4606',  'Chapal Barua',                  'VP',     '7369',  'Audit',       'RBIA',                'Team Lead',           'chapal.barua',          '1715011645'],
        ['7161',  'Hasan Hafizur Rahman',          'VP',     '',      'Audit',       'RBIA',                'Team Lead',           'hasan.rahman',          '1717178800'],
        ['2932',  'Kazi Zahirul Islam',            'VP',     '7318',  'Audit',       'RBIA',                'Team Lead',           'mzahirul.islam',        '1712012086'],
        ['4199',  'Mahmudul Hasan',                'VP',     '7321',  'Audit',       'RBIA',                'Team Lead',           'mah.hasan',             '1841335522'],
        ['3681',  'Md. Abdur Rob Howlader',        'VP',     '7312',  'Audit',       'RBIA',                'Team Lead',           'abdur.howlader',        '1749303426'],
        ['3611',  'Md. Kamal Hossain',             'VP',     '7335',  'Audit',       'RBIA',                'Team Lead',           'md.kamalhossain',       '1711474441'],
        ['6875',  'Mohammed Mohiuddin Biswas',     'VP',     '7355',  'Audit',       'RBIA',                'Team Lead',           'mohiuddin.biswas',      '1713385030'],
        ['2600',  'Fatema-Tuj-Johura',             'VP',     '',      'Audit',       'RBIA',                'Team Lead',           'fatematuj.johura',      '1755651449'],
        ['3270',  'Wayes Ahmed',                   'VP',     '7339',  'Audit',       'RBIA-Monitoring',     'Team Member',         'wayes.ahmed',           '1913822361'],
        ['679',   'Md. Helal Uddin',               'VP',     '7325',  'Compliance',  'RBIA',                'Team Member',         'h.uddin',               '1716830458'],
        ['779',   'Kazi Monir Hossain',            'VP',     '7368',  'Monitoring',  'Monitoring',          'Team Member',         'kmonir.hossain',        '1819468183'],
        ['4200',  'Rafiul Bari Khan',              'FAVP',   '7328',  'Audit',       'ISLAMIC',             'Functional Head',     'rafiul.bari',           '1515610848'],
        ['2959',  'Md. Wasim Uddin Qureshi',       'FAVP',   '7336',  'Audit',       'IT',                  'Team Member',         'wasim.qureshi',         '1733653003'],
        ['4102',  'Md. Fahad Ahmed Bhuiyan',       'FAVP',   '7364',  'Audit',       'RBIA',                'Team Lead',           'mfahad.bhuiyan',        '1730995153'],
        ['3609',  'Md. Kamal Sarder',              'FAVP',   '7327',  'Audit',       'RBIA',                'Team Lead',           'mkamal.sarder',         '1784705429'],
        ['741',   'Md.Saiful Kabir',               'FAVP',   '7342',  'Audit',       'RBIA',                'Team Lead',           'saiful.kabir',          '1821801492'],
        ['8072',  'Mohammad Omar Faruque',         'FAVP',   '7380',  'Audit',       'RBIA',                'Team Lead',           'mohammad.faruque',      '1929574251'],
        ['3811',  'Muhammad Mahbubur Rahman',      'FAVP',   '',      'Audit',       'RBIA',                'Team Lead',           'mahbubur.rahman',       '1716588687'],
        ['4190',  'S M Oly Ahad',                  'FAVP',   '',      'Audit',       'RBIA',                'Team Lead',           'oly.ahad@ucb.com.bd',   '1748916595'],
        ['5873',  'Sunnyeat Ismat Omith',          'FAVP',   '7334',  'Audit',       'RBIA',                'Team Lead',           'sunnyeat.omith',        '1730352516'],
        ['5550',  'Md. Shahidul Islam Prodhan',    'FAVP',   '',      'Compliance',  'RBIA',                'Team Member',         'shahid.islam',          '1326726415'],
        ['10493', 'Mohammad Shafiqul Islam',       'FAVP',   '',      'Compliance',  'BB',                  'Team Member',         'mohammad.shafiqul',     '1712242359'],
        ['9476',  'Mohammad Mazharul Islam',       'FAVP',   '7329',  'Compliance',  'RBIA',                'Team Member',         'islam.mazharul',        '1822645484'],
        ['4258',  'Iftekhar Karim',                'FAVP',   '7345',  'Compliance',  'RBIA',                'Functional Head',     'iftekhar.karim',        '1670186061'],
        ['612',   'Md. Abdur Rahim',               'FAVP',   '7358',  'Compliance',  'RBIA',                'Team Member',         'ab.rahim',              '1731881203'],
        ['6835',  'Md. Mahedi Hassan',             'FAVP',   '7308',  'Compliance',  'RBIA',                'Team Member',         'mahedi.hassan',         '1671118910'],
        ['7993',  'Sumaira Tasmeen',               'FAVP',   '7372',  'Compliance',  'RBIA',                'Team Member',         'sumaira.tasmeen',       '1711938187'],
        ['5845',  'A.B.M. Mamunul Kabir',          'FAVP',   '7389',  'Monitoring',  'Monitoring',          'Team Member',         'mamunul.kabir',         '1711736673'],
        ['4251',  'Chhabi Rani Paul',              'FAVP',   '7370',  'Monitoring',  'Monitoring',          'Team Member',         'chhabi.paul',           '1911747174'],
        ['8747',  'Akram Uddin Magumder',          'AVP',    '7374',  'Audit',       'RBIA',                'Team Lead',           'akram.uddin',           '1913967237'],
        ['5334',  'Khandaker Abdul Muntashir',     'AVP',    '7375',  'Audit',       'RBIA',                'Team Lead',           'k.muntashir',           '1829671633'],
        ['8079',  'Jakir Hossain',                 'AVP',    '7352',  'Audit',       'Special',             'Team Member',         'jakir.hossain',         '1683693770'],
        ['5702',  'Md. Mezbaul Haider',            'AVP',    '7305',  'Audit',       'Special',             'Team Member',         'mezbaul.haider',        '1777448769'],
        ['5286',  'S. Md. Badiul Akbar',           'AVP',    '7313',  'Audit',       'Special',             'Team Member',         'sbadiul.akbar',         '1911703314'],
        ['2818',  'Nurul Amin',                    'AVP',    '7360',  'Compliance',  'BB',                  'Team Member',         'n.amin',                '1811894247'],
        ['3477',  'Md. Firoz Khan',                'AVP',    '7353',  'Compliance',  'RBIA',                'Team Member',         'firoz.khan',            '1816404870'],
        ['6948',  'Sabrina Rashid',                'AVP',    '',      'Compliance',  'RBIA',                'Team Member',         'sabrina.rashid',        '1717386686'],
        ['790',   'Zahangir Alam',                 'AVP',    '7326',  'Compliance',  'RBIA',                'Team Member',         'mdj.alam',              '1915479039'],
        ['8745',  'Lubana Rahman',                 'SEO',    '7361',  'Audit',       'FX',                  'Team Member',         'lubana.rahman',         '1727707405'],
        ['8678',  'Feroz Hossain',                 'SEO',    '',      'Audit',       'ISLAMIC',             'Team Member',         'feroz.hossain',         '1728856027'],
        ['6324',  'Mohammad Masuf Bin Nuruddin',   'SEO',    '7338',  'Audit',       'IT',                  'Team Member',         'masuf.nuruddin',        '1744779977'],
        ['6316',  'Muhammad Sadequr Rahman',       'SEO',    '7385',  'Audit',       'IT',                  'Team Member',         'sadequr.rahman',        '1730044424'],
        ['8731',  'Ahmad Sayeed Russel',           'SEO',    '7330',  'Audit',       'RBIA',                'Team Lead',           'sayeed.russel',         '1911612504'],
        ['8071',  'Imtiaz Hossain',                'SEO',    '',      'Audit',       'RBIA',                'Team Lead',           'imtiaz.hossain',        '1730333002'],
        ['10302', 'Ishtiaq Mahmud Emon',           'SEO',    '',      'Audit',       'RBIA',                'Team Lead',           'ishtiaq.emon',          '1717558202'],
        ['8739',  'Md. Omar Faruk',                'SEO',    '7330',  'Audit',       'Special',             'Team Member',         'md.omar.faruk',         '1719382651'],
        ['2767',  'Muhammad Rashedul Islam',       'SEO',    '7357',  'Compliance',  'RBIA',                'Team Member',         'rashedul.islam',        '1819444733'],
        ['3856',  'Amina Akhter',                  'SEO',    '',      'Monitoring',  'Monitoring',          'Team Member',         'amina.akhter',          '1754334318'],
        ['9661',  'Rownak Tabassum Prima',         'EO',     '7373',  'Compliance',  'IT Audit Compliance', 'Team Member',         'rownak.prima',          '1914538280'],
        ['9317',  'Rathindra Nath Mondal',         'EO',     '7359',  'Audit',       'IT',                  'Team Member',         'rathindra.nath',        '1710649448'],
        ['8200',  'Sakif Samih Ul Haq',            'EO',     '7322',  'Audit',       'IT',                  'Team Member',         'sakif.haq',             '1911810725'],
        ['9660',  'Ujjwal Kanthi Dhar',            'EO',     '7378',  'Audit',       'IT',                  'Team Member',         'ujjwal.dhar',           '1711083849'],
        ['4052',  'Aminul Islam',                  'EO',     '',      'Audit',       'Special',             'Team Member',         'a.islam',               '1515261450'],
        ['8750',  'Kawsar Mohammad Farhad',        'EO',     '7344',  'Audit',       'RBIA',                'Team Lead',           'kawsar.farhad',         '1717194959'],
        ['8741',  'Kazi Shahriar Sonnet',          'EO',     '7387',  'Audit',       'RBIA',                'Team Lead',           'kazi.sonnet',           '1843333366'],
        ['6402',  'Md. Rafiqur Rahman',            'EO',     '7381',  'Audit',       'RBIA',                'Team Lead',           'mdrafiqur.rahman',      '1670965483'],
        ['6323',  'Raihan Kabir',                  'EO',     '7382',  'Audit',       'RBIA-Monitoring',     'Team Member',         'raihan.kabir',          '1843168670'],
        ['1411',  'Doulan Barua',                  'EO',     '7340',  'Compliance',  'BB',                  'Team Member',         'doulan.borua',          '1712080120'],
        ['10301', 'Ashadus Jaman',                 'SO',     '',      'Audit',       'RBIA',                'Team Lead',           'ashadus.jaman',         '1681139929'],
        ['8913',  'Md. Mainuddin',                 'SO',     '7383',  'Audit',       'RBIA',                'Team Lead',           'mainuddin.md',          '1521470588'],
        ['8751',  'Md. Riaz Uddin',                'SO',     '7344',  'Audit',       'RBIA',                'Team Lead',           'md.riaz.uddin',         '1728840117'],
        ['10274', 'Monir Ahammad Bhuiyan',         'SO',     '',      'Audit',       'RBIA',                'Team Lead',           'monir.bhuiyan',         '1517094586'],
        ['6317',  'Razib Khan',                    'SO',     '7351',  'Audit',       'RBIA',                'Team Lead',           'razib.khan',            '1919406708'],
        ['7957',  'Md. Salman Al- Mamun',          'Off',    '7384',  'Audit',       'IT',                  'Team Member',         'salman.mamun',          '1687176880'],
        ['9352',  'S. M. Jafrul Hasan',            'Off',    '7349',  'Audit',       'IT',                  'Team Member',         'jafrul.hasan',          '1722489198'],
        ['10381', 'Md. Shariful Islam',            'Off',    '',      'Audit',       'RBIA-Monitoring',     'Team Member',         'm.shariful.islam',      '1687143878'],
        ['10228', 'Samzid Khan',                   'JO',     '',      'Audit',       'IT',                  'Team Member',         'samzid.khan',           '1727336106'],
        ['8048',  'Mahede Hasan Shaoun',           'JO',     '7341',  'Audit',       'RBIA',                'Team Lead',           'mahede.shaoun',         '1716896891'],
        ['8027',  'Wahidul Islam',                 'JO',     '7310',  'Audit',       'Special',             'Team Member',         'islam.wahidul',         '1733423242'],
        ['2162',  'Anwarul Islam',                 'EQ',     '7309',  'Compliance',  'BB',                  'Team Member',         'anwarul.islam',         '1815535888'],
        ['2828',  'Omar Faruqe',                   'SO(CS)', '7320',  'Compliance',  'General section',     'Team Member',         'faruque.omar',          '1749066584'],
    ];

    $stmt = $conn->prepare(
        "INSERT IGNORE INTO `employees`
             (eid, name, designation, division, sub_division, role, email, cell_number, extension_no)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $inserted = 0;
    $skipped  = 0;
    foreach ($employees as $s) {
        $ext   = ($s[3] !== '') ? $s[3] : null;
        $email = seedEmail($s[7]);
        $cell  = '0' . $s[8];

        $stmt->bind_param('sssssssss',
            $s[0],   // eid
            $s[1],   // name
            $s[2],   // designation
            $s[4],   // division
            $s[5],   // sub_division
            $s[6],   // role
            $email,  // email (with domain)
            $cell,   // cell_number (with 0 prefix)
            $ext     // extension_no (null if empty)
        );
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) $inserted++;
            else                          $skipped++;
        }
    }
    $stmt->close();

    $messages[] = ['ok', "{$inserted} employee records inserted" .
        ($skipped > 0 ? ", {$skipped} skipped (duplicate EID)." : '.')];

    $conn->close();
    if (empty($errors)) $installed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install – ICCD Officer's Portal</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.min.css" rel="stylesheet">
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
            max-width: 580px;
            width: 100%;
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,.3);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #1a3a6c, #2563a8);
            color: white;
            padding: 28px 32px;
            border: none;
        }
        .card-header h2 { font-size: 1.4rem; font-weight: 700; margin: 0 0 4px; }
        .card-header p  { margin: 0; opacity: .8; font-size: .875rem; }
        .card-body { padding: 28px 32px; }
        .step {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid #f2f2f2;
            font-size: .9rem;
        }
        .step:last-child { border-bottom: none; }
        .step-icon { flex-shrink: 0; font-size: 1rem; margin-top: 1px; }
        .icon-ok   { color: #28a745; }
        .icon-info { color: #0d6efd; }
        code { background: #f0f4f8; padding: 1px 5px; border-radius: 4px; font-size: .85em; }
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
        .btn-go:hover { opacity: .88; color: white; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <div style="font-size:2.2rem;margin-bottom:10px;">
            <i class="bi bi-gear-wide-connected"></i>
        </div>
        <h2>System Installation</h2>
        <p>ICCD Officer&rsquo;s Portal &ndash; Employee Management System</p>
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
                <div class="mt-2 text-muted" style="font-size:.85rem;">
                    Make sure XAMPP MySQL is running and credentials in
                    <code>config.php</code> are correct.
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
                    Login with &nbsp;<code>operator</code> / <code>op123</code>
                    &nbsp;and change the password immediately.
                </div>
            </div>
            <a href="index.php" class="btn-go">
                <i class="bi bi-arrow-right-circle-fill me-2"></i>Go to Officer's Portal
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
