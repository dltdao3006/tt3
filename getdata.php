<?php
require 'connectDB.php';
date_default_timezone_set('Asia/Jakarta');

header('Content-Type: text/plain; charset=utf-8');

$d = date("Y-m-d");
$t = date("H:i:s");

// Basic request validation
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo "Method_Not_Allowed";
    exit();
}

if (empty($_GET['card_uid']) || empty($_GET['device_token'])) {
    http_response_code(400);
    echo "Missing_parameters";
    exit();
}

$card_uid = $_GET['card_uid'];
$device_uid = $_GET['device_token'];

// Helper to safely prepare and execute select returning first row
function fetch_single_row($conn, $sql, $types = null, $params = []) {
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_close($stmt);
        return ['error' => 'SQL_Prepare_Error'];
    }
    if ($types !== null) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return ['error' => 'SQL_Execute_Error'];
    }
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return ['row' => $row];
}

// 1) Check device
$deviceRes = fetch_single_row($conn, "SELECT * FROM devices WHERE device_uid=?", "s", [$device_uid]);
if (isset($deviceRes['error'])) {
    http_response_code(500);
    echo "SQL_Error_Select_device";
    exit();
}
$deviceRow = $deviceRes['row'];
if (!$deviceRow) {
    http_response_code(403);
    echo "Invalid Device!";
    exit();
}

$device_mode = (int)$deviceRow['device_mode'];
$device_dep = $deviceRow['device_dep'] ?? '';

// MODE 1: normal login/logout
if ($device_mode === 1) {
    $userRes = fetch_single_row($conn, "SELECT * FROM users WHERE card_uid=?", "s", [$card_uid]);
    if (isset($userRes['error'])) {
        http_response_code(500);
        echo "SQL_Error_Select_card";
        exit();
    }
    $userRow = $userRes['row'];
    if (!$userRow) {
        echo "Not found!";
        exit();
    }

    if ((int)$userRow['add_card'] !== 1) {
        echo "Not registerd!";
        exit();
    }

    // Check device allowed for this card
    if ((string)$userRow['device_uid'] === "0") {
        echo "Not Allowed!";
        exit();
    }

    $Uname = $userRow['username'];
    $Number = $userRow['serialnumber'];

    // Check if there's an open log (card_out=0) today
    $stmt = mysqli_stmt_init($conn);
    $sql = "SELECT * FROM users_logs WHERE card_uid=? AND checkindate=? AND card_out=0 LIMIT 1";
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_close($stmt);
        echo "SQL_Error_Select_logs";
        exit();
    }
    mysqli_stmt_bind_param($stmt, "ss", $card_uid, $d);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $logRow = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    // LOGIN (no open log)
    if (!$logRow) {
        $stmt = mysqli_stmt_init($conn);
        $sql = "INSERT INTO users_logs (username, serialnumber, card_uid, device_uid, device_dep, checkindate, timein, timeout) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            mysqli_stmt_close($stmt);
            echo "SQL_Error_Insert_login";
            exit();
        }
        $timeout = "00:00:00";
        mysqli_stmt_bind_param($stmt, "ssssssss", $Uname, $Number, $card_uid, $device_uid, $device_dep, $d, $t, $timeout);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            echo "SQL_Error_Execute_Insert_login";
            exit();
        }
        mysqli_stmt_close($stmt);
        echo "login".$Uname.$t;
        exit();
    }

    // LOGOUT (close open log)
    $stmt = mysqli_stmt_init($conn);
    $sql = "UPDATE users_logs SET timeout=?, card_out=1 WHERE card_uid=? AND checkindate=? AND card_out=0";
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_close($stmt);
        echo "SQL_Error_Update_logout";
        exit();
    }
    mysqli_stmt_bind_param($stmt, "sss", $t, $card_uid, $d);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo "SQL_Error_Execute_Update_logout";
        exit();
    }
    mysqli_stmt_close($stmt);
    echo "logout".$Uname.$t;
    exit();
}

// MODE 0: add / select card
if ($device_mode === 0) {
    $userRes = fetch_single_row($conn, "SELECT * FROM users WHERE card_uid=?", "s", [$card_uid]);
    if (isset($userRes['error'])) {
        http_response_code(500);
        echo "SQL_Error_Select_card";
        exit();
    }
    $userRow = $userRes['row'];

    // Card exists in users table -> mark available/select
    if ($userRow) {
        // If some other card has card_select = 1, reset them first
        $stmt = mysqli_stmt_init($conn);
        $sqlReset = "UPDATE users SET card_select=0 WHERE card_select=1";
        if (!mysqli_stmt_prepare($stmt, $sqlReset)) {
            mysqli_stmt_close($stmt);
            echo "SQL_Error_Reset";
            exit();
        }
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            echo "SQL_Error_Execute_Reset";
            exit();
        }
        mysqli_stmt_close($stmt);

        // Set this card as selected
        $stmt = mysqli_stmt_init($conn);
        $sqlSet = "UPDATE users SET card_select=1 WHERE card_uid=?";
        if (!mysqli_stmt_prepare($stmt, $sqlSet)) {
            mysqli_stmt_close($stmt);
            echo "SQL_Error_Set_select";
            exit();
        }
        mysqli_stmt_bind_param($stmt, "s", $card_uid);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            echo "SQL_Error_Execute_Set_select";
            exit();
        }
        mysqli_stmt_close($stmt);

        echo "available";
        exit();
    }

    // New card: insert new user row and select it
    $stmt = mysqli_stmt_init($conn);
    $sqlResetAll = "UPDATE users SET card_select=0";
    if (!mysqli_stmt_prepare($stmt, $sqlResetAll)) {
        mysqli_stmt_close($stmt);
        echo "SQL_Error_Reset_All";
        exit();
    }
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo "SQL_Error_Execute_Reset_All";
        exit();
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_stmt_init($conn);
    $sqlInsert = "INSERT INTO users (card_uid, card_select, device_uid, device_dep, user_date) VALUES (?, 1, ?, ?, CURDATE())";
    if (!mysqli_stmt_prepare($stmt, $sqlInsert)) {
        mysqli_stmt_close($stmt);
        echo "SQL_Error_Insert_new_card";
        exit();
    }
    mysqli_stmt_bind_param($stmt, "sss", $card_uid, $device_uid, $device_dep);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo "SQL_Error_Execute_Insert_new_card";
        exit();
    }
    mysqli_stmt_close($stmt);

    echo "succesful";
    exit();
}

// Fallback (unexpected device_mode)
echo "Invalid_Device_Mode";
exit();
?>
