<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';

$success = '';
$error   = '';
$ticket  = null;

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    $id        = (int) $_POST['ticket_id'];
    $status    = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis'] ?? '');
    $actions   = mysqli_real_escape_string($conn, $_POST['actions_taken'] ?? '');
    $tech      = mysqli_real_escape_string($conn, $_POST['tech_personnel'] ?? '');
    $accepted  = mysqli_real_escape_string($conn, $_POST['accepted_by'] ?? '');

    $sql = "UPDATE tickets SET
        status         = '$status',
        diagnosis      = '$diagnosis',
        actions_taken  = '$actions',
        tech_personnel = '$tech',
        accepted_by    = '$accepted'
        WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        $success = "Ticket #$id updated successfully.";
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}

// Load selected ticket (from GET or after POST)
$selected_id = (int)($_POST['ticket_id'] ?? $_GET['id'] ?? 0);
if ($selected_id > 0) {
    $result = mysqli_query($conn, "SELECT * FROM tickets WHERE id = $selected_id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $ticket = mysqli_fetch_assoc($result);
    }
}

// Load ALL tickets for the dropdown
$all_tickets = mysqli_query($conn, "SELECT id, service_request_no, client_name, priority_level, status FROM tickets ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Ticket - NHA IT Support</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .update-wrap {
            max-width: 860px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #222;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        }

        /* Selector bar */
        .search-bar {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #555;
            background: #fdfcfa;
        }

        .search-bar select {
            flex: 1;
            border: none;
            padding: 10px 14px;
            font-size: 13px;
            font-family: 'Source Sans 3', Arial, sans-serif;
            background: #fdfcfa;
            outline: none;
            cursor: pointer;
            color: #222;
        }

        .search-bar select:focus { background: #f7f5ef; }

        /* Alerts */
        .alert {
            padding: 10px 16px;
            font-size: 13px;
            border-left: 4px solid;
            margin: 12px 16px;
        }
        .alert-success { background: #edfaed; border-color: #2a7a2a; color: #2a7a2a; }
        .alert-error   { background: #fff0f0; border-color: #a00;    color: #a00; }

        /* Ticket summary (read-only info) */
        .ticket-summary {
            margin: 0;
            border-bottom: 2px solid #555;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
        }

        .summary-field {
            display: flex;
            flex-direction: column;
            border-right: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 0;
        }

        .summary-field:nth-child(3n) { border-right: none; }

        .summary-field label {
            background: #f0ede6;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #555;
            padding: 4px 10px;
            border-bottom: 1px solid #ddd;
        }

        .summary-field span {
            padding: 6px 10px;
            font-size: 13px;
            color: #111;
        }

        /* Update form fields */
        .update-form {
            padding: 0;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid #ddd;
        }

        .form-field label {
            background: #f0ede6;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #444;
            padding: 5px 12px;
            border-bottom: 1px solid #ccc;
        }

        .form-field select,
        .form-field textarea {
            border: none;
            padding: 8px 12px;
            font-size: 13px;
            font-family: 'Source Sans 3', Arial, sans-serif;
            background: #fdfcfa;
            outline: none;
            resize: vertical;
        }

        .form-field select:focus,
        .form-field textarea:focus {
            background: #f7f5ef;
        }

        .form-field select { cursor: pointer; }

        /* Two-col form row */
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid #ddd;
        }

        .form-row-2 .form-field {
            border-bottom: none;
            border-right: 2px solid #555;
        }

        .form-row-2 .form-field:last-child { border-right: none; }

        /* Submit row */
        .update-submit {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f0ede6;
            border-top: 3px double #222;
        }

        .update-submit a {
            font-size: 12px;
            color: #555;
            text-decoration: none;
            letter-spacing: 0.04em;
        }

        .update-submit a:hover { color: #222; }

        .update-submit input[type="submit"] {
            background: #2a2a2a;
            color: #fff;
            border: none;
            padding: 9px 32px;
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 13px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
        }

        .update-submit input[type="submit"]:hover { background: #444; }

        /* Empty state */
        .empty-prompt {
            text-align: center;
            padding: 50px 20px;
            color: #888;
        }

        .empty-prompt p { font-size: 14px; margin-bottom: 6px; }
        .empty-prompt small { font-size: 12px; }

        /* Badge inline */
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


        /* Status radio selector */
        .status-field { gap: 0; }

        .status-selector {
            display: flex;
            gap: 8px;
            padding: 10px 12px;
            flex-wrap: wrap;
            background: #fdfcfa;
        }

        .status-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border: 2px solid var(--sc, #2a2a2a);
            color: var(--sc, #2a2a2a);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            font-family: 'Source Sans 3', Arial, sans-serif;
        }

        .status-btn input[type="radio"] { display: none; }

        .status-btn:hover,
        .status-btn-active {
            background: var(--sc, #2a2a2a);
            color: #fff !important;
        }

        .status-preview {
            padding: 6px 12px 10px;
            font-size: 12px;
            color: #666;
            background: #fdfcfa;
            border-top: 1px solid #eee;
        }

        @media (max-width: 600px) {
            .summary-grid  { grid-template-columns: 1fr 1fr; }
            .form-row-2    { grid-template-columns: 1fr; }
            .form-row-2 .form-field { border-right: none; border-bottom: 1px solid #ddd; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
<div class="update-wrap">

    <h1 class="title nha">
        National Housing Authority
        <sub>Update Support Ticket</sub>
    </h1>

    <!-- Ticket selector dropdown -->
    <form method="GET" action="update_ticket.php">
        <div class="search-bar">
            <select name="id" onchange="this.form.submit()" style="flex:1;border:none;padding:10px 14px;font-size:13px;font-family:'Source Sans 3',Arial,sans-serif;background:#fdfcfa;outline:none;cursor:pointer;">
                <option value="">— Select a ticket to update —</option>
                <?php
                if ($all_tickets && mysqli_num_rows($all_tickets) > 0):
                    while ($t = mysqli_fetch_assoc($all_tickets)):
                        $sel = ($selected_id === (int)$t['id']) ? 'selected' : '';
                        $label = '#'.$t['id'].' | '.($t['service_request_no'] ?: 'No Req. No.').' — '.htmlspecialchars($t['client_name']).' ['.($t['status'] ?: 'Open').'] ';
                        echo "<option value='{$t['id']}' $sel>".htmlspecialchars($label)."</option>";
                    endwhile;
                else:
                    echo '<option disabled>No tickets found in database</option>';
                endif;
                ?>
            </select>
        </div>
    </form>

    <?php if ($success): ?>
        <div class="alert alert-success">✔ <?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">✘ <?= $error ?></div>
    <?php endif; ?>

    <?php if ($ticket): ?>

        <!-- Read-only ticket summary -->
        <div class="section-band">Ticket Information (Read-Only)</div>
        <div class="ticket-summary">
            <div class="summary-grid">
                <div class="summary-field">
                    <label>Ticket ID</label>
                    <span><?= $ticket['id'] ?></span>
                </div>
                <div class="summary-field">
                    <label>Request No.</label>
                    <span><?= htmlspecialchars($ticket['service_request_no']) ?></span>
                </div>
                <div class="summary-field">
                    <label>Date</label>
                    <span><?= htmlspecialchars($ticket['date_created']) ?></span>
                </div>
                <div class="summary-field">
                    <label>Client Name</label>
                    <span><?= htmlspecialchars($ticket['client_name']) ?></span>
                </div>
                <div class="summary-field">
                    <label>Department</label>
                    <span><?= htmlspecialchars($ticket['department']) ?></span>
                </div>
                <div class="summary-field">
                    <label>Priority</label>
                    <span>
                        <?php
                        $p = $ticket['priority_level'] ?? '';
                        $cls = match(strtolower($p)) {
                            'critical' => 'badge-critical',
                            'high'     => 'badge-high',
                            'medium'   => 'badge-medium',
                            'low'      => 'badge-low',
                            default    => ''
                        };
                        ?>
                        <span class="badge <?= $cls ?>"><?= htmlspecialchars($p) ?></span>
                    </span>
                </div>
                <div class="summary-field">
                    <label>Type</label>
                    <span><?= htmlspecialchars($ticket['type'] ?? '—') ?></span>
                </div>
                <div class="summary-field">
                    <label>Brand / Model</label>
                    <span><?= htmlspecialchars($ticket['brand_model'] ?? '—') ?></span>
                </div>
                <div class="summary-field">
                    <label>Serial No.</label>
                    <span><?= htmlspecialchars($ticket['serial_number'] ?? '—') ?></span>
                </div>
            </div>

            <!-- Details full width -->
            <div style="border-top:1px solid #ddd;">
                <div class="summary-field" style="border-right:none;">
                    <label>Problem Description</label>
                    <span style="white-space:pre-wrap;"><?= htmlspecialchars($ticket['details'] ?? '—') ?></span>
                </div>
            </div>
        </div>

        <!-- Update form -->
        <div class="section-band">Update Ticket</div>
        <form method="POST" action="update_ticket.php?id=<?= $selected_id ?>">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">

            <div class="update-form">

                <!-- Status field with live badge preview -->
                <div class="form-field status-field">
                    <label for="status">Status</label>
                    <div class="status-selector">
                        <?php
                        $statuses = ['Open', 'In-Progress', 'For Parts', 'For Replacement', 'Endorsed to Supplier', 'Unrepairable', 'Resolved', 'Closed'];
                        $current_status = $ticket['status'] ?? 'Open';
                        $status_colors = [
                            'Open'                 => '#8b1a1a',
                            'In-Progress'          => '#7a6200',
                            'For Parts'            => '#5a3e8a',
                            'For Replacement'      => '#1a5a8a',
                            'Endorsed to Supplier' => '#1a6a7a',
                            'Unrepairable'         => '#4a4a4a',
                            'Resolved'             => '#2a6a2a',
                            'Closed'               => '#2a2a2a',
                        ];
                        foreach ($statuses as $s):
                            $active = $current_status === $s ? 'status-btn-active' : '';
                            $color  = $status_colors[$s] ?? '#2a2a2a';
                        ?>
                        <label class="status-btn <?= $active ?>" style="--sc:<?= $color ?>">
                            <input type="radio" name="status" value="<?= $s ?>"
                                <?= $current_status === $s ? 'checked' : '' ?>
                                onchange="updateStatusPreview('<?= $s ?>', '<?= $color ?>')">
                            <?= $s ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <!-- Live preview badge -->
                    <div class="status-preview" id="statusPreview">
                        Current status:&nbsp;
                        <span id="statusBadge"
                              style="display:inline-block;padding:3px 12px;font-size:12px;font-weight:700;
                                     letter-spacing:0.07em;text-transform:uppercase;color:#fff;
                                     background:<?= $status_colors[$current_status] ?? '#2a2a2a' ?>">
                            <?= htmlspecialchars($current_status) ?>
                        </span>
                    </div>
                </div>

                <div class="form-field">
                    <label for="diagnosis">Diagnosis / Warranty Details</label>
                    <textarea name="diagnosis" id="diagnosis" rows="3"><?= htmlspecialchars($ticket['diagnosis'] ?? '') ?></textarea>
                </div>

                <div class="form-field">
                    <label for="actions_taken">Actions Taken / Resolution / Recommendations</label>
                    <textarea name="actions_taken" id="actions_taken" rows="4"><?= htmlspecialchars($ticket['actions_taken'] ?? '') ?></textarea>
                </div>

                <div class="form-row-2">
                    <div class="form-field">
                        <label for="tech_personnel">Technical Personnel</label>
                        <textarea name="tech_personnel" id="tech_personnel" rows="3"><?= htmlspecialchars($ticket['tech_personnel'] ?? '') ?></textarea>
                    </div>
                    <div class="form-field">
                        <label for="accepted_by">Solution / Remedy Accepted By</label>
                        <textarea name="accepted_by" id="accepted_by" rows="3"><?= htmlspecialchars($ticket['accepted_by'] ?? '') ?></textarea>
                    </div>
                </div>

            </div>

            <div class="update-submit">
                <a href="ticket_list.php">← Back to Ticket List</a>
                <input type="submit" value="Save Changes">
            </div>
        </form>

    <?php else: ?>
        <div class="empty-prompt">
            <p>Select a ticket from the dropdown above to update it.</p>
            <small>All submitted tickets will appear in the list.</small>
        </div>
    <?php endif; ?>

</div>
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

function updateStatusPreview(label, color) {
    var badge = document.getElementById("statusBadge");
    badge.textContent = label;
    badge.style.background = color;
    // Toggle active class on buttons
    document.querySelectorAll(".status-btn").forEach(function(btn) {
        var radio = btn.querySelector("input[type=radio]");
        if (radio && radio.value === label) {
            btn.classList.add("status-btn-active");
        } else {
            btn.classList.remove("status-btn-active");
        }
    });
}
</script>
</body>
</html>
