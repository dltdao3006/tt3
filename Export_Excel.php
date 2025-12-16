<?php
// Connect to database
require 'connectDB.php';
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_POST['To_Excel'])) {
    header("Location: UsersLog.php");
    exit();
}

// Helper: safe POST string
function ppost($k, $conn) {
    return isset($_POST[$k]) ? mysqli_real_escape_string($conn, trim((string)$_POST[$k])) : '';
}

// collect filters
$startDate = ppost('date_sel_start', $conn);
$endDate   = ppost('date_sel_end', $conn);
$timeSel   = ppost('time_sel', $conn); // "Time_in" or "Time_out"
$timeStart = ppost('time_sel_start', $conn);
$timeEnd   = ppost('time_sel_end', $conn);
$cardSel   = ppost('card_sel', $conn);
$devSel    = ppost('dev_sel', $conn);

// defaults
if ($startDate === '' || $startDate === '0') $startDate = date('Y-m-d');

// build common where for date/device/card etc.
// IMPORTANT: do NOT include timein/timeout filters in $where_common (we apply them in subqueries)
$whereParts = [];
if ($endDate !== '' && $endDate !== '0') {
    $whereParts[] = "checkindate BETWEEN '{$startDate}' AND '{$endDate}'";
} else {
    $whereParts[] = "checkindate = '{$startDate}'";
}
if ($cardSel !== '' && $cardSel !== '0') $whereParts[] = "card_uid = '{$cardSel}'";
if ($devSel !== '' && $devSel !== '0') $whereParts[] = "device_uid = '{$devSel}'";
// add other static filters if needed
$where_common = implode(' AND ', $whereParts);
if (trim($where_common) === '') $where_common = '1=1';

// build optional time filters for subqueries
$timein_filter_sql = '';
$timeout_filter_sql = '';
if ($timeSel === 'Time_in') {
    if ($timeStart !== '' && $timeStart !== '0' && ($timeEnd === '' || $timeEnd === '0')) {
        $timein_filter_sql = " AND timein = '$timeStart' ";
    } elseif ($timeStart !== '' && $timeStart !== '0' && $timeEnd !== '' && $timeEnd !== '0') {
        $timein_filter_sql = " AND timein BETWEEN '$timeStart' AND '$timeEnd' ";
    }
} elseif ($timeSel === 'Time_out') {
    if ($timeStart !== '' && $timeStart !== '0' && ($timeEnd === '' || $timeEnd === '0')) {
        $timeout_filter_sql = " AND timeout = '$timeStart' ";
    } elseif ($timeStart !== '' && $timeStart !== '0' && $timeEnd !== '' && $timeEnd !== '0') {
        $timeout_filter_sql = " AND timeout BETWEEN '$timeStart' AND '$timeEnd' ";
    }
}

// shift end map (device_uid => shift_end time). Lowercase keys.
$shift_end_map = [
    '275006c8c1b3206c' => '17:30:00', // nhà máy
    // add known devices here
    'default' => '17:00:00'
];
// optional load from DB table device_shifts(device_uid, shift_end)
$tbl = mysqli_query($conn, "SHOW TABLES LIKE 'device_shifts'");
if ($tbl && mysqli_num_rows($tbl) > 0) {
    $q = mysqli_query($conn, "SELECT device_uid, shift_end FROM device_shifts");
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $k = strtolower(trim($r['device_uid']));
            $s = trim($r['shift_end']);
            if ($s !== '') {
                if (preg_match('/^\d{2}:\d{2}$/', $s)) $s .= ':00';
                if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $s)) $shift_end_map[$k] = $s;
            }
        }
    }
}

// NEW SQL: p = all distinct person/day rows according to where_common (includes OFF and even 00:00 values)
// scans_aggr = aggregated valid scans (MIN/MAX) from union of timein/timeout (exclude 00:00)
// left join scans_aggr onto p to keep everyone
$sql = "
SELECT
  p.serialnumber,
  p.username,
  p.checkindate,
  p.device_uid,
  p.device_dep,
  sa.first_in,
  sa.last_out,
  s1.id AS first_in_id,
  s1.card_uid AS first_in_card,
  s2.id AS last_out_id,
  s2.card_uid AS last_out_card
