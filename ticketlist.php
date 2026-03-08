<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tickets WHERE id = $id");
    header("Location: ticket_list.php");
    exit;
}

// Search / filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$priority_filter = isset($_GET['priority']) ? mysqli_real_escape_string($conn, $_GET['priority']) : '';

$where = "WHERE 1=1";
if ($search)          $where .= " AND (client_name LIKE '%$search%' OR service_request_no LIKE '%$search%' OR department LIKE '%$search%')";
if ($priority_filter) $where .= " AND priority_level = '$priority_filter'";

$result = mysqli_query($conn, "SELECT * FROM tickets $where ORDER BY id DESC");
$total  = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket List - National Housing Authority</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* ── List-page specific styles ── */
        .list-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #222;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        }

        .list-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #f0ede6;
            border-bottom: 2px solid #555;
            flex-wrap: wrap;
        }

        .list-toolbar a.btn-new {
            background: #2a2a2a;
            color: #fff;
            text-decoration: none;
            padding: 7px 18px;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
            font-family: 'Source Serif 4', Georgia, serif;
        }

        .list-toolbar a.btn-new:hover { background: #444; }

        .list-toolbar input[type="text"] {
            flex: 1;
            min-width: 160px;
            padding: 6px 10px;
            border: 1px solid #aaa;
            font-size: 13px;
            font-family: 'Source Sans 3', Arial, sans-serif;
            background: #fff;
        }

        .list-toolbar select {
            padding: 6px 10px;
            border: 1px solid #aaa;
            font-size: 13px;
            background: #fff;
            font-family: 'Source Sans 3', Arial, sans-serif;
        }

        .list-toolbar button {
            background: #2a2a2a;
            color: #fff;
            border: none;
            padding: 7px 16px;
            font-size: 12px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            font-family: 'Source Serif 4', Georgia, serif;
        }

        .list-toolbar button:hover { background: #444; }

        .list-toolbar .total {
            margin-left: auto;
            font-size: 12px;
            color: #555;
            white-space: nowrap;
            font-style: italic;
        }

        /* Table */
        .ticket-table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        thead tr {
            background: #2a2a2a;
            color: #fff;
        }

        thead th {
            padding: 8px 12px;
            text-align: left;
            font-family: 'Source Serif 4', Georgia, serif;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-size: 11px;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid #e0ddd6;
            transition: background 0.15s;
        }

        tbody tr:nth-child(even) { background: #faf8f4; }
        tbody tr:hover           { background: #f0ede6; }

        tbody td {
            padding: 7px 12px;
            vertical-align: middle;
            color: #222;
        }

        /* Priority badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid;
        }

        .badge-low      { color: #2a7a2a; border-color: #2a7a2a; background: #edfaed; }
        .badge-medium   { color: #7a6200; border-color: #c9a000; background: #fffbe6; }
        .badge-high     { color: #b84000; border-color: #e06000; background: #fff3eb; }
        .badge-critical { color: #fff;    border-color: #a00;    background: #a00; }

        /* Action buttons */
        .btn-view {
            display: inline-block;
            padding: 3px 10px;
            font-size: 11px;
            text-decoration: none;
            border: 1px solid #2a2a2a;
            color: #2a2a2a;
            background: #fff;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            transition: background 0.15s, color 0.15s;
        }
        .btn-view:hover { background: #2a2a2a; color: #fff; }

        .btn-delete {
            display: inline-block;
            padding: 3px 10px;
            font-size: 11px;
            text-decoration: none;
            border: 1px solid #a00;
            color: #a00;
            background: #fff;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            transition: background 0.15s, color 0.15s;
            cursor: pointer;
        }
        .btn-delete:hover { background: #a00; color: #fff; }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
            font-size: 14px;
        }

        /* Modal overlay for view */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 100;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
            overflow-y: auto;
        }
        .modal-overlay.active { display: flex; }

        .modal {
            background: #fff;
            border: 2px solid #222;
            max-width: 760px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: #2a2a2a;
            color: #fff;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
        }

        .modal-body {
            padding: 16px;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid #ccc;
        }

        .modal-field {
            display: flex;
            border-bottom: 1px solid #e0ddd6;
            border-right: 1px solid #e0ddd6;
        }

        .modal-field:nth-child(even) { border-right: none; }

        .modal-field.full {
            grid-column: 1 / -1;
            border-right: none;
        }

        .modal-field label {
            background: #f0ede6;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 5px 10px;
            min-width: 130px;
            border-right: 1px solid #ccc;
            color: #444;
            display: flex;
            align-items: center;
        }

        .modal-field span {
            padding: 5px 10px;
            font-size: 12.5px;
            color: #111;
            flex: 1;
        }

        .modal-section-head {
            grid-column: 1 / -1;
            background: #2a2a2a;
            color: #fff;
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
<div class="list-wrapper">

    <h1 class="title nha">
        National Housing Authority
        <sub>IT Support — Ticket List</sub>
    </h1>

    <!-- Toolbar -->
    <form method="GET" action="ticket_list.php">
        <div class="list-toolbar">
            <a href="create_ticket.php" class="btn-new">+ New Ticket</a>

            <input type="text" name="search" placeholder="Search by name, request no., dept…"
                   value="<?= htmlspecialchars($search) ?>">

            <select name="priority">
                <option value="">All Priorities</option>
                <option value="Low"      <?= $priority_filter === 'Low'      ? 'selected' : '' ?>>Low</option>
                <option value="Medium"   <?= $priority_filter === 'Medium'   ? 'selected' : '' ?>>Medium</option>
                <option value="High"     <?= $priority_filter === 'High'     ? 'selected' : '' ?>>High</option>
                <option value="Critical" <?= $priority_filter === 'Critical' ? 'selected' : '' ?>>Critical</option>
            </select>

            <button type="submit">Filter</button>
            <?php if ($search || $priority_filter): ?>
                <a href="ticket_list.php" style="font-size:12px;color:#a00;text-decoration:none;">✕ Clear</a>
            <?php endif; ?>

            <span class="total"><?= $total ?> ticket<?= $total !== 1 ? 's' : '' ?></span>
        </div>
    </form>

    <!-- Table -->
    <div class="ticket-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Request No.</th>
                    <th>Date</th>
                    <th>Client Name</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($total === 0): ?>
                <tr><td colspan="8" class="no-results">No tickets found.</td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['service_request_no']) ?></td>
                    <td><?= htmlspecialchars($row['date_created']) ?></td>
                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                    <td><?= htmlspecialchars($row['department']) ?></td>
                    <td><?= htmlspecialchars($row['type']) ?></td>
                    <td>
                        <?php
                        $p = $row['priority_level'];
                        $cls = match(strtolower($p)) {
                            'low'      => 'badge-low',
                            'medium'   => 'badge-medium',
                            'high'     => 'badge-high',
                            'critical' => 'badge-critical',
                            default    => ''
                        };
                        ?>
                        <span class="badge <?= $cls ?>"><?= htmlspecialchars($p) ?></span>
                    </td>
                    <td>
                        <?php
                        $st = $row['status'] ?? 'Open';
                        $scls = match(strtolower(trim($st))) {
                            'open'                 => 'badge-high',
                            'in-progress'          => 'badge-medium',
                            'for parts'            => 'badge-parts',
                            'for replacement'      => 'badge-replacement',
                            'endorsed to supplier' => 'badge-supplier',
                            'unrepairable'         => 'badge-unrepairable',
                            'resolved'             => 'badge-low',
                            'closed'               => 'badge-closed',
                            default                => ''
                        };
                        ?>
                        <span class="badge <?= $scls ?>"><?= htmlspecialchars($st) ?></span>
                    </td>
                    <td style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="#" class="btn-view" onclick="openModal(<?= htmlspecialchars(json_encode($row)) ?>); return false;">View</a>
                        <a href="ticket_list.php?delete=<?= $row['id'] ?>"
                           class="btn-delete"
                           onclick="return confirm('Delete this ticket?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div><!-- /list-wrapper -->

<!-- VIEW MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="modal" id="modalBox">
        <div class="modal-header">
            <span>Ticket Details</span>
            <button class="modal-close" onclick="document.getElementById('modalOverlay').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-grid" id="modalGrid"></div>
        </div>
    </div>
</div>

<script>
function openModal(row) {
    const grid = document.getElementById('modalGrid');
    const f = (label, val) => `
        <div class="modal-field">
            <label>${label}</label>
            <span>${val || '—'}</span>
        </div>`;
    const fFull = (label, val) => `
        <div class="modal-field full">
            <label>${label}</label>
            <span>${val || '—'}</span>
        </div>`;
    const head = (title) => `<div class="modal-section-head">${title}</div>`;

    grid.innerHTML =
        head('Client Information') +
        f('Request No.', row.service_request_no) +
        f('Date', row.date_created) +
        fFull('Department', row.department) +
        f('Client Name', row.client_name) +
        f('Position', row.position_designation) +
        f('Contact', row.contact_number) +
        f('Email', row.email) +
        f('Priority', row.priority_level) +

        head('Hardware / Item') +
        f('Type', row.type) +
        f('Brand / Model', row.brand_model) +
        f('Warranty', row.warranty) +
        f('Property No.', row.property_number) +
        f('Serial No.', row.serial_number) +
        f('Year Acquired', row.year_acquired) +
        f('Active Directory', row.active_directory) +
        f('Memory Type', row.memory_type) +
        f('Memory Speed', row.memory_speed) +
        f('Storage Type', row.storage_type) +

        head('Support Type') +
        f('Support Type', row.support_type) +
        f('Hardware', [
            row.hw_install  == 1 ? 'Installation'           : '',
            row.hw_repair   == 1 ? 'Repair'                 : '',
            row.hw_assembly == 1 ? 'Assembly'               : '',
            row.hw_pm       == 1 ? 'Preventive Maintenance' : '',
            row.hw_others   == 1 ? 'Others'                 : '',
        ].filter(Boolean).join(', ') || '—') +
        f('Software', [
            row.sw_install == 1 ? 'Installation' : '',
            row.sw_repair  == 1 ? 'Repair'       : '',
            row.sw_update  == 1 ? 'Updating'     : '',
            row.sw_format  == 1 ? 'Formatting'   : '',
            row.sw_others  == 1 ? 'Others'       : '',
        ].filter(Boolean).join(', ') || '—') +
        f('Network & Maint.', [
            row.nm_vc     == 1 ? 'Video Conferencing'  : '',
            row.nm_tu     == 1 ? 'Tune-up/OS Updating' : '',
            row.nm_vs     == 1 ? 'Virus Scanning'      : '',
            row.nm_ns     == 1 ? 'Network/Sharing'     : '',
            row.nm_others == 1 ? 'Others'              : '',
        ].filter(Boolean).join(', ') || '—') +

        head('Resolution') +
        fFull('Details / Scenario', row.details) +
        fFull('Diagnosis', row.diagnosis) +
        fFull('Actions Taken', row.actions_taken) +
        fFull('Technical Personnel', row.tech_personnel) +
        fFull('Accepted By', row.accepted_by);

    document.getElementById('modalOverlay').classList.add('active');
}

function closeModal(e) {
    if (e.target === document.getElementById('modalOverlay')) {
        document.getElementById('modalOverlay').classList.remove('active');
    }
}
</script>
<script>
(function(){
    var btn=document.getElementById("sidebarToggle");
    var sidebar=document.getElementById("sidebar");
    var overlay=document.getElementById("sidebarOverlay");
    function openS(){ sidebar.classList.add("open"); overlay.classList.add("active"); }
    function closeS(){ sidebar.classList.remove("open"); overlay.classList.remove("active"); }
    btn.addEventListener("click",function(){ sidebar.classList.contains("open")?closeS():openS(); });
    overlay.addEventListener("click",closeS);
})();
</script>
</body>
</html>
