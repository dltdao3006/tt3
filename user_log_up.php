<?php  
session_start();
date_default_timezone_set('Asia/Jakarta');
?>
<div class="table-responsive" style="max-height: 500px; overflow-y: auto;"> 
  <table class="table">
    <thead>
      <tr>
        <th style="width: 1%;">STT</th>
        <th style="width: 20%;">Họ và tên</th>
        <th style="width: 10%;">Mã nhân viên</th>
        <th style="width: 10%;">Nơi làm việc</th>
        <th style="width: 10%;">Ngày làm việc</th>
        <th style="width: 10%;">Giờ vào</th>
        <th style="width: 10%;">Giờ ra</th>
        <th style="width: 10%;">Đi trễ</th>
        <th style="width: 8%;">Ghi chú</th>
      </tr>
    </thead>
    <tbody>
      <?php
        require'connectDB.php';

        // Khởi tạo biến tìm kiếm mặc định là ngày hôm nay
        if (!isset($_SESSION['searchQuery'])) {
            $_SESSION['searchQuery'] = "checkindate='".date("Y-m-d")."'";
        }

        // 1. XỬ LÝ KHI NHẤN NÚT LỌC (Filter)
        if (isset($_POST['log_date'])) {
          $searchArr = [];

          // Lọc theo ngày
          if (!empty($_POST['date_sel_start']) && !empty($_POST['date_sel_end'])) {
              $Start_date = $_POST['date_sel_start'];
              $End_date = $_POST['date_sel_end'];
              $searchArr[] = "checkindate BETWEEN '$Start_date' AND '$End_date'";
          } elseif (!empty($_POST['date_sel_start'])) {
              $Start_date = $_POST['date_sel_start'];
              $searchArr[] = "checkindate='$Start_date'";
          }

          // Lọc theo giờ (Time In)
          if ($_POST['time_sel'] == "Time_in" && !empty($_POST['time_sel_start']) && !empty($_POST['time_sel_end'])) {
              $searchArr[] = "timein BETWEEN '{$_POST['time_sel_start']}' AND '{$_POST['time_sel_end']}'";
          }
          // Lọc theo giờ (Time Out)
          if ($_POST['time_sel'] == "Time_out" && !empty($_POST['time_sel_start']) && !empty($_POST['time_sel_end'])) {
              $searchArr[] = "timeout BETWEEN '{$_POST['time_sel_start']}' AND '{$_POST['time_sel_end']}'";
          }

          // Lọc theo nhân viên (card_uid)
          if (!empty($_POST['card_sel']) && $_POST['card_sel'] != 0) {
              $searchArr[] = "card_uid='{$_POST['card_sel']}'";
          }

          // Lọc theo phòng ban (device_uid)
          if (!empty($_POST['dev_uid']) && $_POST['dev_uid'] != 0) {
              $dev_uid = mysqli_real_escape_string($conn, $_POST['dev_uid']);
              $searchArr[] = "device_uid='$dev_uid'";
          }

          // --- SỬA LỖI LOGIC TẠI ĐÂY ---
          if (count($searchArr) > 0) {
              // Nếu có điều kiện lọc -> Nối lại
              $_SESSION['searchQuery'] = implode(" AND ", $searchArr);
          } else {
              // Nếu KHÔNG có điều kiện lọc nào (người dùng để trống hết)
              // -> Mặc định lấy dữ liệu ngày hôm nay (hoặc 1=1 nếu muốn lấy hết)
              $_SESSION['searchQuery'] = "checkindate='".date("Y-m-d")."'";
          }
        }
        
        // 2. KHI MỚI VÀO TRANG (Initial Load) -> CHỈ HIỆN NGÀY HÔM NAY
        if (isset($_POST['select_date']) && $_POST['select_date'] == 1) {
            $Start_date = date("Y-m-d");
            $_SESSION['searchQuery'] = "checkindate='".$Start_date."'";
        }

        // 3. THỰC HIỆN TRUY VẤN
        // Thêm kiểm tra biến $_SESSION['searchQuery'] để tránh lỗi undefined
        $queryCondition = isset($_SESSION['searchQuery']) ? $_SESSION['searchQuery'] : "checkindate='".date("Y-m-d")."'";
        $sql = "SELECT * FROM users_logs WHERE ".$queryCondition." ORDER BY id DESC";
        
        $result = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($result, $sql)) {
            // In lỗi SQL ra để debug nếu cần thiết (ẩn đi khi chạy thật)
            // echo mysqli_error($conn); 
            echo '<tr><td colspan="8" style="text-align:center;">Lỗi truy vấn cơ sở dữ liệu.</td></tr>';
        }
        else{
            mysqli_stmt_execute($result);
            $resultl = mysqli_stmt_get_result($result);
            if (mysqli_num_rows($resultl) > 0){
                while ($row = mysqli_fetch_assoc($resultl)){
                    // Tính thời gian trễ
                    $interval = "";
                    $note="";
                    if($row['timein'] != "00:00:00") {
                        $timeIn = strtotime($row['timein']);
                        $startWork = strtotime('08:00:00');
                        if(($timeIn > $startWork) && ($row['timein'] < "17:00:00")){
                            $diff = $timeIn - $startWork;
                            $interval = gmdate("H:i:s", $diff);
                        }
                    }
                    if (($row['timein'] == "00:00:00") && ($row['timeout'] == "00:00:00"))  {
                        $note = "Off";
                      }
                    elseif  ($row['timein'] <= "14:30:00" && 
                       ($row['timeout'] == "00:00:00" || $row['timeout'] == "0000-00-00 00:00:00" || empty($row['timeout']))) {
                        $note = "Chưa check-out";
                    }

        ?>
                  <TR>
                      <TD><?php echo $row['id'];?></TD>
                      <TD style="text-align: left; padding-left: 30px; font-weight: 600;"><?php echo $row['username'];?></TD>
                      <TD><?php echo $row['serialnumber'];?></TD>
                      <TD><?php echo $row['device_dep'];?></TD>
                      <TD><?php echo $row['checkindate'];?></TD>
                      <TD style="color: #10b981; font-weight:600;"><?php echo $row['timein'];?></TD>
                      <TD style="color: #ef4444; font-weight:600;"><?php echo $row['timeout'];?></TD>
                      <TD style="color: #f59e0b; font-weight: bold;"><?php echo $interval; ?></TD>
                      <TD style="color: #0000FF; font-weight: bold;"><?php echo $note; ?></TD>
                  </TR>
      <?php
                }
            } else {
                // Thông báo khi không có dữ liệu
                echo '<tr><td colspan="8" style="padding: 40px; color: #666; text-align: center;">
                        Không tìm thấy dữ liệu phù hợp.<br>
                        <small>Vui lòng kiểm tra lại bộ lọc ngày tháng.</small>
                      </td></tr>';
            }
        }
      ?>
    </tbody>
  </table>
</div>