FROM
  (
    -- p: all distinct person/day rows from users_logs (this keeps OFF and no-scan rows)
    SELECT DISTINCT serialnumber, username, checkindate, device_uid, device_dep
    FROM users_logs
    WHERE ({$where_common})
  ) AS p
LEFT JOIN
  (
    -- sa: aggregated scans (valid only)
    SELECT serialnumber, checkindate, MIN(scan_time) AS first_in, MAX(scan_time) AS last_out
    FROM (
      SELECT serialnumber, checkindate, timein  AS scan_time
      FROM users_logs
      WHERE timein IS NOT NULL
        AND TRIM(timein) <> ''
        AND TRIM(timein) <> '00:00'
        AND TRIM(timein) <> '00:00:00'
        {$timein_filter_sql}
        AND ({$where_common})
      UNION ALL
      SELECT serialnumber, checkindate, timeout AS scan_time
      FROM users_logs
      WHERE timeout IS NOT NULL
        AND TRIM(timeout) <> ''
        AND TRIM(timeout) <> '00:00'
        AND TRIM(timeout) <> '00:00:00'
        {$timeout_filter_sql}
        AND ({$where_common})
    ) AS scans_g
    GROUP BY serialnumber, checkindate
  ) AS sa
  ON sa.serialnumber = p.serialnumber
 AND sa.checkindate  = p.checkindate

LEFT JOIN
  (
    -- s1: to fetch id & card for first_in
    SELECT id, serialnumber, checkindate, timein AS scan_time, card_uid
    FROM users_logs
    WHERE timein IS NOT NULL
      AND TRIM(timein) <> ''
      AND TRIM(timein) <> '00:00'
      AND TRIM(timein) <> '00:00:00'
      {$timein_filter_sql}
      AND ({$where_common})
    UNION ALL
    SELECT id, serialnumber, checkindate, timeout AS scan_time, card_uid
    FROM users_logs
    WHERE timeout IS NOT NULL
      AND TRIM(timeout) <> ''
      AND TRIM(timeout) <> '00:00'
      AND TRIM(timeout) <> '00:00:00'
      {$timeout_filter_sql}
      AND ({$where_common})
  ) AS s1
  ON s1.serialnumber = p.serialnumber
 AND s1.checkindate  = p.checkindate
 AND s1.scan_time    = sa.first_in

LEFT JOIN
  (
    -- s2: to fetch id & card for last_out
    SELECT id, serialnumber, checkindate, timein AS scan_time, card_uid
    FROM users_logs
    WHERE timein IS NOT NULL
      AND TRIM(timein) <> ''
      AND TRIM(timein) <> '00:00'
      AND TRIM(timein) <> '00:00:00'
      {$timein_filter_sql}
      AND ({$where_common})
    UNION ALL
    SELECT id, serialnumber, checkindate, timeout AS scan_time, card_uid
    FROM users_logs
    WHERE timeout IS NOT NULL
      AND TRIM(timeout) <> ''
      AND TRIM(timeout) <> '00:00'
      AND TRIM(timeout) <> '00:00:00'
      {$timeout_filter_sql}
      AND ({$where_common})
  ) AS s2
  ON s2.serialnumber = p.serialnumber
 AND s2.checkindate  = p.checkindate
 AND s2.scan_time    = sa.last_out

ORDER BY p.checkindate DESC, p.serialnumber ASC
";

error_log("Export SQL (all persons + scans): " . $sql);

$res = mysqli_query($conn, $sql);
if (!$res) {
    error_log("SQL error: " . mysqli_error($conn));
    header("Location: UsersLog.php");
    exit();
}

// build output HTML table (Excel)
$output = '';

// Build report title
$report_title = '';
if ($endDate === '' || $endDate === '0') {
    // Only 1 day (StartDate)
    $report_title = "CHI TIẾT CHẤM CÔNG NGÀY " . date('d/m/Y', strtotime($startDate));
} else {
    // Date range
    $report_title = "CHI TIẾT CHẤM CÔNG TỪ " . date('d/m/Y', strtotime($startDate)) .
                    " ĐẾN " . date('d/m/Y', strtotime($endDate));
}

