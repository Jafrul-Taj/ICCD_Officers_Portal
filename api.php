<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'login':            handleLogin();       break;
    case 'logout':           handleLogout();      break;
    case 'check_session':    checkSession();      break;
    case 'get_employees':    getEmployees();      break;
    case 'get_filter_data':  getFilterData();     break;
    case 'get_subdivisions': getSubdivisions();   break;
    case 'get_roles':        getRoles();          break;
    case 'get_employee':     getEmployee();       break;
    case 'add_employee':
        requireOperator();
        addEmployee();
        break;
    case 'edit_employee':
        requireOperator();
        editEmployee();
        break;
    case 'toggle_status':
        requireOperator();
        toggleStatus();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

// ─────────────────────────────────────────────
//  AUTH
// ─────────────────────────────────────────────

function requireOperator() {
    if (empty($_SESSION['operator_id'])) {
        http_response_code(401);
        echo json_encode([
            'success'         => false,
            'message'         => 'Unauthorized. Please login as operator.',
            'session_expired' => true
        ]);
        exit;
    }
}

function checkSession() {
    echo json_encode([
        'success'   => true,
        'logged_in' => !empty($_SESSION['operator_id']),
        'username'  => $_SESSION['operator_username'] ?? null
    ]);
}

function handleLogin() {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        return;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['operator_id']       = $user['id'];
        $_SESSION['operator_username'] = $user['username'];
        echo json_encode(['success' => true, 'message' => 'Login successful.', 'username' => $user['username']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
}

function handleLogout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
}

// ─────────────────────────────────────────────
//  DESIGNATION ORDER (shared constant)
// ─────────────────────────────────────────────

function designationOrder(): array {
    return [
        'EVP'    => 1,  'SVP'  => 2,  'FVP'   => 3,  'VP'    => 4,
        'FAVP'   => 5,  'AVP'  => 6,  'SEO'   => 7,  'EO'    => 8,
        'SO'     => 9,  'Off'  => 10, 'JO'    => 11, 'DEO'   => 12,
        'SO(CS)' => 13, 'EQ'   => 14,
    ];
}

function roleOrder(): array {
    return [
        'Head of ICC & Audit' => 1,
        'Audit Head'          => 2,
        'Compliance Head'     => 3,
        'Functional Head'     => 4,
        'Team Lead'           => 5,
        'Team Member'         => 6,
    ];
}

// ─────────────────────────────────────────────
//  EMPLOYEES – READ
// ─────────────────────────────────────────────

function getEmployees() {
    $division      = trim($_GET['division']     ?? '');
    $sub_div       = trim($_GET['sub_division'] ?? '');
    $role          = trim($_GET['role']         ?? '');
    $name          = trim($_GET['name']         ?? '');
    $eid           = trim($_GET['eid']          ?? '');
    $show_inactive = (($_GET['show_inactive'] ?? '0') === '1');

    if (empty($_SESSION['operator_id'])) {
        $show_inactive = false;
    }

    $where  = [];
    $params = [];
    $types  = '';

    if ($division !== '') { $where[] = 'division = ?';     $params[] = $division;          $types .= 's'; }
    if ($sub_div  !== '') { $where[] = 'sub_division = ?'; $params[] = $sub_div;           $types .= 's'; }
    if ($role     !== '') { $where[] = 'role = ?';         $params[] = $role;              $types .= 's'; }
    if ($name     !== '') { $where[] = 'name LIKE ?';      $params[] = '%' . $name . '%';  $types .= 's'; }
    if ($eid      !== '') { $where[] = 'eid LIKE ?';       $params[] = '%' . $eid . '%';   $types .= 's'; }
    if (!$show_inactive)  { $where[] = "status = 'active'"; }

    $sql = 'SELECT id, eid, name, designation, division, sub_division, role, email, cell_number, status
            FROM employees';
    if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
    $sql .= ' ORDER BY name ASC';

    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    if ($params) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    $order = designationOrder();
    usort($rows, function ($a, $b) use ($order) {
        $oa = $order[$a['designation']] ?? 999;
        $ob = $order[$b['designation']] ?? 999;
        if ($oa !== $ob) return $oa - $ob;
        return strcmp($a['name'], $b['name']);
    });

    echo json_encode(['success' => true, 'data' => $rows]);
}

function getFilterData() {
    $conn = getConnection();

    // Divisions + sub_divisions map
    $result = $conn->query(
        "SELECT DISTINCT division, sub_division FROM employees
         WHERE division IS NOT NULL AND division <> ''
         ORDER BY division, sub_division"
    );
    $divisions       = [];
    $subdivisionsMap = [];
    $allSubs         = [];

    while ($row = $result->fetch_assoc()) {
        $div = $row['division'];
        $sub = $row['sub_division'];
        if (!in_array($div, $divisions, true)) $divisions[] = $div;
        if ($sub !== null && $sub !== '') {
            if (!isset($subdivisionsMap[$div])) $subdivisionsMap[$div] = [];
            if (!in_array($sub, $subdivisionsMap[$div], true)) $subdivisionsMap[$div][] = $sub;
            if (!in_array($sub, $allSubs, true))               $allSubs[] = $sub;
        }
    }
    sort($divisions);
    sort($allSubs);
    foreach ($subdivisionsMap as &$arr) sort($arr);
    unset($arr);

    // Designations (sorted by hierarchy)
    $result = $conn->query(
        "SELECT DISTINCT designation FROM employees
         WHERE designation IS NOT NULL AND designation <> ''
         ORDER BY designation"
    );
    $designations = [];
    while ($row = $result->fetch_assoc()) $designations[] = $row['designation'];
    $dOrder = designationOrder();
    usort($designations, function ($a, $b) use ($dOrder) {
        $oa = $dOrder[$a] ?? 999;
        $ob = $dOrder[$b] ?? 999;
        if ($oa !== $ob) return $oa - $ob;
        return strcmp($a, $b);
    });

    // Roles (sorted by hierarchy)
    $result = $conn->query(
        "SELECT DISTINCT role FROM employees
         WHERE role IS NOT NULL AND role <> ''
         ORDER BY role"
    );
    $roles  = [];
    while ($row = $result->fetch_assoc()) $roles[] = $row['role'];
    $rOrder = roleOrder();
    usort($roles, function ($a, $b) use ($rOrder) {
        $oa = $rOrder[$a] ?? 999;
        $ob = $rOrder[$b] ?? 999;
        if ($oa !== $ob) return $oa - $ob;
        return strcmp($a, $b);
    });

    $conn->close();
    echo json_encode([
        'success'          => true,
        'divisions'        => $divisions,
        'subdivisions'     => $subdivisionsMap,
        'all_subdivisions' => $allSubs,
        'designations'     => $designations,
        'roles'            => $roles,
    ]);
}

function getSubdivisions() {
    $division = trim($_GET['division'] ?? '');
    if ($division === '') {
        echo json_encode(['success' => false, 'message' => 'Division is required.']);
        return;
    }
    $conn = getConnection();
    $stmt = $conn->prepare(
        "SELECT DISTINCT sub_division FROM employees
         WHERE division = ? AND sub_division IS NOT NULL AND sub_division != ''
         ORDER BY sub_division ASC"
    );
    $stmt->bind_param('s', $division);
    $stmt->execute();
    $result       = $stmt->get_result();
    $subdivisions = [];
    while ($row = $result->fetch_assoc()) $subdivisions[] = $row['sub_division'];
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'subdivisions' => $subdivisions]);
}

