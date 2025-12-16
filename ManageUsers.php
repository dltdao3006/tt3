<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Manage Users</title>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<link rel="icon" type="image/png" href="images/favicon.png">
	<link rel="stylesheet" type="text/css" href="css/manageusers.css">

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha1256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="js/bootbox.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.js"></script>
	<script src="js/manage_users.js"></script>
	<script>
	  $(document).ready(function(){
	  	  $.ajax({
	        url: "manage_users_up.php"
	        }).done(function(data) {
	        $('#manage_users').html(data);
	      });
	    setInterval(function(){
	      $.ajax({
	        url: "manage_users_up.php"
	        }).done(function(data) {
	        $('#manage_users').html(data);
	      });
	    },5000);
	  });
	</script>
</head>
<body>
<?php include'header.php';?>
<main>
    <h1 class="page-title">Quản lý nhân viên</h1>

    <div class="container-layout">
        <div class="form-card slideInDown animated">
            <h3 class="form-header">Thông tin nhân viên</h3>
            
            <form enctype="multipart/form-data">
                <div class="alert_user"></div>
                <input type="hidden" name="user_id" id="user_id">
                
                <div class="input-group">
                    <input type="text" name="name" id="name" placeholder="Tên nhân viên..." required>
                </div>
                <div class="input-group">
                    <input type="text" name="number" id="number" placeholder="Số điện thoại..." required>
                </div>
                <div class="input-group">
                    <input type="email" name="email" id="email" placeholder="Email nhân viên..." required>
                </div>

                <div style="margin: 15px 0;">
                    <label style="font-weight:600; color:#333; margin-bottom:5px; display:block;">Giới tính</label>
                    <label style="margin-right:20px; color:#555; cursor:pointer;"><input type="radio" name="gender" class="gender" value="Nữ"> Nữ</label>
                    <label style="color:#555; cursor:pointer;"><input type="radio" name="gender" class="gender" value="Nam" checked="checked"> Nam</label>
                </div>

                <div class="input-group">
                    <select class="dev_sel" name="dev_sel" id="dev_sel">
                      <option value="0">All Departments</option>
                      <?php
                        require'connectDB.php';
                        $sql = "SELECT * FROM devices ORDER BY device_name ASC";
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

                <div class="btn-group-row">
                    <button type="button" name="user_add" class="btn btn-blue user_add">Thêm nhân viên</button>
                    <button type="button" name="user_upd" class="btn btn-blue user_upd">Cập nhật</button>
                </div>
                <button type="button" name="user_rmo" class="btn btn-red user_rmo">Xóa nhân viên</button>
            </form>
        </div>

        <div class="table-card slideInRight animated">
            <div id="manage_users"></div>
        </div>
    </div>
</main>
</body>
</html>