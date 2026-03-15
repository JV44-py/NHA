<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hr_it_support/login.php');
    exit;
}
include 'config.php';

$success = '';
$error   = '';
$ticket  = null;

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    $id        = (int) $_POST['ticket_id'];
    $status    = mysqli_real_escape_string($conn, $_POST['status']       ?? '');
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']    ?? '');
    $actions   = mysqli_real_escape_string($conn, $_POST['actions_taken']  ?? '');
    $tech      = mysqli_real_escape_string($conn, $_POST['tech_personnel'] ?? '');
    $accepted  = mysqli_real_escape_string($conn, $_POST['accepted_by']    ?? '');

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
        $error = 'Update failed: ' . mysqli_error($conn);
    }
}

// Load selected ticket
$selected_id = (int)($_POST['ticket_id'] ?? $_GET['id'] ?? 0);
if ($selected_id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM tickets WHERE id = $selected_id LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $ticket = mysqli_fetch_assoc($res);
    }
}

// All tickets for dropdown
$all_tickets = mysqli_query($conn, "SELECT id, service_request_no, client_name, status FROM tickets ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Ticket — NHA IT Support</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=Source+Sans+3:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Ticket selector bar */
        .ticket-select-bar {
            display: flex;
            border: 1px solid var(--line);
            margin-bottom: 20px;
            background: var(--white);
        }
        .ticket-select-bar select {
            flex: 1;
            border: none;
            padding: 10px 14px;
            font-size: 13px;
            font-family: 'Source Sans 3', sans-serif;
            background: transparent;
            outline: none;
            cursor: pointer;
            color: var(--ink);
        }
        .ticket-select-bar select:focus { background: #fdfcfa; }

        /* Summary grid (read-only) */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border: 1px solid var(--line);
            margin-bottom: 0;
        }
        .summary-field {
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--line);
            border-bottom: 1px solid var(--line-lt);
        }
        .summary-field:nth-child(3n) { border-right: none; }
        .summary-field.full { grid-column: 1 / -1; border-right: none; }
        .summary-field label {
            background: var(--bg-warm);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 4px 10px;
            border-bottom: 1px solid var(--line);
        }
        .summary-field span {
            padding: 6px 10px;
            font-size: 13px;
            color: var(--ink);
            white-space: pre-wrap;
        }

        /* Form fields in update form */
        .update-field {
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid var(--line);
        }
        .update-field label {
            background: var(--bg-warm);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 5px 12px;
            border-bottom: 1px solid var(--line);
        }
        .update-field textarea {
            border: none;
            padding: 9px 12px;
            font-size: 13px;
            font-family: 'Source Sans 3', sans-serif;
            background: #fdfcfa;
            outline: none;
            resize: vertical;
        }
        .update-field textarea:focus { background: #f7f5ef; }

        .update-two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .update-two-col .update-field {
            border-right: 1px solid var(--line);
        }
        .update-two-col .update-field:last-child { border-right: none; }

        /* Status selector */
        .status-preview {
            padding: 6px 12px 10px;
            font-size: 12px;
            color: var(--muted);
            background: #fdfcfa;
            border-top: 1px solid var(--line-lt);
        }

        .empty-prompt {
            text-align: center;
            padding: 52px 20px;
            color: var(--faint);
        }
        .empty-prompt p    { font-size: 14px; margin-bottom: 6px; }
        .empty-prompt small { font-size: 12px; }

        @media (max-width: 600px) {
            .summary-grid      { grid-template-columns: 1fr 1fr; }
            .update-two-col    { grid-template-columns: 1fr; }
            .update-two-col .update-field { border-right: none; }
        }
    </style>
</head>
<body>

<div class="shell">

    <?php include 'sidebar.php'; ?>

    <div class="page-content">

        <div class="page-header">
            <div class="eyebrow">National Housing Authority — IT Support</div>
            <h1>Update Ticket</h1>
            <div class="sub">Select a ticket from the list below to update its status and resolution notes.</div>
        </div>

        <!-- Ticket selector -->
        <form method="GET" action="update_ticket.php">
            <div class="ticket-select-bar">
                <select name="id" onchange="this.form.submit()">
                    <option value="">— Select a ticket to update —</option>
                    <?php
                    if ($all_tickets && mysqli_num_rows($all_tickets) > 0):
                        while ($t = mysqli_fetch_assoc($all_tickets)):
                            $sel   = ($selected_id === (int)$t['id']) ? 'selected' : '';
                            $st    = $t['status'] ?: 'Open';
                            $label = '#' . $t['id'] . '  |  ' . ($t['service_request_no'] ?: 'No Req. No.') . '  —  ' . $t['client_name'] . '  [' . $st . ']';
                            echo '<option value="' . $t['id'] . '" ' . $sel . '>' . htmlspecialchars($label) . '</option>';
                        endwhile;
                    else:
                        echo '<option disabled>No tickets in database</option>';
                    endif;
                    ?>
                </select>
            </div>
        </form>

        <?php if ($success): ?>
            <div class="alert alert-success">✔ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">✘ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($ticket): ?>

            <!-- Read-only summary -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <span class="card-title">Ticket Information</span>
                    <span style="font-size:10.5px; opacity:0.6; margin-left:auto;">Read-only</span>
                </div>
                <div class="summary-grid">
                    <div class="summary-field">
                        <label>Ticket ID</label>
                        <span>#<?= $ticket['id'] ?></span>
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
                            $p    = $ticket['priority_level'] ?? '';
                            $pcls = match(strtolower($p)) {
                                'critical' => 'badge-critical',
                                'high'     => 'badge-high',
                                'medium'   => 'badge-medium',
                                'low'      => 'badge-low',
                                default    => ''
                            };
                            ?>
                            <span class="badge <?= $pcls ?>"><?= htmlspecialchars($p) ?></span>
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
                    <div class="summary-field full">
                        <label>Problem Description</label>
                        <span><?= htmlspecialchars($ticket['details'] ?? '—') ?></span>
                    </div>
                </div>
            </div>

            <!-- Update form -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Update Ticket #<?= $ticket['id'] ?></span>
                </div>
                <form method="POST" action="update_ticket.php?id=<?= $selected_id ?>">
                    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">

                    <!-- Status selector -->
                    <div class="update-field" style="border-bottom:2px solid var(--navy);">
                        <label>Status</label>
                        <div class="status-selector">
                            <?php
                            $statuses = ['Open', 'In-Progress', 'For Parts', 'For Replacement', 'Endorsed to Supplier', 'Unrepairable', 'Resolved', 'Closed'];
                            $current_status = $ticket['status'] ?: 'Open';
                            $status_colors  = [
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
                        <div class="status-preview" id="statusPreview">
                            Current:&nbsp;
                            <span id="statusBadge" style="display:inline-block; padding:2px 12px; font-size:11.5px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:#fff; background:<?= $status_colors[$current_status] ?? '#2a2a2a' ?>;">
                                <?= htmlspecialchars($current_status) ?>
                            </span>
                        </div>
                    </div>

                    <div class="update-field">
                        <label for="diagnosis">Diagnosis / Warranty Details</label>
                        <textarea name="diagnosis" id="diagnosis" rows="3"><?= htmlspecialchars($ticket['diagnosis'] ?? '') ?></textarea>
                    </div>

                    <div class="update-field">
                        <label for="actions_taken">Actions Taken / Resolution / Recommendations</label>
                        <textarea name="actions_taken" id="actions_taken" rows="4"><?= htmlspecialchars($ticket['actions_taken'] ?? '') ?></textarea>
                    </div>

                    <div class="update-two-col">
                        <div class="update-field">
                            <label for="tech_personnel">Technical Personnel</label>
                            <textarea name="tech_personnel" id="tech_personnel" rows="3"><?= htmlspecialchars($ticket['tech_personnel'] ?? '') ?></textarea>
                        </div>
                        <div class="update-field">
                            <label for="accepted_by">Solution / Remedy Accepted By</label>
                            <textarea name="accepted_by" id="accepted_by" rows="3"><?= htmlspecialchars($ticket['accepted_by'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="submit-row">
                        <a href="ticketlist.php" class="btn btn-outline">← Back to Ticket List</a>
                        <button type="submit" class="btn btn-primary">Save Changes →</button>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="empty-prompt">
                <p>Select a ticket from the dropdown above to update it.</p>
                <small>All submitted tickets appear in the list.</small>
            </div>
        <?php endif; ?>

    </div><!-- /page-content -->
</div><!-- /shell -->

<script>
function updateStatusPreview(label, color) {
    var badge = document.getElementById('statusBadge');
    badge.textContent  = label;
    badge.style.background = color;
    document.querySelectorAll('.status-btn').forEach(function (btn) {
        var radio = btn.querySelector('input[type=radio]');
        if (radio && radio.value === label) {
            btn.classList.add('status-btn-active');
        } else {
            btn.classList.remove('status-btn-active');
        }
    });
}
</script>

</body>
</html>