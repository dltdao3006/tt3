<head>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel='stylesheet' type='text/css' href="css/bootstrap.css"/>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/header.css"/>
</head>
<header>
<div class="header">
	<div class="logo">
		<a href="index.php"><img style="height:80px" src="icons/LOGO-HOA-NGAN-NGANG.png"></a>
	</div>
</div>
<?php  
  if (isset($_GET['error'])) {
		if ($_GET['error'] == "wrongpasswordup") {
			echo '	<script type="text/javascript">
					 	setTimeout(function () {
			                $(".up_info1").fadeIn(200);
			                $(".up_info1").text("Sai mật khaẩu!!");
			                $("#admin-account").modal("show");
		              	}, 500);
		              	setTimeout(function () {
		                	$(".up_info1").fadeOut(1000);
		              	}, 3000);
					</script>';
		}
	} 
	if (isset($_GET['success'])) {
		if ($_GET['success'] == "updated") {
			echo '	<script type="text/javascript">
			 			setTimeout(function () {
			                $(".up_info2").fadeIn(200);
			                $(".up_info2").text("Tài khoản đã cập nhật thành công");
              			}, 500);
              			setTimeout(function () {
                			$(".up_info2").fadeOut(1000);
              			}, 3000);
					</script>';
		}
	}
	if (isset($_GET['login'])) {
	    if ($_GET['login'] == "success") {
	      echo '<script type="text/javascript">
	              
	              setTimeout(function () {
	                $(".up_info2").fadeIn(200);
	                $(".up_info2").text("Đăng nhập thành công");
	              }, 500);

	              setTimeout(function () {
	                $(".up_info2").fadeOut(1000);
	              }, 4000);
	            </script> ';
	    }
	  }
?>
<div class="topnav" id="myTopnav">
	<a href="index.php">Nhân viên</a>
    <a href="ManageUsers.php">Quản lý nhân viên</a>
    <a href="UsersLog.php">Nhật ký chấm công</a>
    <a href="devices.php">Thiết bị</a>
    <?php  
    	if (isset($_SESSION['Admin-name'])) {
    		echo '<a href="#" data-toggle="modal" data-target="#admin-account">'.$_SESSION['Admin-name'].'</a>';
    		echo '<a href="logout.php">Đăng xuất</a>';
    	}
    	else{
    		echo '<a href="login.php">Đăng nhập</a>';
    	}
    ?>
    <a href="javascript:void(0);" class="icon" onclick="navFunction()">
	  <i class="fa fa-bars"></i></a>
</div>
<div class="up_info1 alert-danger"></div>
<div class="up_info2 alert-success"></div>
</header>
<script>
	function navFunction() {
	  var x = document.getElementById("myTopnav");
	  if (x.className === "topnav") {
	    x.className += " responsive";
	  } else {
	    x.className = "topnav";
	  }
	}
</script>

<!-- Account Update -->
<div class="modal fade" id="admin-account" tabindex="-1" role="dialog" aria-labelledby="Admin Update" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
      
      <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid #f1f5f9; padding: 20px; border-radius: 12px 12px 0 0;">
        <h3 class="modal-title" id="exampleModalLongTitle" style="color: #1e3a8a; font-weight: 700; font-size: 18px; margin: 0;">
            <i class="fa fa-user-cog"></i> Cập nhật thông tin quản trị
        </h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="opacity: 0.5;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="ac_update.php" method="POST" enctype="multipart/form-data">
          <div class="modal-body" style="padding: 25px; background-color: #fff;">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="User-mail" style="font-weight: 600; color: #333; margin-bottom: 8px; display: block;">Tên người quản trị:</label>
                <input type="text" name="up_name" class="form-control" placeholder="Nhập tên..." value="<?php echo $_SESSION['Admin-name']; ?>" required 
                       style="height: 45px; border-radius: 8px; background-color: #f8fafc; border: 1px solid #e2e8f0;"/>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="User-mail" style="font-weight: 600; color: #333; margin-bottom: 8px; display: block;">E-mail:</label>
                <input type="email" name="up_email" class="form-control" placeholder="Nhập email..." value="<?php echo $_SESSION['Admin-email']; ?>" required 
                       style="height: 45px; border-radius: 8px; background-color: #f8fafc; border: 1px solid #e2e8f0;"/>
            </div>

            <div class="form-group" style="margin-bottom: 10px;">
                <label for="User-psw" style="font-weight: 600; color: #333; margin-bottom: 8px; display: block;">Mật khẩu hiện tại:</label>
                <input type="password" name="up_pwd" class="form-control" placeholder="Nhập mật khẩu để xác nhận..." required 
                       style="height: 45px; border-radius: 8px; background-color: #f8fafc; border: 1px solid #e2e8f0;"/>
            </div>
          </div>

          <div class="modal-footer" style="background-color: #fff; border-top: 1px solid #f1f5f9; padding: 15px 25px; border-radius: 0 0 12px 12px;">
            <button type="submit" name="update" class="btn btn-primary" style="background-color: #3b82f6; border: none; padding: 10px 20px; font-weight: 600; border-radius: 6px;">Lưu thay đổi</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background-color: #f1f5f9; color: #333; border: 1px solid #e2e8f0; padding: 10px 20px; font-weight: 600; border-radius: 6px;">Đóng</button>
          </div>
      </form>
    </div>
  </div>
</div>
<!-- //Account Update -->
	

	
