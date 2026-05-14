<?php
session_start();
$isOperator      = !empty($_SESSION['operator_id']);
$operatorUsername = htmlspecialchars($_SESSION['operator_username'] ?? '', ENT_QUOTES);
$opCols           = $isOperator ? 11 : 10;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Archive</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:       #1a3a6c;
            --primary-dark:  #0f2347;
            --primary-light: #2563a8;
            --accent:        #e8b94a;
        }

        * { box-sizing: border-box; }

        body {
            background: #eef2f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
        }

        /* ── Header ── */
        .site-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 55%, var(--primary-light) 100%);
            box-shadow: 0 3px 12px rgba(0,0,0,.35);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            color: white;
            text-decoration: none;
        }
        .brand-icon {
            background: rgba(255,255,255,.14);
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 1.8rem;
            line-height: 1;
            flex-shrink: 0;
        }
        .brand-title   { font-size: 1.3rem; font-weight: 700; margin: 0; letter-spacing: .3px; }
        .header-right  { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .op-badge {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);
            color: white;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: .8rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-header {
            border-radius: 8px;
            font-size: .82rem;
            padding: 6px 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1.5px solid;
            cursor: pointer;
            transition: background .15s, opacity .15s;
        }
        .btn-login  { background: rgba(255,255,255,.14); border-color: rgba(255,255,255,.45); color: white; }
        .btn-login:hover  { background: rgba(255,255,255,.25); color: white; }
        .btn-logout { background: rgba(220,53,69,.75); border-color: rgba(220,53,69,.9); color: white; }
        .btn-logout:hover { background: #dc3545; color: white; }

        /* ── Page wrapper ── */
        .page-wrap {
            max-width: 1400px;
            margin: 0 auto;
            padding: 22px 20px;
        }

        /* ── Filter card ── */
        .filter-card {
            background: white;
            border-radius: 14px;
            padding: 18px 22px 22px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            margin-bottom: 20px;
        }
        .section-title {
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 14px;
        }
        .filter-label {
            font-size: .78rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 4px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1.5px solid #dde2ea;
            font-size: .855rem;
            height: 38px;
            padding: 0 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(37,99,168,.13);
        }
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
        }
        .form-select:disabled {
            background-color: #f8f9fa;
            color: #adb5bd;
            cursor: not-allowed;
        }
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* ── Table card ── */
        .table-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .table-head-bar {
            background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .table-head-bar h5 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .count-pill {
            background: rgba(255,255,255,.18);
            border-radius: 20px;
            padding: 2px 12px;
            font-size: .75rem;
            white-space: nowrap;
        }
        .emp-table { margin: 0; }
        .emp-table thead th {
            background: #f5f7fb;
            border-bottom: 2px solid #e2e8f0;
            color: #4a5568;
            font-weight: 700;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .55px;
            white-space: nowrap;
            padding: 11px 14px;
        }
        .emp-table tbody td {
            padding: 11px 14px;
            vertical-align: middle;
            font-size: .86rem;
            border-bottom: 1px solid #f0f4f8;
            color: #2d3748;
        }
        .emp-table tbody tr:last-child td { border-bottom: none; }
        .emp-table tbody tr:hover td { background: #f7faff; }
        .emp-table tbody tr.row-inactive td { color: #9ca3af; background: #fdf2f2; }
        .emp-table tbody tr.row-inactive:hover td { background: #fce8e8; }

        .eid-val  { font-family: 'Consolas', monospace; font-weight: 700; color: var(--primary); font-size: .83rem; }
        .emp-name { font-weight: 600; color: #1a202c; }

        /* Status badges */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .pill-active   { background: #d1fae5; color: #065f46; }
        .pill-inactive { background: #fee2e2; color: #991b1b; }

        /* Role badge */
        .role-pill {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            white-space: nowrap;
            background: #ede9fe;
            color: #5b21b6;
        }

        /* Action buttons */
        .btn-act {
            padding: 3px 9px;
            font-size: .75rem;
            border-radius: 6px;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Modal header */
        .modal-header {
            background: linear-gradient(90deg, var(--primary-dark), var(--primary));
            color: white;
            padding: 16px 22px;
        }
        .modal-header .btn-close { filter: brightness(0) invert(1); opacity: .7; }
        .modal-title { font-weight: 700; font-size: .95rem; }
        .modal-content { border: none; border-radius: 14px; overflow: hidden; }

        .modal-header-warn {
            background: linear-gradient(90deg, #854d0e, #ca8a04);
            color: white;
            padding: 16px 22px;
        }

        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: var(--primary-dark); border-color: var(--primary-dark); }

        /* Loading overlay */
        #loadingOverlay {
            position: fixed; inset: 0;
            background: rgba(15,35,71,.35);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .spinner-box {
            background: white;
            border-radius: 14px;
            padding: 22px 30px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 6px 30px rgba(0,0,0,.25);
            font-weight: 600;
            color: var(--primary);
        }

        /* Toast */
        .toast-wrap { position: fixed; top: 20px; right: 20px; z-index: 11000; min-width: 300px; }

        /* Empty state */
        .empty-state { text-align: center; padding: 56px 20px; color: #9ca3af; }
        .empty-state .ei { font-size: 2.8rem; display: block; margin-bottom: 10px; }

        /* Add btn gold accent */
        .btn-add {
            background: var(--accent);
            border-color: var(--accent);
            color: #1a1a1a;
            font-weight: 700;
            border-radius: 8px;
            font-size: .8rem;
            padding: 5px 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-add:hover { background: #d4a832; border-color: #d4a832; color: #1a1a1a; }

        /* Required field marker */
        .req { color: #dc3545; margin-left: 2px; }

        @media (max-width: 768px) {
            .header-inner  { padding: 10px 14px; }
            .brand-title   { font-size: 1rem; }
            .page-wrap     { padding: 12px 10px; }
            .table-responsive { overflow-x: auto; }
        }
    </style>
</head>
<body>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display:none;">
    <div class="spinner-box">
        <div class="spinner-border text-primary" role="status" style="width:1.6rem;height:1.6rem;"></div>
        <span>Please wait…</span>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- ══════════════ HEADER ══════════════ -->
<header class="site-header">
    <div class="header-inner">
        <div class="header-brand">
            <div class="brand-icon"><i class="bi bi-people-fill"></i></div>
            <div>
                <p class="brand-title">ICCD Officer's Portal</p>
            </div>
        </div>
        <div class="header-right">
            <?php if ($isOperator): ?>
                <span class="op-badge">
                    <i class="bi bi-person-badge-fill"></i>
                    Welcome, <strong><?= $operatorUsername ?></strong>
                </span>
                <button class="btn btn-header btn-logout" id="logoutBtn">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            <?php else: ?>
                <button class="btn btn-header btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="bi bi-lock-fill"></i> Operator Login
                </button>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- ══════════════ PAGE CONTENT ══════════════ -->
<div class="page-wrap">

    <!-- ── Filters ── -->
    <div class="filter-card">
        <div class="section-title"><i class="bi bi-funnel-fill"></i> Filter Employees</div>
        <div class="row g-3 align-items-end">

            <div class="col-6 col-md-3 col-lg-2">
                <div class="filter-label">Division</div>
                <select class="form-select" id="divisionFilter">
                    <option value="">All Divisions</option>
                </select>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <div class="filter-label">Sub Division</div>
                <select class="form-select" id="subdivisionFilter">
                    <option value="">All Sub Divisions</option>
                </select>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <div class="filter-label">Role</div>
                <select class="form-select" id="roleFilter">
                    <option value="">All Roles</option>
                </select>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <div class="filter-label">Name</div>
                <input type="text" class="form-control" id="nameFilter" placeholder="Search name…">
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <div class="filter-label">EID</div>
                <input type="text" class="form-control" id="eidFilter" placeholder="Search EID…">
            </div>

            <?php if ($isOperator): ?>
            <div class="col-6 col-md-3 col-lg-2 d-flex align-items-center" style="padding-top:20px;">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="showInactive">
                    <label class="form-check-label" for="showInactive" style="font-size:.84rem;cursor:pointer;">
                        Show Inactive
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-6 col-md-3 col-lg-2" style="padding-top:20px;">
                <button class="btn btn-outline-secondary w-100" id="resetFilters"
                        style="height:38px;font-size:.83rem;border-radius:8px;">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>

        </div>
    </div>

    <!-- ── Employee Table ── -->
    <div class="table-card">
        <div class="table-head-bar">
            <h5><i class="bi bi-table"></i> Officer's Directory</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="count-pill" id="recCount">— records</span>
                <?php if ($isOperator): ?>
                <button class="btn btn-add" id="addEmployeeBtn">
                    <i class="bi bi-plus-circle-fill"></i> Add Employee
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table emp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>EID</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Division</th>
                        <th>Sub Division</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Cell Number</th>
                        <th>Status</th>
                        <?php if ($isOperator): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="empTbody">
                    <tr>
                        <td colspan="<?= $opCols ?>" class="empty-state">
                            <i class="bi bi-hourglass-split ei"></i>
                            Loading employees…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /page-wrap -->


<!-- ══════════════ LOGIN MODAL ══════════════ -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shield-lock-fill me-2"></i>Operator Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="loginForm" autocomplete="off" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">Username</label>
                        <input type="text" class="form-control" id="loginUsername"
                               placeholder="Enter username" autocomplete="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="loginPassword"
                                   placeholder="Enter password" autocomplete="current-password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePwd"
                                    style="border-radius:0 8px 8px 0;">
                                <i class="bi bi-eye" id="togglePwdIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div id="loginErr" class="alert alert-danger py-2" style="display:none;font-size:.83rem;"></div>
                    <button type="submit" class="btn btn-primary w-100 mt-1" id="loginBtn"
                            style="border-radius:9px;font-weight:700;padding:10px;">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════ EMPLOYEE ADD / EDIT MODAL ══════════════ -->
<div class="modal fade" id="empModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="empModalTitle">
                    <i class="bi bi-person-plus-fill me-2"></i>Add Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="empForm" novalidate autocomplete="off">
                    <input type="hidden" id="empId">
                    <div class="row g-3">

                        <!-- Row 1: EID + Full Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                EID <span class="req">*</span>
                            </label>
                            <input type="text" class="form-control" id="fEid"
                                   placeholder="e.g. 3201" maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Full Name <span class="req">*</span>
                            </label>
                            <input type="text" class="form-control" id="fName"
                                   placeholder="Employee full name" maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Row 2: Designation + Role -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Designation <span class="req">*</span>
                            </label>
                            <select class="form-select" id="fDesig">
                                <option value="">Select Designation</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Role <span class="req">*</span>
                            </label>
                            <select class="form-select" id="fRole">
                                <option value="">Select Role</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Row 3: Division + Sub Division -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Division <span class="req">*</span>
                            </label>
                            <select class="form-select" id="fDiv">
                                <option value="">Select Division</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Sub Division <span class="req">*</span>
                            </label>
                            <select class="form-select" id="fSubDiv" disabled>
                                <option value="">Select Division first</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Row 4: Email + Cell Number -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Email <span class="req">*</span>
                            </label>
                            <input type="email" class="form-control" id="fEmail"
                                   placeholder="name@domain.com" maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Cell Number <span class="req">*</span>
                            </label>
                            <input type="text" class="form-control" id="fCell"
                                   placeholder="01XXXXXXXXX (10–15 digits)" maxlength="15">
                            <div class="invalid-feedback"></div>
                        </div>

                    </div><!-- /row -->
                    <div id="empFormErr" class="alert alert-danger py-2 mt-3"
                         style="display:none;font-size:.83rem;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="border-radius:8px;">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEmpBtn"
                        style="border-radius:8px;font-weight:700;">
                    <i class="bi bi-check-circle-fill me-1"></i>Save Employee
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════ CONFIRM MODAL ══════════════ -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header modal-header-warn">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter:brightness(0) invert(1);opacity:.7;"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p id="confirmMsg" class="mb-0" style="font-size:.9rem;line-height:1.6;"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                        style="border-radius:7px;min-width:80px;">Cancel</button>
                <button class="btn btn-sm" id="confirmOkBtn"
                        style="border-radius:7px;font-weight:700;min-width:100px;">Confirm</button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════ SCRIPTS ══════════════ -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script>
'use strict';

/* ── Globals ── */
const IS_OPERATOR = <?= $isOperator ? 'true' : 'false' ?>;
const OP_COLS     = <?= $opCols ?>;

let subdivisionsMap  = {};
let allSubdivisions  = [];
let allDivisions     = [];
let allDesignations  = [];
let allRoles         = [];
let editingId        = null;
let filterTimer      = null;
let pendingAction    = null;

let loginModal, empModal, confirmModal;

/* ══════════════ INIT ══════════════ */
$(function () {
    loginModal   = new bootstrap.Modal('#loginModal');
    empModal     = new bootstrap.Modal('#empModal');
    confirmModal = new bootstrap.Modal('#confirmModal');

    loadFilterData();
    loadEmployees();

    /* ── Filter events ── */
    $('#divisionFilter').on('change', onFilterDivisionChange);
    $('#subdivisionFilter').on('change', onFilterSubdivChange);
    $('#roleFilter').on('change', scheduleFilter);
    $('#nameFilter, #eidFilter').on('input', scheduleFilter);
    <?php if ($isOperator): ?>
    $('#showInactive').on('change', loadEmployees);
    <?php endif; ?>
    $('#resetFilters').on('click', resetFilters);

    /* ── Login ── */
    $('#loginForm').on('submit', function (e) { e.preventDefault(); doLogin(); });
    $('#togglePwd').on('click', function () {
        const el   = document.getElementById('loginPassword');
        const icon = document.getElementById('togglePwdIcon');
        el.type = (el.type === 'password') ? 'text' : 'password';
        icon.className = (el.type === 'text') ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
    $('#loginModal').on('hidden.bs.modal', function () {
        $('#loginErr').hide();
        $('#loginForm')[0].reset();
    });

    /* ── Operator controls ── */
    <?php if ($isOperator): ?>
    $('#logoutBtn').on('click', doLogout);
    $('#addEmployeeBtn').on('click', openAddModal);
    $('#saveEmpBtn').on('click', saveEmployee);

    $('#empTbody').on('click', '.btn-edit', function () {
        openEditModal($(this).data('id'));
    });
    $('#empTbody').on('click', '.btn-toggle', function () {
        confirmToggle($(this).data('id'), $(this).data('status'), $(this).data('name'));
    });

    $('#confirmOkBtn').on('click', function () {
        if (pendingAction) {
            doToggleStatus(pendingAction.id, pendingAction.newStatus);
            confirmModal.hide();
            pendingAction = null;
        }
    });

    setInterval(sessionCheck, 300000);
    <?php endif; ?>

    /* ── Form division change → update sub_division select ── */
    $('#fDiv').on('change', onFormDivisionChange);

    /* ── Reset modal on close ── */
    $('#empModal').on('hidden.bs.modal', function () {
        clearFormErrors();
        $('#empForm')[0].reset();
        $('#fSubDiv')
            .find('option:not(:first)').remove().end()
            .find('option:first').text('Select Division first').end()
            .prop('disabled', true).val('');
    });

    /* ── Real-time per-field validation ── */
    $('#fEid, #fName, #fEmail, #fCell').on('input', function () { clearFieldError(this); });
    $('#fDesig, #fDiv, #fSubDiv, #fRole').on('change', function () { clearFieldError(this); });
});

/* ══════════════ FILTER DATA ══════════════ */
function loadFilterData() {
    $.get('api.php', { action: 'get_filter_data' }, function (res) {
        if (!res.success) return;

        subdivisionsMap = res.subdivisions      || {};
        allSubdivisions = res.all_subdivisions  || [];
        allDivisions    = res.divisions         || [];
        allDesignations = res.designations      || [];
        allRoles        = res.roles             || [];

        /* Filter: division */
        const $df = $('#divisionFilter');
        $df.find('option:not(:first)').remove();
        allDivisions.forEach(d => $df.append(new Option(d, d)));

        /* Filter: role */
        const $rf = $('#roleFilter');
        $rf.find('option:not(:first)').remove();
        allRoles.forEach(r => $rf.append(new Option(r, r)));

        /* Filter: sub_division (all) */
        refreshFilterSubdivOptions('');
    }, 'json');
}

function onFilterDivisionChange() {
    const div  = $(this).val();
    const $sub = $('#subdivisionFilter');
    $sub.find('option:not(:first)').remove().end().val('');

    if (div === '') {
        /* Restore all sub-divisions and all roles */
        refreshFilterSubdivOptions('');
        updateRoleFilter('', '');
        scheduleFilter();
        return;
    }

    $.get('api.php', { action: 'get_subdivisions', division: div }, function (data) {
        if (data.success) {
            data.subdivisions.forEach(s => $sub.append(new Option(s, s)));
        }
        /* Show only roles that exist in this division */
        updateRoleFilter(div, '');
        scheduleFilter();
    }, 'json');
}

function onFilterSubdivChange() {
    const div = $('#divisionFilter').val();
    const sub = $(this).val();
    /* Narrow or widen roles based on current division + sub_division */
    updateRoleFilter(div, sub);
    scheduleFilter();
}

function updateRoleFilter(division, subDiv) {
    const prevRole = $('#roleFilter').val();

    $.get('api.php', {
        action:       'get_roles',
        division:     division || '',
        sub_division: subDiv   || ''
    }, function (res) {
        if (!res.success) return;

        const $rf = $('#roleFilter');
        $rf.find('option:not(:first)').remove();
        res.roles.forEach(r => $rf.append(new Option(r, r)));

        /* Restore previous selection only if it still appears in the narrowed list */
        const stillValid = res.roles.includes(prevRole);
        $rf.val(stillValid ? prevRole : '');

        /* If the previously-selected role was removed, re-run the employee query */
        if (prevRole && !stillValid) scheduleFilter();
    }, 'json');
}

function refreshFilterSubdivOptions(division) {
    const $sel = $('#subdivisionFilter');
    $sel.find('option:not(:first)').remove();
    const list = (division && subdivisionsMap[division])
        ? subdivisionsMap[division]
        : allSubdivisions;
    list.forEach(s => $sel.append(new Option(s, s)));
}

/* ══════════════ FORM DROPDOWNS ══════════════ */
function populateFormDropdowns() {
    /* Designation */
    const $d = $('#fDesig');
    $d.find('option:not(:first)').remove();
    allDesignations.forEach(d => $d.append(new Option(d, d)));

    /* Division */
    const $div = $('#fDiv');
    $div.find('option:not(:first)').remove();
    allDivisions.forEach(d => $div.append(new Option(d, d)));

    /* Sub Division – reset & disable until division selected */
    $('#fSubDiv')
        .find('option:not(:first)').remove().end()
        .find('option:first').text('Select Division first').end()
        .prop('disabled', true).val('');

    /* Role */
    const $r = $('#fRole');
    $r.find('option:not(:first)').remove();
    allRoles.forEach(r => $r.append(new Option(r, r)));
}

function onFormDivisionChange() {
    const div  = $(this).val();
    const $sub = $('#fSubDiv');

    $sub.find('option:not(:first)').remove();
    $sub.val('').prop('disabled', true);
    clearFieldError($sub[0]);

    if (!div) {
        $sub.find('option:first').text('Select Division first');
        return;
    }

    $sub.find('option:first').text('Select Sub Division');
    const subs = subdivisionsMap[div] || [];
    if (subs.length > 0) {
        subs.forEach(s => $sub.append(new Option(s, s)));
        $sub.prop('disabled', false);
    }
    /* If the division has no recorded sub_divisions, sub stays disabled */
}

function setSelectValue(selector, value) {
    const $sel = $(selector);
    if (value) {
        const alreadyPresent = $sel.find('option').toArray()
            .some(o => o.value === value);
        if (!alreadyPresent) $sel.append(new Option(value, value));
    }
    $sel.val(value || '');
}

/* ══════════════ LOAD EMPLOYEES ══════════════ */
function loadEmployees() {
    const params = {
        action:        'get_employees',
        division:      $('#divisionFilter').val(),
        sub_division:  $('#subdivisionFilter').val(),
        role:          $('#roleFilter').val(),
        name:          $('#nameFilter').val(),
        eid:           $('#eidFilter').val(),
        show_inactive: ($('#showInactive').length && $('#showInactive').is(':checked')) ? '1' : '0'
    };

    showSpinner();
    $.get('api.php', params)
        .done(function (res) {
            hideSpinner();
            if (res.success) renderTable(res.data);
            else showToast('Failed to load employees.', 'danger');
        })
        .fail(function () {
            hideSpinner();
            showToast('Server error while loading employees.', 'danger');
        });
}

function scheduleFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(loadEmployees, 320);
}

function resetFilters() {
    $('#divisionFilter').val('');
    $('#subdivisionFilter').val('');
    refreshFilterSubdivOptions('');
    $('#nameFilter').val('');
    $('#eidFilter').val('');
    if ($('#showInactive').length) $('#showInactive').prop('checked', false);
    /* Restore full role list, then reload employees */
    updateRoleFilter('', '');
    loadEmployees();
}

/* ══════════════ RENDER TABLE ══════════════ */
function renderTable(employees) {
    const $tbody = $('#empTbody');
    $tbody.empty();
    $('#recCount').text(employees.length + ' record' + (employees.length !== 1 ? 's' : ''));

    if (employees.length === 0) {
        $tbody.append(
            $('<tr>').append(
                $('<td>').attr('colspan', OP_COLS).html(
                    '<div class="empty-state">' +
                    '<i class="bi bi-inbox ei"></i>' +
                    '<p class="fw-semibold mb-1">No employees found</p>' +
                    '<small>Try adjusting your filters</small>' +
                    '</div>'
                )
            )
        );
        return;
    }

    employees.forEach(function (emp, idx) {
        const inactive = emp.status === 'inactive';
        const $tr = $('<tr>').toggleClass('row-inactive', inactive);

        $tr.append($('<td>').text(idx + 1));
        $tr.append($('<td>').append($('<span>').addClass('eid-val').text(emp.eid || '')));
        $tr.append($('<td>').append($('<span>').addClass('emp-name').text(emp.name || '')));
        $tr.append($('<td>').text(emp.designation || ''));
        $tr.append($('<td>').text(emp.division || ''));
        $tr.append($('<td>').text(emp.sub_division || ''));
        $tr.append($('<td>').append(
            emp.role
                ? $('<span>').addClass('role-pill').text(emp.role)
                : $('<span>').text('')
        ));
        $tr.append($('<td>').text(emp.email || ''));
        $tr.append($('<td>').text(emp.cell_number || ''));

        const pillClass = inactive ? 'pill-inactive' : 'pill-active';
        const pillIcon  = inactive ? 'bi-x-circle-fill' : 'bi-check-circle-fill';
        const pillLabel = inactive ? 'Inactive' : 'Active';
        $tr.append(
            $('<td>').append(
                $('<span>').addClass('status-pill ' + pillClass).html(
                    '<i class="bi ' + pillIcon + '"></i> ' + pillLabel
                )
            )
        );

        if (IS_OPERATOR) {
            const $tdAct = $('<td>');
            $tdAct.append(
                $('<button>')
                    .addClass('btn btn-sm btn-outline-primary btn-act btn-edit me-1')
                    .attr('data-id', emp.id)
                    .html('<i class="bi bi-pencil-fill"></i> Edit')
            );
            if (inactive) {
                $tdAct.append(
                    $('<button>')
                        .addClass('btn btn-sm btn-outline-success btn-act btn-toggle')
                        .attr({ 'data-id': emp.id, 'data-status': 'active', 'data-name': emp.name })
                        .html('<i class="bi bi-arrow-up-circle-fill"></i> Reactivate')
                );
            } else {
                $tdAct.append(
                    $('<button>')
                        .addClass('btn btn-sm btn-outline-danger btn-act btn-toggle')
                        .attr({ 'data-id': emp.id, 'data-status': 'inactive', 'data-name': emp.name })
                        .html('<i class="bi bi-slash-circle-fill"></i> Inactivate')
                );
            }
            $tr.append($tdAct);
        }

        $tbody.append($tr);
    });
}

/* ══════════════ ADD / EDIT ══════════════ */
function openAddModal() {
    editingId = null;
    clearFormErrors();
    $('#empModalTitle').html('<i class="bi bi-person-plus-fill me-2"></i>Add Employee');
    $('#empForm')[0].reset();
    $('#empId').val('');
    populateFormDropdowns();
    empModal.show();
}

function openEditModal(id) {
    editingId = id;
    clearFormErrors();
    $('#empModalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Employee');
    populateFormDropdowns();

    showSpinner();
    $.get('api.php', { action: 'get_employee', id: id })
        .done(function (res) {
            hideSpinner();
            if (!res.success || !res.data) {
                showToast('Failed to load employee data.', 'danger');
                return;
            }
            const e = res.data;

            $('#empId').val(e.id);
            $('#fEid').val(e.eid    || '');
            $('#fName').val(e.name  || '');
            $('#fEmail').val(e.email || '');
            $('#fCell').val(e.cell_number || '');

            setSelectValue('#fDesig', e.designation);

            /* Division first, then sub_division */
            setSelectValue('#fDiv', e.division);
            const $sub = $('#fSubDiv');
            $sub.find('option:not(:first)').remove();
            $sub.find('option:first').text('Select Sub Division');
            if (e.division) {
                const subs = subdivisionsMap[e.division] || [];
                subs.forEach(s => $sub.append(new Option(s, s)));
                /* If employee's sub_division is not in the mapped list, add it */
                if (e.sub_division && !subs.includes(e.sub_division)) {
                    $sub.append(new Option(e.sub_division, e.sub_division));
                }
                $sub.prop('disabled', false);
            }
            $sub.val(e.sub_division || '');

            setSelectValue('#fRole', e.role);

            empModal.show();
        })
        .fail(handleAjaxFail);
}

/* ══════════════ SAVE EMPLOYEE ══════════════ */
function saveEmployee() {
    clearFormErrors();

    const eid   = $('#fEid').val().trim();
    const name  = $('#fName').val().trim();
    const desig = $('#fDesig').val();
    const div   = $('#fDiv').val();
    const subDisabled = $('#fSubDiv').prop('disabled');
    const sub   = subDisabled ? '' : ($('#fSubDiv').val() || '');
    const role  = $('#fRole').val();
    const email = $('#fEmail').val().trim();
    const cell  = $('#fCell').val().trim();

    let valid = true;
    const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!eid)   { setFieldError('#fEid',   'EID is required.');            valid = false; }
    if (!name)  { setFieldError('#fName',  'Full Name is required.');      valid = false; }
    if (!desig) { setFieldError('#fDesig', 'Please select a Designation.'); valid = false; }
    if (!div)   { setFieldError('#fDiv',   'Please select a Division.');    valid = false; }
    if (!subDisabled && !sub) {
        setFieldError('#fSubDiv', 'Please select a Sub Division.');         valid = false;
    }
    if (!role)  { setFieldError('#fRole',  'Please select a Role.');       valid = false; }
    if (!email) {
        setFieldError('#fEmail', 'Email is required.');                     valid = false;
    } else if (!emailRx.test(email)) {
        setFieldError('#fEmail', 'Enter a valid email (e.g. name@domain.com).'); valid = false;
    }
    if (!cell) {
        setFieldError('#fCell', 'Cell Number is required.');                valid = false;
    } else if (!/^\d{10,15}$/.test(cell)) {
        setFieldError('#fCell', 'Must be 10–15 digits, numbers only.');     valid = false;
    }

    if (!valid) return;

    const data = {
        action:       editingId ? 'edit_employee' : 'add_employee',
        id:           editingId || '',
        eid,
        name,
        designation:  desig,
        division:     div,
        sub_division: sub,
        role,
        email,
        cell_number:  cell
    };

    showSpinner();
    $('#saveEmpBtn').prop('disabled', true);

    $.post('api.php', data)
        .done(function (res) {
            hideSpinner();
            $('#saveEmpBtn').prop('disabled', false);
            if (res.success) {
                empModal.hide();
                showToast(res.message, 'success');
                loadEmployees();
                loadFilterData();
            } else {
                if (res.session_expired) { onSessionExpired(); return; }
                $('#empFormErr').text(res.message).show();
            }
        })
        .fail(function (xhr) {
            hideSpinner();
            $('#saveEmpBtn').prop('disabled', false);
            if (xhr.status === 401) onSessionExpired();
            else showToast('Server error. Please try again.', 'danger');
        });
}

/* ══════════════ TOGGLE STATUS ══════════════ */
function confirmToggle(id, newStatus, empName) {
    pendingAction = { id, newStatus };
    const verb = newStatus === 'active' ? 'reactivate' : 'inactivate';
    const safe = $('<span>').text(empName).html();
    $('#confirmMsg').html(
        'Are you sure you want to <strong>' + verb + '</strong> employee<br><strong>' + safe + '</strong>?'
    );
    const $btn = $('#confirmOkBtn');
    $btn.text(newStatus === 'active' ? 'Reactivate' : 'Inactivate');
    $btn.removeClass('btn-success btn-warning btn-danger')
        .addClass(newStatus === 'active' ? 'btn-success' : 'btn-warning');
    confirmModal.show();
}

function doToggleStatus(id, newStatus) {
    showSpinner();
    $.post('api.php', { action: 'toggle_status', id, status: newStatus })
        .done(function (res) {
            hideSpinner();
            if (res.success) {
                showToast(res.message, 'success');
                loadEmployees();
            } else {
                if (res.session_expired) { onSessionExpired(); return; }
                showToast(res.message || 'Failed to update status.', 'danger');
            }
        })
        .fail(function (xhr) {
            hideSpinner();
            if (xhr.status === 401) onSessionExpired();
            else showToast('Server error. Please try again.', 'danger');
        });
}

/* ══════════════ AUTH ══════════════ */
function doLogin() {
    const username = $('#loginUsername').val().trim();
    const password = $('#loginPassword').val();
    if (!username || !password) {
        $('#loginErr').text('Please enter username and password.').show();
        return;
    }
    $('#loginBtn').prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-1"></span> Logging in…');
    $('#loginErr').hide();

    $.post('api.php', { action: 'login', username, password })
        .done(function (res) {
            $('#loginBtn').prop('disabled', false)
                .html('<i class="bi bi-box-arrow-in-right me-1"></i>Login');
            if (res.success) {
                loginModal.hide();
                showToast('Welcome, ' + res.username + '! Reloading…', 'success');
                setTimeout(() => location.reload(), 900);
            } else {
                $('#loginErr').text(res.message).show();
                $('#loginPassword').val('').focus();
            }
        })
        .fail(function () {
            $('#loginBtn').prop('disabled', false)
                .html('<i class="bi bi-box-arrow-in-right me-1"></i>Login');
            $('#loginErr').text('Server error. Please try again.').show();
        });
}

function doLogout() {
    showSpinner();
    $.post('api.php', { action: 'logout' }).always(function () {
        hideSpinner();
        showToast('Logged out. Returning to viewer mode…', 'info');
        setTimeout(() => location.reload(), 700);
    });
}

function sessionCheck() {
    $.get('api.php', { action: 'check_session' }, function (res) {
        if (res.success && !res.logged_in) onSessionExpired();
    }, 'json');
}

function onSessionExpired() {
    showToast('Your session has expired. Returning to viewer mode…', 'warning');
    setTimeout(() => location.reload(), 2200);
}

/* ══════════════ FORM VALIDATION HELPERS ══════════════ */
function setFieldError(selector, msg) {
    const $el = $(selector);
    $el.addClass('is-invalid');
    $el.siblings('.invalid-feedback').first().text(msg);
}

function clearFieldError(el) {
    $(el).removeClass('is-invalid')
        .siblings('.invalid-feedback').first().text('');
}

function clearFormErrors() {
    $('#empForm .is-invalid').removeClass('is-invalid');
    $('#empForm .invalid-feedback').text('');
    $('#empFormErr').hide();
}

/* ══════════════ GENERAL HELPERS ══════════════ */
function handleAjaxFail(xhr) {
    hideSpinner();
    if (xhr.status === 401) onSessionExpired();
    else showToast('Server error. Please try again.', 'danger');
}

function showSpinner() { $('#loadingOverlay').show(); }
function hideSpinner() { $('#loadingOverlay').hide(); }

function showToast(message, type) {
    const icons = {
        success: 'bi-check-circle-fill',
        danger:  'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info:    'bi-info-circle-fill'
    };
    const icon    = icons[type] || icons.info;
    const toastId = 'toast_' + Date.now() + '_' + Math.random().toString(36).slice(2);
    const safe    = $('<span>').text(message).html();

    const $t = $(`
        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-2"
             role="alert" aria-atomic="true"
             style="min-width:280px;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.2);">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2" style="font-size:.875rem;">
                    <i class="bi ${icon} flex-shrink-0"></i>
                    <span>${safe}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>`);

    $('#toastWrap').append($t);
    const instance = new bootstrap.Toast(document.getElementById(toastId), { delay: 4500 });
    instance.show();
    $t.on('hidden.bs.toast', function () { $(this).remove(); });
}
</script>
</body>
</html>
