<?php
session_start();
if (isset($_SESSION['Admin-name'])) {
  header("location: index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập hệ thống</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <script src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function(){
        // Chuyển đổi qua lại giữa form đăng nhập và quên mật khẩu
        $('.message a').click(function(e){
           e.preventDefault();
           $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
           
           // Thay đổi tiêu đề tương ứng
           var currentTitle = $('h1').text();
           if(currentTitle.includes("Đăng nhập")) {
               $('h1').html("🔑 Khôi phục mật khẩu");
           } else {
               $('h1').html("🔒 Đăng nhập hệ thống");
           }
        });
      });
    </script>
</head>
<body>
<main>
    <div class="login-page">
      <div class="form">
        <h1>🔒 Đăng nhập hệ thống</h1>

        <?php  
          if (isset($_GET['error'])) {
            if ($_GET['error'] == "invalidEmail") echo '<div class="alert alert-danger">Email không hợp lệ!</div>';
            elseif ($_GET['error'] == "sqlerror") echo '<div class="alert alert-danger">Lỗi kết nối cơ sở dữ liệu!</div>';
            elseif ($_GET['error'] == "wrongpassword") echo '<div class="alert alert-danger">Sai mật khẩu!</div>';
            elseif ($_GET['error'] == "nouser") echo '<div class="alert alert-danger">Email không tồn tại!</div>';
            elseif ($_GET['error'] == "emptyfields") echo '<div class="alert alert-danger">Vui lòng điền đầy đủ thông tin!</div>';
          }
          if (isset($_GET['reset']) && $_GET['reset'] == "success") echo '<div class="alert alert-success">Vui lòng kiểm tra email của bạn!</div>';
          if (isset($_GET['account']) && $_GET['account'] == "activated") echo '<div class="alert alert-success">Kích hoạt thành công, hãy đăng nhập!</div>';
          if (isset($_GET['active']) && $_GET['active'] == "success") echo '<div class="alert alert-success">Đường dẫn kích hoạt đã được gửi!</div>';
        ?>

        <form class="reset-form" action="reset_pass.php" method="post">
          <input type="email" name="email" placeholder="Nhập Email của bạn..." required/>
          <button type="submit" name="reset_pass">Gửi yêu cầu</button>
          <p class="message"><a href="#">Quay lại đăng nhập</a></p>
        </form>

        <form class="login-form" action="ac_login.php" method="post">
          <input type="email" name="email" id="email" placeholder="Email..." required/>
          <input type="password" name="pwd" id="pwd" placeholder="Mật khẩu..." required/>
          <button type="submit" name="login" id="login">Đăng nhập</button>
          <p class="message">Quên mật khẩu? <a href="#">Đặt lại mật khẩu</a></p>
        </form>
      </div>
    </div>
</main>

</body>
</html>