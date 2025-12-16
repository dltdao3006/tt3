<?php  
//Connect to database
require 'connectDB.php';
/* Database connection settings */
//date_default_timezone_set('Asia/Damascus');
date_default_timezone_set('Asia/Jakarta');
$d = date("Y-m-d");
$t = date("H:i:s");


$sql = "SELECT card_uid, username, serialnumber FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
	$sql_check = "SELECT * FROM users_logs WHERE card_uid=".$row["card_uid"]." AND checkindate='$d'" ;
    $result1 = $conn->query($sql_check);

    if ($result1->num_rows > 0) {
      // output data of each row
      while($row1 = $result1->fetch_assoc()) {
        echo "Working  card_id: " . $row1["card_uid"]. " - username: " . $row1["username"]. "<br>";
      }
    } 
    else {
      echo "Off card_id: " . $row["card_uid"]. " - username: " . $row["username"]. " - serrial " . $row["serialnumber"]. "<br>";
      $name = $row["username"];
      $serialnumber = $row["serialnumber"];
      $card_uid = $row["card_uid"];
      $device_uid = $row["device_uid"];
      $sql_execute = "INSERT INTO users_logs (username, serialnumber, card_uid, device_uid, device_dep, checkindate, timein, timeout, card_out) VALUES ('$name','$serialnumber','$card_uid','$device_uid','Off', '$d', '00:00:00', '00:00:00',1)";
      $result3 = $conn->query($sql_execute);
    }

  }
} else {
  echo "0 results";
}

?>

