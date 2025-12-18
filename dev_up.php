<?php 
session_start();
// Mặc định role là 3 (User) nếu chưa có session
$role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 3;
?>
<div class="table-responsive">          
    <table class="table">
        <thead>
            <tr>
                <th>Tên thiết bị</th>
                <th>Mã thiết bị</th>
                <th>Ngày tạo</th>
                <th>Cấu hình</th>
                <?php if ($role == 1) { ?>
                    <th>Điều chỉnh</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php  
                require 'connectDB.php';
                $sql = "SELECT * FROM devices ORDER BY id DESC";
                $result = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo '<p class="error">SQL Error</p>';
                } 
                else{
                    mysqli_stmt_execute($result);
                    $resultl = mysqli_stmt_get_result($result);
                    echo '<form action="" method="POST" enctype="multipart/form-data">';
                    while ($row = mysqli_fetch_assoc($resultl)){

                        // 1. XỬ LÝ MODE (GIỮ NGUYÊN)
                        $radio1 = ($row["device_mode"] == 0) ? "checked" : "" ;
                        $radio2 = ($row["device_mode"] == 1) ? "checked" : "" ;

                        $de_mode = '<div class="mode_select">
                                    <input type="radio" id="'.$row["id"].'-one" name="'.$row["id"].'" class="mode_sel" data-id="'.$row["id"].'" value="0" '.$radio1.'/>
                                    <label for="'.$row["id"].'-one">Đăng ký mới</label>
                                    <input type="radio" id="'.$row["id"].'-two" name="'.$row["id"].'" class="mode_sel" data-id="'.$row["id"].'" value="1" '.$radio2.'/>
                                    <label for="'.$row["id"].'-two">Chấm công</label>
                                    </div>';

                        // 2. XỬ LÝ MÃ THIẾT BỊ VÀ NÚT UPDATE TOKEN
                        $device_uid_display = "";
                        $btn_update_token = "";

                        if ($role == 2) {
                            // Nếu là Mod: Che mã thiết bị, Ẩn nút update
                            // Chỉ hiện 4 ký tự cuối
                            $device_uid_display = "********" . substr($row["device_uid"], -4);
                            $btn_update_token = ""; 
                        } else {
                            // Nếu là Admin: Hiện full, Hiện nút update
                            $device_uid_display = $row["device_uid"];
                            $btn_update_token = '<button type="button" class="dev_uid_up btn btn-warning" id="del_'.$row["id"].'" data-id="'.$row["id"].'" title="Cập nhật Token của thiết bị này"><span class="glyphicon glyphicon-refresh"> </span></button>';
                        }

                        // 3. XỬ LÝ NÚT XÓA (Chỉ Admin có)
                        $delete_cell = "";
                        if ($role == 1) {
                            $delete_cell = '<td>
                                                <button type="button" class="dev_del btn btn-danger" id="del_'.$row["id"].'" data-id="'.$row["id"].'" title="Xóa thiết bị này"><span class="glyphicon glyphicon-trash"></span></button>
                                            </td>';
                        }

                        // 4. HIỂN THỊ DÒNG
                        echo '<tr>
                                <td>'.$row["device_name"].'</td>
                                <td>
                                    '.$btn_update_token.'
                                    <span style="margin-left: 5px;">'.$device_uid_display.'</span>
                                </td>
                                <td>'.$row["device_date"].'</td>
                                <td>'.$de_mode.'</td>
                                '.$delete_cell.'
                              </tr>';
                    }
                    echo '</form>';
                }
            ?>
        </tbody>
    </table>
</div>