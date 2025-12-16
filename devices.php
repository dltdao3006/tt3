<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Quản lý thiết bị</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="css/devices.css"/>

  <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha1256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="js/bootbox.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.js"></script>
  <script src="js/dev_config.js"></script>
  <script>
      $(window).on("load resize ", function() {
        var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
        $('.tbl-header').css({'padding-right':scrollWidth});
    }).resize();
  </script>
  <script>
    $(document).ready(function(){
        $.ajax({
            url: "dev_up.php",
            type: 'POST',
            data: { 'dev_up': 1 }
          }).done(function(data) {
          $('#devices').html(data);
        });
    });
  </script>
</head>
<body>
<?php include'header.php';?>
<main>
    <div class="alert_dev"></div>

  <section class="container">
        <h1 class="slideInDown animated">Quản lý thiết bị</h1>

        <div class="card-wrapper slideInDown animated">
            
            <div class="card-header">
                <h3>Danh sách thiết bị</h3>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#new-device">
                    <i class="fa fa-plus-circle"></i> Thiết bị mới
                </button>
            </div>

            <div id="devices"></div>
        </div>

    <div class="modal fade" id="new-device" tabindex="-1" role="dialog" aria-labelledby="New Device" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title" id="exampleModalLongTitle">Thêm thiết bị mới</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
              <label for="dev_name"><b>Tên thiết bị:</b></label>
              <input type="text" name="dev_name" id="dev_name" placeholder="Ví dụ: Cổng chính..." required/><br><br>
              <label for="dev_dep"><b>Khu vực / Phòng ban:</b></label>
              <input type="text" name="dev_dep" id="dev_dep" placeholder="Ví dụ: Phòng IT..." required/><br>
            </div>
            <div class="modal-footer">
              <button type="button" name="dev_add" id="dev_add" class="btn btn-success">Lưu thiết bị</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </form>
        </div>
      </div>
    </div>
    </section>
</main>
</body>
</html>