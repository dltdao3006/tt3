<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nhật ký chấm công</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/userslog.css">

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha1256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>   
    <script type="text/javascript" src="js/bootbox.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script src="js/user_log.js"></script>
    <script>
      $(window).on("load resize ", function() {
        var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
        $('.tbl-header').css({'padding-right':scrollWidth});
    }).resize();
    </script>
    <script>
      $(document).ready(function(){
        $.ajax({
          url: "user_log_up.php",
          type: 'POST',
          data: { 'select_date': 1 }
          }).done(function(data) {
            $('#userslog').html(data);
          });

        setInterval(function(){
          $.ajax({
            url: "user_log_up.php",
            type: 'POST',
            data: { 'select_date': 0 }
            }).done(function(data) {
              $('#userslog').html(data);
            });
        }, 15000);
      });
    </script>
</head>
<body>
<?php include'header.php'; ?> 

<main>
  <section class="container py-lg-5">
      <h1 class="slideInDown animated">Nhật ký chấm công</h1>

      <div class="form-style-5 slideInDown animated">
        <button type="button" data-toggle="modal" data-target="#Filter-export">
            <i class="fa fa-filter"></i> Lọc dữ liệu / Xuất Kết Quả Chấm Công
        </button>
      </div>

      <div class="slideInRight animated">
        <div id="userslog"></div>
      </div>
  </section>
</main>

<div class="modal fade bd-example-modal-lg" id="Filter-export" tabindex="-1" role="dialog" aria-labelledby="Filter/Export" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg animate" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLongTitle">Lọc dữ liệu chấm công</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" action="Export_Excel.php" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-lg-6 col-sm-6">
                <div class="panel panel-primary">
                  <div class="panel-heading">Khoảng thời gian:</div>
                  <div class="panel-body">
                  <label for="Start-Date"><b>Từ ngày:</b></label>
                  <input type="date" name="date_sel_start" id="date_sel_start">
                  <label for="End -Date"><b>Đến ngày:</b></label>
                  <input type="date" name="date_sel_end" id="date_sel_end">
                  </div>
                </div>
              </div>
              <div class="col-lg-6 col-sm-6">
                <div class="panel panel-primary">
                  <div class="panel-heading">
                      Lọc theo giờ:
                    <div class="time" style="display:none">
                      <input type="radio" id="radio-one" name="time_sel" class="time_sel" value="Time_in" checked/>
                      <label for="radio-one">Time-in</label>
                      <input type="radio" id="radio-two" name="time_sel" class="time_sel" value="Time_out" />
                      <label for="radio-two">Time-out</label>
                    </div>
                  </div>
                  <div class="panel-body">
                    <label for="Start-Time"><b>Từ giờ:</b></label>
                    <input type="time" name="time_sel_start" id="time_sel_start">
                    <label for="End -Time"><b>Đến giờ:</b></label>
                    <input type="time" name="time_sel_end" id="time_sel_end">
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-4 col-sm-12">
                <label for="Fingerprint"><b>Lọc theo nhân viên:</b></label>
                <select class="card_sel" name="card_sel" id="card_sel">
                  <option value="0">Tất cả nhân viên</option>
                  <?php
                    require'connectDB.php';
                    $sql = "SELECT * FROM users WHERE add_card=1 ORDER BY id ASC";
                    $result = mysqli_stmt_init($conn);
                    if (mysqli_stmt_prepare($result, $sql)) {
                        mysqli_stmt_execute($result);
                        $resultl = mysqli_stmt_get_result($result);
                        while ($row = mysqli_fetch_assoc($resultl)){
                            echo '<option value="'.$row['card_uid'].'">'.$row['username'].'</option>';
                        }
                    }
                  ?>
                </select>
              </div>
              <div class="col-lg-4 col-sm-12">
                <label for="Device"><b>Lọc theo phòng ban:</b></label>
                <select class="dev_sel" name="dev_sel" id="dev_sel">
                  <option value="0">Tất cả phòng ban</option>
                  <?php
                    require'connectDB.php';
                    $sql = "SELECT * FROM devices ORDER BY device_dep ASC";
                    $result = mysqli_stmt_init($conn);
                    if (mysqli_stmt_prepare($result, $sql)) {
                        mysqli_stmt_execute($result);
                        $resultl = mysqli_stmt_get_result($result);
                        while ($row = mysqli_fetch_assoc($resultl)){
                            echo '<option value="'.$row['device_uid'].'">'.$row['device_dep'].'</option>';
                        }
                    }
                  ?>
                </select>
              </div>
              <div class="col-lg-4 col-sm-12">
                <label><b>Xuất kết quả chấm công:</b></label>
                <input type="submit" name="To_Excel" value="Xuất Excel" class="btn btn-primary" style="width:100%">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" name="user_log" id="user_log" class="btn btn-success">Lọc dữ liệu</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>