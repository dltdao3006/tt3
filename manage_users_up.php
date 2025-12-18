<div class="table-responsive" style="max-height: 800px; overflow-y: auto;"> 
  <table class="table">
    <thead> <tr>
        <th>Mã thẻ</th>
        <th>Họ và tên</th>
        <th>Giới tính</th>
        <th>Mã nhân viên</th>
        <th>Phòng Ban</th>
        <th>Ngày tạo</th>
      </tr>
    </thead>
    <tbody> <?php
      require'connectDB.php';
      $sql = "SELECT * FROM users ORDER BY id DESC";
      $result = mysqli_stmt_init($conn);
      if (!mysqli_stmt_prepare($result, $sql)) {
          echo '<p class="error">SQL Error</p>';
      }
      else{
          mysqli_stmt_execute($result);
          $resultl = mysqli_stmt_get_result($result);
          if (mysqli_num_rows($resultl) > 0){
              while ($row = mysqli_fetch_assoc($resultl)){
      ?>
                  <TR>
                    <TD>
                        <form>
                        <button type="button" class="select_btn" id="<?php echo $row['card_uid'];?>" title="Chọn thẻ này">
                                <?php echo $row['card_uid'];?>
                            </button>
                            <?php if ($row['card_select'] == 1) {
                          echo "<span style='color: #10b981; margin-left: 5px;'><i class='glyphicon glyphicon-ok'></i></span>";
                        } ?>
                      </form>
                    </TD>
                  <TD><?php echo $row['username'];?></TD>
                  <TD><?php echo $row['gender'];?></TD>
                  <TD><?php echo $row['serialnumber'];?></TD>
                  <TD><?php echo $row['department'];?></TD>
                  <TD><?php echo $row['user_date'];?></TD>
                  </TR>
    <?php
            }   
        }
      }
    ?>
    </tbody>
  </table>
</div>