// Optional: device filter label
$device_label = '';
if ($devSel !== '' && $devSel !== '0') {
    $device_label = "<br><i>Thiết bị: {$devSel}</i>";
}

// Optional: card filter label
$card_label = '';
if ($cardSel !== '' && $cardSel !== '0') {
    $card_label = "<br><i>Thẻ: {$cardSel}</i>";
}

// Render title row
$output = '';

// Build title text
if ($endDate === '' || $endDate === '0') {
    $title_date = "Ngày " . date('Y-m-d', strtotime($startDate));
} else {
    $title_date = "Từ ngày " . $startDate . " đến ngày " . $endDate;
}

// --- Render new styled title ---
// ---------- BEGIN: nicer report header + table ----------
$output = '';

// Build title text
if ($endDate === '' || $endDate === '0') {
    $title_date = "Ngày " . date('Y-m-d', strtotime($startDate));
} else {
    $title_date = "Từ ngày " . $startDate . " đến ngày " . $endDate;
}

// Global inline styles for Excel HTML
$global_style = '
    font-family: Arial, Helvetica, sans-serif;
    color: #000;
';

// Title block (centered, big)
$output .= '
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; '.$global_style.'">
  <tr>
    <td colspan="13" style="text-align:center; padding-top:12px; padding-bottom:6px;">
      <div style="font-size:28px; font-weight:700;">CHI TIẾT CHẤM CÔNG</div>
    </td>
  </tr>
  <tr>
    <td colspan="13" style="text-align:center; padding-bottom:6px;">
      <div style="font-size:14px; font-weight:600;">'.$title_date.'</div>
    </td>
  </tr>
  <tr>
    <td colspan="13" style="border-top:6px solid #0F7A4C;"></td>
  </tr>
  <tr><td colspan="13" style="height:10px;"></td></tr>
</table>
';

// Table header + column layout
// Adjust widths here (percent or fixed px). Tweak to your taste.
// Use table-layout:fixed so widths respected; use word-wrap for long names.
$output .= '
<table border="1" cellpadding="4" cellspacing="0" 
       style="width:100%; border-collapse:collapse; table-layout:fixed; font-family:Arial; font-size:12px;">

  <colgroup>
    <col style="width:10%"/>   <!-- Mã NV -->
    <col style="width:30%"/>   <!-- Tên -->
    <col style="width:10%"/>   <!-- Ngày -->
    <col style="width:7%"/>    <!-- Thứ -->
    <col style="width:12%"/>   <!-- First In -->
    <col style="width:12%"/>   <!-- Last Out -->
    <col style="width:10%"/>   <!-- Trễ -->
    <col style="width:10%"/>   <!-- Tổng phút -->
    <col style="width:10%"/>   <!-- OT -->
    <col style="width:12%"/>   <!-- Nơi -->
    <col style="width:15%"/>   <!-- Ghi chú -->
  </colgroup>

  <thead>
    <tr style="background:#f0f0f0; font-weight:bold; text-align:center;">
      <th>Mã NV</th>
      <th style="text-align:left;">Tên</th>
      <th>Ngày</th>
      <th>Thứ</th>
      <th>First In</th>
      <th>Last Out</th>
      <th>Trễ</th>
      <th>Tổng thời gian(phút)</th>
      <th>Overtime (phút)</th>
      <th>Nơi làm việc</th>
      <th>Ghi chú</th>
    </tr>
  </thead>

  <tbody>
';

