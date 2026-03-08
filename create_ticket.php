<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function clean($conn, $val) {
        return mysqli_real_escape_string($conn, trim($val ?? ''));
    }

    $service    = clean($conn, $_POST['Services'] ?? '');
    $date       = clean($conn, $_POST['Date'] ?? '');
    $dept       = clean($conn, $_POST['GDR'] ?? '');
    $client     = clean($conn, $_POST['ClientName'] ?? '');
    $position   = clean($conn, $_POST['PosDes'] ?? '');
    $contact    = clean($conn, $_POST['contactNum'] ?? '');
    $email      = clean($conn, $_POST['Email'] ?? '');
    $priority   = clean($conn, $_POST['Prior'] ?? '');

    $type       = clean($conn, $_POST['Type'] ?? '');
    $model      = clean($conn, $_POST['Model'] ?? '');
    $warranty   = clean($conn, $_POST['Warranty'] ?? '');
    $property   = clean($conn, $_POST['PropertyNum'] ?? '');
    $serial     = clean($conn, $_POST['Serial'] ?? '');
    $year_acq   = clean($conn, $_POST['YearAcq'] ?? '');
    $active_dir = clean($conn, $_POST['ActiveDir'] ?? '');

    $mem_type   = clean($conn, $_POST['Mem'] ?? '');
    $mem_speed  = clean($conn, $_POST['MemSpeed'] ?? '');
    $storage    = clean($conn, $_POST['StorageType'] ?? '');
    $others1    = clean($conn, $_POST['Others1'] ?? '');
    $others2    = clean($conn, $_POST['Others2'] ?? '');
    $others3    = clean($conn, $_POST['Others3'] ?? '');
    $details    = clean($conn, $_POST['Details'] ?? '');

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

    $support_type = clean($conn, $_POST['ST'] ?? '');
    $diagnose     = clean($conn, $_POST['Diagnose'] ?? '');
    $nstp         = clean($conn, $_POST['NSTP'] ?? '');
    $actions      = clean($conn, $_POST['ACTIONSTAK'] ?? '');
    $accepted_by  = clean($conn, $_POST['SRAB'] ?? '');

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
        header("Location: ticket_list.php");
        exit;
    } else {
        $error = "Database error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request - National Housing Authority</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="page-wrapper">

    <h1 class="title nha">
        National Housing Authority
        <sub>IT Support Service Request Form</sub>
    </h1>

    <?php if (!empty($error)): ?>
        <p class="error-msg"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="create_ticket.php" method="POST">

        <!-- SECTION 1: CLIENT / REQUEST INFO -->
        <div class="section-band">Client &amp; Request Information</div>

        <div class="ticketing">
            <div class="ticketing-grid">

                <!-- LEFT: Client info -->
                <div class="ticketing-left">
                    <div class="field-row row1">
                        <label for="Services">Service Request No.</label>
                        <div class="field-input"><input type="text" id="Services" name="Services"></div>
                    </div>
                    <div class="field-row row2">
                        <label for="Date">Date</label>
                        <div class="field-input"><input type="date" id="Date" name="Date"></div>
                    </div>
                    <div class="field-row row3">
                        <label for="GDR">Group / Dept. / Region</label>
                        <div class="field-input"><input type="text" id="GDR" name="GDR"></div>
                    </div>
                    <div class="field-row row4">
                        <label for="ClientName">Client Name</label>
                        <div class="field-input"><input type="text" id="ClientName" name="ClientName"></div>
                    </div>
                    <div class="field-row row5">
                        <label for="PosDes">Position / Designation</label>
                        <div class="field-input"><input type="text" id="PosDes" name="PosDes"></div>
                    </div>
                    <div class="field-row row6">
                        <label for="contactNum">Contact Number</label>
                        <div class="field-input"><input type="text" id="contactNum" name="contactNum"></div>
                    </div>
                    <div class="field-row row7">
                        <label for="Email">Email Address</label>
                        <div class="field-input"><input type="email" id="Email" name="Email"></div>
                    </div>
                    <div class="field-row row8">
                        <label for="Prior">Priority Level</label>
                        <div class="field-input">
                            <select id="Prior" name="Prior">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Hardware info -->
                <div class="ticketing-right">
                    <div class="section-band">Hardware / Item Information</div>
                    <div class="hw-subgrid">
                        <div class="field-row">
                            <label for="Type">Type</label>
                            <div class="field-input"><input type="text" id="Type" name="Type"></div>
                        </div>
                        <div class="field-row">
                            <label for="Model">Brand / Model</label>
                            <div class="field-input"><input type="text" id="Model" name="Model"></div>
                        </div>
                        <div class="field-row">
                            <label>Warranty</label>
                            <div class="field-input">
                                <div class="radio-group">
                                    <label><input type="radio" name="Warranty" value="Yes"> Yes</label>
                                    <label><input type="radio" name="Warranty" value="No" checked> No</label>
                                </div>
                            </div>
                        </div>
                        <div class="field-row">
                            <label for="PropertyNum">Property No.</label>
                            <div class="field-input"><input type="text" id="PropertyNum" name="PropertyNum"></div>
                        </div>
                        <div class="field-row">
                            <label for="Serial">Serial No.</label>
                            <div class="field-input"><input type="text" id="Serial" name="Serial"></div>
                        </div>
                        <div class="field-row">
                            <label for="YearAcq">Year Acquired</label>
                            <div class="field-input"><input type="text" id="YearAcq" name="YearAcq"></div>
                        </div>
                        <div class="field-row">
                            <label>Active Directory</label>
                            <div class="field-input">
                                <div class="radio-group">
                                    <label><input type="radio" name="ActiveDir" value="Yes"> Yes</label>
                                    <label><input type="radio" name="ActiveDir" value="No" checked> No</label>
                                </div>
                            </div>
                        </div>
                        <div class="field-row">
                            <label for="Mem">Memory Type</label>
                            <div class="field-input">
                                <select name="Mem" id="Mem">
                                    <option value="DDR3">DDR3</option>
                                    <option value="DDR4">DDR4</option>
                                    <option value="DDR5">DDR5</option>
                                </select>
                            </div>
                        </div>
                        <div class="field-row">
                            <label for="MemSpeed">Memory Speed</label>
                            <div class="field-input"><input type="text" id="MemSpeed" name="MemSpeed"></div>
                        </div>
                        <div class="field-row">
                            <label for="StorageType">Storage Type</label>
                            <div class="field-input">
                                <select name="StorageType" id="StorageType">
                                    <option value="HDD">HDD</option>
                                    <option value="SSD">SSD</option>
                                    <option value="M2">M.2</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                        </div>
                        <div class="field-row">
                            <label for="Others1">Others 1</label>
                            <div class="field-input"><input type="text" id="Others1" name="Others1"></div>
                        </div>
                        <div class="field-row">
                            <label for="Others2">Others 2</label>
                            <div class="field-input"><input type="text" id="Others2" name="Others2"></div>
                        </div>
                        <div class="field-row" style="grid-column: 1 / -1;">
                            <label for="Others3">Others 3</label>
                            <div class="field-input"><input type="text" id="Others3" name="Others3"></div>
                        </div>
                    </div>
                </div>

            </div><!-- /ticketing-grid -->

            <div class="details-row">
                <label for="Details">Details / Scenario — Detailed Description of Problem</label>
                <textarea id="Details" name="Details" rows="4"></textarea>
            </div>
        </div><!-- /ticketing -->

        <!-- SECTION 2: SUPPORT TYPE -->
        <div class="ticketing2">

            <div class="support-type-row">
                <label for="ST">Support Type:</label>
                <input type="text" id="ST" name="ST">
            </div>

            <div class="support-grid">
                <div class="support-col">
                    <div class="col-header">Hardware</div>
                    <div class="check-row"><label for="HW_Install">Installation</label><input type="checkbox" id="HW_Install" name="HW_Install"></div>
                    <div class="check-row"><label for="HW_Repair">Repair</label><input type="checkbox" id="HW_Repair" name="HW_Repair"></div>
                    <div class="check-row"><label for="HW_Assembly">Assembly</label><input type="checkbox" id="HW_Assembly" name="HW_Assembly"></div>
                    <div class="check-row"><label for="HW_PM">Preventive Maintenance</label><input type="checkbox" id="HW_PM" name="HW_PM"></div>
                    <div class="check-row"><label for="HW_Others">Others</label><input type="checkbox" id="HW_Others" name="HW_Others"></div>
                </div>
                <div class="support-col">
                    <div class="col-header">Software</div>
                    <div class="check-row"><label for="SW_Install">Installation</label><input type="checkbox" id="SW_Install" name="SW_Install"></div>
                    <div class="check-row"><label for="SW_Repair">Repair</label><input type="checkbox" id="SW_Repair" name="SW_Repair"></div>
                    <div class="check-row"><label for="SW_Update">Updating</label><input type="checkbox" id="SW_Update" name="SW_Update"></div>
                    <div class="check-row"><label for="SW_Format">Formatting</label><input type="checkbox" id="SW_Format" name="SW_Format"></div>
                    <div class="check-row"><label for="SW_Others">Others</label><input type="checkbox" id="SW_Others" name="SW_Others"></div>
                </div>
                <div class="support-col">
                    <div class="col-header">Network &amp; Maintenance</div>
                    <div class="check-row"><label for="NM_VC">Video Conferencing</label><input type="checkbox" id="NM_VC" name="NM_VC"></div>
                    <div class="check-row"><label for="NM_TU">Tune-up / OS Updating</label><input type="checkbox" id="NM_TU" name="NM_TU"></div>
                    <div class="check-row"><label for="NM_VS">Virus Scanning</label><input type="checkbox" id="NM_VS" name="NM_VS"></div>
                    <div class="check-row"><label for="NM_NS">Network / Sharing</label><input type="checkbox" id="NM_NS" name="NM_NS"></div>
                    <div class="check-row"><label for="NM_Others">Others</label><input type="checkbox" id="NM_Others" name="NM_Others"></div>
                </div>
            </div>

            <div class="text-block-row">
                <div class="text-block">
                    <label for="Diagnose">Diagnosis / Warranty Details</label>
                    <textarea id="Diagnose" name="Diagnose" rows="4"></textarea>
                </div>
                <div class="text-block">
                    <label for="NSTP">Name &amp; Signature of Technical Personnel</label>
                    <textarea id="NSTP" name="NSTP" rows="4"></textarea>
                </div>
            </div>

            <div class="text-block">
                <label for="ACTIONSTAK">Actions Taken / Resolution / Recommendations</label>
                <textarea id="ACTIONSTAK" name="ACTIONSTAK" rows="4"></textarea>
            </div>

            <div class="text-block">
                <label for="SRAB">Solution / Remedy Accepted By</label>
                <textarea id="SRAB" name="SRAB" rows="3"></textarea>
            </div>

        </div><!-- /ticketing2 -->

        <div class="submit-row">
            <input type="submit" value="Submit Ticket">
        </div>

    </form>
</div>
</body>
</html>
