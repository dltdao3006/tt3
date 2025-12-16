<?php
/* Database connection settings */
    $servername = "localhost";
    $username = "root";     //put your phpmyadmin username.(default is "root")
    $password = "root";         //if your phpmyadmin has a password put it here.(default is "root")
    $dbname = "hethongh671c_chamcong";

//      $servername = "sql307.epizy.com";
//         $username = "epiz_33904854";     //put your phpmyadmin username.(default is "root")
//         $password = "4Z14zVZR5Cbw";          //if your phpmyadmin has a password put it here.(default is "root")
//         $dbname = "epiz_33904854_rfidattendance";
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Database Connection failed: " . $conn->connect_error);
    }
?>