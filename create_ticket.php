<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hr_it_support/login.php');
    exit;
}
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function clean($conn, $val) {
        return mysqli_real_escape_string($conn, trim($val ?? ''));
    }

    $service    = clean($conn, $_POST['Services']    ?? '');
    $date       = clean($conn, $_POST['Date']        ?? '');
    $dept       = clean($conn, $_POST['GDR']         ?? '');
    $client     = clean($conn, $_POST['ClientName']  ?? '');
    $position   = clean($conn, $_POST['PosDes']      ?? '');
    $contact    = clean($conn, $_POST['contactNum']  ?? '');
    $email      = clean($conn, $_POST['Email']       ?? '');
    $priority   = clean($conn, $_POST['Prior']       ?? '');

    $type       = clean($conn, $_POST['Type']        ?? '');
    $model      = clean($conn, $_POST['Model']       ?? '');
    $warranty   = clean($conn, $_POST['Warranty']    ?? '');
    $property   = clean($conn, $_POST['PropertyNum'] ?? '');
    $serial     = clean($conn, $_POST['Serial']      ?? '');
    $year_acq   = clean($conn, $_POST['YearAcq']     ?? '');
    $active_dir = clean($conn, $_POST['ActiveDir']   ?? '');

    $mem_type   = clean($conn, $_POST['Mem']         ?? '');
    $mem_speed  = clean($conn, $_POST['MemSpeed']    ?? '');
    $storage    = clean($conn, $_POST['StorageType'] ?? '');
    $others1    = clean($conn, $_POST['Others1']     ?? '');
    $others2    = clean($conn, $_POST['Others2']     ?? '');
    $others3    = clean($conn, $_POST['Others3']     ?? '');
    $details    = clean($conn, $_POST['Details']     ?? '');

    $hw_install  = isset($_POST['HW_Install'])  ? 1 : 0;
    $hw_repair   = isset($_POST['HW_Repair'])   ? 1 : 0;
    $hw_assembly = isset($_POST['HW_Assembly']) ? 1 : 0;
    $hw_pm       = isset($_POST['HW_PM'])       ? 1 : 0;
    $hw_others   = isset($_POST['HW_Others'])   ? 1 : 0;

    $sw_install  = isset($_POST['SW_Install'])  ? 1 : 0;
    $sw_repair   = isset($_POST['SW_Repair'])   ? 1 : 0;
    $sw_update   = isset($_POST['SW_Update'])   ? 1 : 0;
    $sw_format   = isset($_POST['SW_Format'])   ? 1 : 0;
    $sw_others   = isset($_POST['SW_Others'])   ? 1 : 0;

    $nm_vc       = isset($_POST['NM_VC'])       ? 1 : 0;
    $nm_tu       = isset($_POST['NM_TU'])       ? 1 : 0;
    $nm_vs       = isset($_POST['NM_VS'])       ? 1 : 0;
    $nm_ns       = isset($_POST['NM_NS'])       ? 1 : 0;
    $nm_others   = isset($_POST['NM_Others'])   ? 1 : 0;

    $support_type = clean($conn, $_POST['ST']         ?? '');
    $diagnose     = clean($conn, $_POST['Diagnose']   ?? '');
    $nstp         = clean($conn, $_POST['NSTP']       ?? '');
    $actions      = clean($conn, $_POST['ACTIONSTAK'] ?? '');
    $accepted_by  = clean($conn, $_POST['SRAB']       ?? '');

    $sql = "INSERT INTO tickets (
        service_request_no, date_created, department, client_name,
        position_designation, contact_number, email, priority_level,
        type, brand_model, warranty, property_number, serial_number,
        year_acquired, active_directory, memory_type, memory_speed,
        storage_type, others1, others2, others3, details,
        hw_install, hw_repair, hw_assembly, hw_pm, hw_others,
        sw_install, sw_repair, sw_update, sw_format, sw_others,
        nm_vc, nm_tu, nm_vs, nm_ns, nm_others,
        support_type, diagnosis, tech_personnel, actions_taken, accepted_by
    ) VALUES (
        '$service','$date','$dept','$client',
        '$position','$contact','$email','$priority',
        '$type','$model','$warranty','$property','$serial',
        '$year_acq','$active_dir','$mem_type','$mem_speed',
        '$storage','$others1','$others2','$others3','$details',
        $hw_install,$hw_repair,$hw_assembly,$hw_pm,$hw_others,
        $sw_install,$sw_repair,$sw_update,$sw_format,$sw_others,
        $nm_vc,$nm_tu,$nm_vs,$nm_ns,$nm_others,
        '$support_type','$diagnose','$nstp','$actions','$accepted_by'
    )";

    if (mysqli_query($conn, $sql)) {
        header('Location: /hr_it_support/ticketlist.php');
        exit;
    } else {
        $error = 'Database error: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Service Request — NHA IT Support</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=Source+Sans+3:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="shell">

    <?php include 'sidebar.php'; ?>

    <div class="page-content">

        <div class="page-header">
            <div class="eyebrow">National Housing Authority — IT Support</div>
            <h1>New Service Request</h1>
            <div class="sub">Fill in all relevant fields and submit to create a new support ticket.</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">

            <!-- ── CARD 1: CLIENT & HARDWARE ── -->
            <div class="card">
                <div class="card-header">
                    <span class="step-badge">1</span>
                    <span class="card-title">Client &amp; Request Information</span>
                </div>
                <div class="card-body">
                    <div class="two-col">

                        <!-- Left: Client Details -->
                        <div class="col-left">
                            <div class="col-subheader">Client Details</div>
                            <div class="field-group">
                                <div class="field">
                                    <label for="Services">Service Request No.</label>
                                    <input type="text" id="Services" name="Services">
                                </div>
                                <div class="field">
                                    <label for="Date">Date</label>
                                    <input type="date" id="Date" name="Date">
                                </div>
                                <div class="field span2">
                                    <label for="GDR">Group / Dept. / Region</label>
                                    <input type="text" id="GDR" name="GDR">
                                </div>
                                <div class="field span2">
                                    <label for="ClientName">Client Name</label>
                                    <input type="text" id="ClientName" name="ClientName">
                                </div>
                                <div class="field span2">
                                    <label for="PosDes">Position / Designation</label>
                                    <input type="text" id="PosDes" name="PosDes">
                                </div>
                                <div class="field">
                                    <label for="contactNum">Contact Number</label>
                                    <input type="text" id="contactNum" name="contactNum">
                                </div>
                                <div class="field">
                                    <label for="Email">Email Address</label>
                                    <input type="email" id="Email" name="Email">
                                </div>
                                <div class="field span2">
                                    <label for="Prior">Priority Level</label>
                                    <select id="Prior" name="Prior">
                                        <option value="Low">Low</option>
                                        <option value="Medium">Medium</option>
                                        <option value="High">High</option>
                                        <option value="Critical">Critical</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Hardware Information -->
                        <div class="col-right">
                            <div class="col-subheader">Hardware / Item Information</div>
                            <div class="field-group">
                                <div class="field">
                                    <label for="Type">Type</label>
                                    <input type="text" id="Type" name="Type">
                                </div>
                                <div class="field">
                                    <label for="Model">Brand / Model</label>
                                    <input type="text" id="Model" name="Model">
                                </div>
                                <div class="field">
                                    <label>Warranty</label>
                                    <div class="radio-inline">
                                        <label><input type="radio" name="Warranty" value="Yes"> Yes</label>
                                        <label><input type="radio" name="Warranty" value="No" checked> No</label>
                                    </div>
                                </div>
                                <div class="field">
                                    <label>Active Directory</label>
                                    <div class="radio-inline">
                                        <label><input type="radio" name="ActiveDir" value="Yes"> Yes</label>
                                        <label><input type="radio" name="ActiveDir" value="No" checked> No</label>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="PropertyNum">Property No.</label>
                                    <input type="text" id="PropertyNum" name="PropertyNum">
                                </div>
                                <div class="field">
                                    <label for="Serial">Serial No.</label>
                                    <input type="text" id="Serial" name="Serial">
                                </div>
                                <div class="field">
                                    <label for="YearAcq">Year Acquired</label>
                                    <input type="text" id="YearAcq" name="YearAcq" placeholder="e.g. 2021">
                                </div>
                                <div class="field">
                                    <label for="Mem">Memory Type</label>
                                    <select name="Mem" id="Mem">
                                        <option value="DDR3">DDR3</option>
                                        <option value="DDR4">DDR4</option>
                                        <option value="DDR5">DDR5</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="MemSpeed">Memory Speed</label>
                                    <input type="text" id="MemSpeed" name="MemSpeed" placeholder="e.g. 3200 MHz">
                                </div>
                                <div class="field">
                                    <label for="StorageType">Storage Type</label>
                                    <select name="StorageType" id="StorageType">
                                        <option value="HDD">HDD</option>
                                        <option value="SSD">SSD</option>
                                        <option value="M2">M.2</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="Others1">Others 1</label>
                                    <input type="text" id="Others1" name="Others1">
                                </div>
                                <div class="field">
                                    <label for="Others2">Others 2</label>
                                    <input type="text" id="Others2" name="Others2">
                                </div>
                                <div class="field span2">
                                    <label for="Others3">Others 3</label>
                                    <input type="text" id="Others3" name="Others3">
                                </div>
                            </div>
                        </div>

                    </div><!-- /two-col -->

                    <!-- Details textarea -->
                    <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--line);">
                        <div class="field">
                            <label for="Details">Details / Scenario — Detailed Description of Problem</label>
                            <textarea id="Details" name="Details" rows="4" placeholder="Describe the issue in as much detail as possible…"></textarea>
                        </div>
                    </div>
                </div>
            </div><!-- /card 1 -->

            <!-- ── CARD 2: SUPPORT TYPE ── -->
            <div class="card">
                <div class="card-header">
                    <span class="step-badge">2</span>
                    <span class="card-title">Support Type &amp; Service Category</span>
                </div>
                <div class="card-body">

                    <div style="display:flex; align-items:center; gap:12px; padding-bottom:14px; border-bottom:1px solid var(--line); margin-bottom:14px;">
                        <label class="field-label" for="ST" style="white-space:nowrap;">Support Type:</label>
                        <input type="text" id="ST" name="ST" class="ctrl" placeholder="e.g. On-site, Remote, Walk-in" style="flex:1;">
                    </div>

                    <div class="check-grid">
                        <div class="check-col">
                            <div class="check-col-header">🔧 Hardware</div>
                            <div class="check-item"><label for="HW_Install">Installation</label><input type="checkbox" id="HW_Install" name="HW_Install"></div>
                            <div class="check-item"><label for="HW_Repair">Repair</label><input type="checkbox" id="HW_Repair" name="HW_Repair"></div>
                            <div class="check-item"><label for="HW_Assembly">Assembly</label><input type="checkbox" id="HW_Assembly" name="HW_Assembly"></div>
                            <div class="check-item"><label for="HW_PM">Preventive Maintenance</label><input type="checkbox" id="HW_PM" name="HW_PM"></div>
                            <div class="check-item"><label for="HW_Others">Others</label><input type="checkbox" id="HW_Others" name="HW_Others"></div>
                        </div>
                        <div class="check-col">
                            <div class="check-col-header">💾 Software</div>
                            <div class="check-item"><label for="SW_Install">Installation</label><input type="checkbox" id="SW_Install" name="SW_Install"></div>
                            <div class="check-item"><label for="SW_Repair">Repair</label><input type="checkbox" id="SW_Repair" name="SW_Repair"></div>
                            <div class="check-item"><label for="SW_Update">Updating</label><input type="checkbox" id="SW_Update" name="SW_Update"></div>
                            <div class="check-item"><label for="SW_Format">Formatting</label><input type="checkbox" id="SW_Format" name="SW_Format"></div>
                            <div class="check-item"><label for="SW_Others">Others</label><input type="checkbox" id="SW_Others" name="SW_Others"></div>
                        </div>
                        <div class="check-col">
                            <div class="check-col-header">🌐 Network &amp; Maintenance</div>
                            <div class="check-item"><label for="NM_VC">Video Conferencing</label><input type="checkbox" id="NM_VC" name="NM_VC"></div>
                            <div class="check-item"><label for="NM_TU">Tune-up / OS Updating</label><input type="checkbox" id="NM_TU" name="NM_TU"></div>
                            <div class="check-item"><label for="NM_VS">Virus Scanning</label><input type="checkbox" id="NM_VS" name="NM_VS"></div>
                            <div class="check-item"><label for="NM_NS">Network / Sharing</label><input type="checkbox" id="NM_NS" name="NM_NS"></div>
                            <div class="check-item"><label for="NM_Others">Others</label><input type="checkbox" id="NM_Others" name="NM_Others"></div>
                        </div>
                    </div>

                </div>
            </div><!-- /card 2 -->

            <!-- ── CARD 3: DIAGNOSIS & RESOLUTION ── -->
            <div class="card">
                <div class="card-header">
                    <span class="step-badge">3</span>
                    <span class="card-title">Diagnosis &amp; Resolution</span>
                </div>
                <div class="card-body">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                        <div class="field">
                            <label for="Diagnose">Diagnosis / Warranty Details</label>
                            <textarea id="Diagnose" name="Diagnose" rows="4" placeholder="Findings and diagnosis…"></textarea>
                        </div>
                        <div class="field">
                            <label for="NSTP">Name &amp; Signature of Technical Personnel</label>
                            <textarea id="NSTP" name="NSTP" rows="4" placeholder="Full name and designation…"></textarea>
                        </div>
                    </div>
                    <div class="field" style="margin-bottom:12px;">
                        <label for="ACTIONSTAK">Actions Taken / Resolution / Recommendations</label>
                        <textarea id="ACTIONSTAK" name="ACTIONSTAK" rows="4" placeholder="Describe the steps taken to resolve the issue…"></textarea>
                    </div>
                    <div class="field">
                        <label for="SRAB">Solution / Remedy Accepted By</label>
                        <textarea id="SRAB" name="SRAB" rows="3" placeholder="Name and signature of the client who accepted the solution…"></textarea>
                    </div>
                </div>
            </div><!-- /card 3 -->

            <!-- Submit -->
            <div class="submit-row">
                <a href="ticketlist.php" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit Ticket →</button>
            </div>

        </form>
    </div><!-- /page-content -->
</div><!-- /shell -->

</body>
</html>