function getRoles() {
    $division = trim($_GET['division']     ?? '');
    $sub_div  = trim($_GET['sub_division'] ?? '');

    $where  = ["role IS NOT NULL", "role <> ''"];
    $params = [];
    $types  = '';

    if ($division !== '') { $where[] = 'division = ?';     $params[] = $division; $types .= 's'; }
    if ($sub_div  !== '') { $where[] = 'sub_division = ?'; $params[] = $sub_div;  $types .= 's'; }

    $sql  = 'SELECT DISTINCT role FROM employees WHERE ' . implode(' AND ', $where);

    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    if ($params) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $result = $stmt->get_result();
    $roles  = [];
    while ($row = $result->fetch_assoc()) $roles[] = $row['role'];
    $stmt->close();
    $conn->close();

    $rOrder = roleOrder();
    usort($roles, function ($a, $b) use ($rOrder) {
        $oa = $rOrder[$a] ?? 999;
        $ob = $rOrder[$b] ?? 999;
        if ($oa !== $ob) return $oa - $ob;
        return strcmp($a, $b);
    });
    echo json_encode(['success' => true, 'roles' => $roles]);
}

function getEmployee() {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        return;
    }
    $conn = getConnection();
    $stmt = $conn->prepare(
        "SELECT id, eid, name, designation, division, sub_division, role, email, cell_number, status
         FROM employees WHERE id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $emp = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($emp) {
        echo json_encode(['success' => true, 'data' => $emp]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Employee not found.']);
    }
}

// ─────────────────────────────────────────────
//  SHARED VALIDATION
// ─────────────────────────────────────────────

function validateEmployeeInput(
    string $eid, string $name, string $desig, string $division,
    string $sub_div, string $role, string $email, string $cell
): ?string {
    if ($eid      === '') return 'EID is required.';
    if ($name     === '') return 'Full Name is required.';
    if ($desig    === '') return 'Designation is required.';
    if ($division === '') return 'Division is required.';
    if ($role     === '') return 'Role is required.';
    if ($email    === '') return 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email address format (e.g. name@domain.com).';
    if ($cell     === '') return 'Cell Number is required.';
    if (!preg_match('/^\d{10,15}$/', $cell)) return 'Cell Number must be 10–15 digits (numbers only).';
    return null;
}

// ─────────────────────────────────────────────
//  EMPLOYEES – WRITE (operator only)
// ─────────────────────────────────────────────

function addEmployee() {
    $eid      = trim($_POST['eid']          ?? '');
    $name     = trim($_POST['name']         ?? '');
    $desig    = trim($_POST['designation']  ?? '');
    $division = trim($_POST['division']     ?? '');
    $sub_div  = trim($_POST['sub_division'] ?? '');
    $role     = trim($_POST['role']         ?? '');
    $email    = trim($_POST['email']        ?? '');
    $cell     = trim($_POST['cell_number']  ?? '');

    $err = validateEmployeeInput($eid, $name, $desig, $division, $sub_div, $role, $email, $cell);
    if ($err) { echo json_encode(['success' => false, 'message' => $err]); return; }

    $conn = getConnection();

    $stmt = $conn->prepare("SELECT id FROM employees WHERE eid = ?");
    $stmt->bind_param('s', $eid);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close(); $conn->close();
        echo json_encode(['success' => false, 'message' => 'EID already exists. Please use a unique EID.']);
        return;
    }
    $stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO employees (eid, name, designation, division, sub_division, role, email, cell_number)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('ssssssss', $eid, $name, $desig, $division, $sub_div, $role, $email, $cell);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Employee added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add employee. Please try again.']);
    }
    $stmt->close();
    $conn->close();
}

