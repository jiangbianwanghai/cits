<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-group"></i> 提醒列表 <span>我的提醒列表</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">我的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li class="active">提醒列表</li>
        </ol>
      </div>
    </div><!-- pageheader -->

    <div class="contentpanel panel-email">
      <div class="row">
        <div class="col-sm-3 col-lg-2">
          <h5 class="subtitle">快捷方式</h5>

        </div>
        <div class="col-sm-9 col-lg-10">
          <div class="panel panel-default">
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-email">
                  <tbody>
                    <?php
                      if ($notify['data']) {
                        $weekarray=array("日","一","二","三","四","五","六");
                        foreach ($notify['data']as $value) {
                          $timeDay = date("Ymd", $value['add_time']);
                          if (!isset($timeGroup[$timeDay])) {
                            if ($timeDay == date("Ymd", time())) {
                              $day = '<span style="color:green">今天</span>';
                            } else {
                              $day = date('Y-m-d', $value['add_time']).' 星期'.$weekarray[date("w",$value['add_time'])];
                            }
                            echo '<tr><td colspan="8"><span class="fa fa-calendar"></span> 创建时间：'.$day.'</td></tr>';
                          }
                        $timeGroup[$timeDay] = 1;
                        $sha = $this->encryption->encrypt($value['id']);
                        $md5 = md5($value['id']);
                    ?>
                    <tr class="unread" id="item-<?php echo $md5;?>">
                      <td>
                        <div class="ckbox ckbox-success">
                          <input type="checkbox" class="chk" name="itemchk" id="checkbox<?php echo $md5;?>" value="<?php echo $sha;?>">
                          <label for="checkbox<?php echo $md5;?>"></label>
                        </div>
                      </td>
                      <td>
                        <a href="javascript:;" item-id="<?php echo $sha;?>" class="star"><i class="fa <?php if ($value['is_read']) { echo 'fa-bell-o'; } else { echo 'fa-bell'; } ?>"></i></a>
                      </td>
                      <td class="description" projectid="<?php echo $md5;?>"><?php echo $users[$value['sender']]['realname'];?> <?php echo $value['subject']; ?></td>
                      <td width="140"><span class="media-meta pull-right"><?php echo date("Y/m/d H:i", $value['add_time'])?></span></td>
                    </tr>
                    <tr id="description-<?php echo $md5;?>" style="display:none;"><td colspan="5" style="background-color:#fff;">content</div></td></tr>
                    <?php
                        }
                      } else {
                    ?>
                      <tr><td align="center">无数据~</td></tr>
                    <?php
                      }
                    ?>
                  </tbody>
                </table>
              </div><!-- table-responsive -->
              <?php echo $pages; ?>
            </div><!-- panel-body -->
          </div><!-- panel -->
        </div><!-- col-sm-9 -->
        <p class="text-right"><small>页面执行时间 <em>{elapsed_time}</em> 秒 使用内存 {memory_usage}</small></p>
      </div>
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>
<script>
$(document).ready(function(){

});
</script>
</body>
</html>
