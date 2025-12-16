<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Danh sách nhân viên</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/favicon.png">
    
    <link rel="stylesheet" type="text/css" href="css/Users.css"/>

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    
    <script>
      $(window).on("load resize ", function() {
        var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
        $('.tbl-header').css({'padding-right':scrollWidth});
    }).resize();
    </script>
</head>
<body>
<?php include'header.php'; ?> 
<main>
    <section class="card-wrapper slideInDown animated">
        <h1 class="page-title">Danh sách nhân viên</h1>
        
        <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Họ và tên</th>
                  <th>Giới tính</th>
                  <th>Mã nhân viên</th>
                  <th>Mã thẻ</th>
                  <th>Nơi làm việc</th>
                  <th>Ngày tạo</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  require'connectDB.php';
                  $sql = "SELECT * FROM users WHERE add_card=1 ORDER BY id DESC";
                  $result = mysqli_stmt_init($conn);
                  if (!mysqli_stmt_prepare($result, $sql)) {
                      echo '<tr><td colspan="5" class="text-center">Lỗi SQL</td></tr>';
                  }
                  else{
                      mysqli_stmt_execute($result);
                      $resultl = mysqli_stmt_get_result($result);
                      if (mysqli_num_rows($resultl) > 0){
                          while ($row = mysqli_fetch_assoc($resultl)){
                  ?>
                              <TR>
                                  <TD><?php echo $row['username'];?></TD>
                                  <TD><?php echo $row['gender'];?></TD>
                                  <TD><?php echo $row['serialnumber'];?></TD>
                                  <TD><span class="badge-card"><?php echo $row['card_uid'];?></span></TD>
                                  <TD><?php echo $row['device_dep'];?></TD>
                                  <TD><?php echo $row['user_date'];?></TD>
                              </TR>
                <?php
                        }   
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding: 30px; color: #777;">Chưa có dữ liệu.</td></tr>';
                    }
                  }
                ?>
              </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>