// Then loop rows as before, but use classes/inline style for alignment
$time_pattern = '/^\d{2}:\d{2}(:\d{2})?$/';
while ($row = mysqli_fetch_assoc($res)) {
    // prepare data (same as before)
    $serial = htmlspecialchars($row['serialnumber']);
    $user = htmlspecialchars($row['username']);
    $checkindate = $row['checkindate'];
    $dt = new DateTime($checkindate);
    $week = array("Chủ nhật", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7");
    $day_of_week = $week[(int)$dt->format('w')];

    $first_in = isset($row['first_in']) ? trim($row['first_in']) : '';
    $last_out  = isset($row['last_out'])  ? trim($row['last_out'])  : '';

    if (preg_match('/^\d{2}:\d{2}$/', $first_in)) $first_in .= ':00';
    if (preg_match('/^\d{2}:\d{2}$/', $last_out)) $last_out .= ':00';

    $row_device_uid = isset($row['device_uid']) ? strtolower(trim($row['device_uid'])) : '';
    $shift_end = isset($shift_end_map[$row_device_uid]) ? $shift_end_map[$row_device_uid] : $shift_end_map['default'];
    if (preg_match('/^\d{2}:\d{2}$/', $shift_end)) $shift_end .= ':00';
    $shift_end_ts = strtotime($shift_end);

    $late_str = '';
    $total_minutes = 0;
    $ot_minutes = 0;

    $first_card = htmlspecialchars($row['first_in_card'] ?? '');
    $last_card  = htmlspecialchars($row['last_out_card'] ?? '');
    $place = htmlspecialchars($row['device_dep'] ?? '');

    // OFF logic and business rules (same as before)
    $note = '';
    if (strtolower(trim($place)) === 'off') {
        $note = 'OFF';
    } else {
        $first_ts = null; $last_ts = null;
        if (preg_match($time_pattern, $first_in)) $first_ts = strtotime($first_in);
        if (preg_match($time_pattern, $last_out)) $last_ts = strtotime($last_out);

        if ($first_ts !== null && $shift_end_ts !== null && $first_ts >= $shift_end_ts) {
            $note .= ($note ? ',' : '') . 'NO_CHECKIN';
            $first_in = ''; $first_card = ''; $first_ts = null;
        }
        if ($first_ts !== null && $last_ts !== null && $first_ts === $last_ts) {
            $note .= ($note ? ',' : '') . 'NO_CHECKOUT';
            $last_out = ''; $last_card = ''; $last_ts = null;
        }

        if ($first_ts !== null) {
            $shift_start_ts = strtotime('08:00:00');
            $late_seconds = $first_ts - $shift_start_ts;
            if ($late_seconds > 0) $late_str = gmdate("H:i:s", $late_seconds);
            if ($last_ts !== null && $last_ts > $first_ts) {
                $total_minutes = (int) round(($last_ts - $first_ts) / 60);
                if ($last_ts > $shift_end_ts) $ot_minutes = (int) round(($last_ts - $shift_end_ts) / 60);
            } else {
                if ($last_ts === null) { if (strpos($note, 'NO_CHECKOUT') === false) $note .= ($note ? ',' : '') . 'NO_CHECKOUT'; }
            }
        } else {
            if (strpos($note, 'NO_CHECKIN') === false) $note .= ($note ? ',' : '') . 'NO_CHECKIN';
            if ($last_ts !== null) { if (strpos($note, 'OUT_ONLY') === false) $note .= ($note ? ',' : '') . 'OUT_ONLY'; }
            else { if (strpos($note, 'NO_SCAN') === false) $note .= ($note ? ',' : '') . 'NO_SCAN'; }
        }
    }

    // row style: alternate background for readability
static $toggle = 0;
    $bg = ($toggle++ % 2 === 0) ? "#ffffff" : "#fafafa";

    $output .= '
    <tr style="background:'.$bg.';">
        <td style="text-align:center;">'.$serial.'</td>
        <td style="text-align:left; word-wrap:break-word;">'.$user.'</td>
        <td style="text-align:center;">'.$checkindate.'</td>
        <td style="text-align:center;">'.$day_of_week.'</td>
        <td style="text-align:center;">'.$first_in.'</td>
        <td style="text-align:center;">'.$last_out.'</td>
        <td style="text-align:center;">'.$late_str.'</td>
        <td style="text-align:right; padding-right:6px;">'.$total_minutes.'</td>
        <td style="text-align:right; padding-right:6px;">'.$ot_minutes.'</td>
        <td style="text-align:center;">'.$place.'</td>
        <td style="text-align:left;">'.$note.'</td>
    </tr>';
}

$output .= '
  </tbody>
</table>
';

// send as Excel
$filename = 'Nhat_ky_cham_cong_' . $startDate . '.xls';
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename={$filename}");
echo $output;
exit();
?>