function editEmployee() {
    $id       = intval($_POST['id']          ?? 0);
    $eid      = trim($_POST['eid']           ?? '');
    $name     = trim($_POST['name']          ?? '');
    $desig    = trim($_POST['designation']   ?? '');
    $division = trim($_POST['division']      ?? '');
    $sub_div  = trim($_POST['sub_division']  ?? '');
    $role     = trim($_POST['role']          ?? '');
    $email    = trim($_POST['email']         ?? '');
    $cell     = trim($_POST['cell_number']   ?? '');

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID.']);
        return;
    }

    $err = validateEmployeeInput($eid, $name, $desig, $division, $sub_div, $role, $email, $cell);
    if ($err) { echo json_encode(['success' => false, 'message' => $err]); return; }

    $conn = getConnection();

    $stmt = $conn->prepare("SELECT id FROM employees WHERE eid = ? AND id != ?");
    $stmt->bind_param('si', $eid, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close(); $conn->close();
        echo json_encode(['success' => false, 'message' => 'EID already used by another employee.']);
        return;
    }
    $stmt->close();

    $stmt = $conn->prepare(
        "UPDATE employees
         SET eid=?, name=?, designation=?, division=?, sub_division=?, role=?, email=?, cell_number=?
         WHERE id=?"
    );
    $stmt->bind_param('ssssssssi', $eid, $name, $desig, $division, $sub_div, $role, $email, $cell, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Employee updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update employee. Please try again.']);
    }
    $stmt->close();
    $conn->close();
}

function toggleStatus() {
    $id     = intval($_POST['id']     ?? 0);
    $status = trim($_POST['status']   ?? '');

    if ($id <= 0 || !in_array($status, ['active', 'inactive'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data.']);
        return;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE employees SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $verb = ($status === 'active') ? 'reactivated' : 'inactivated';
        echo json_encode(['success' => true, 'message' => "Employee {$verb} successfully."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
    }
    $stmt->close();
    $conn->close();
}
