<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-pencil"></i> 用户面板 <span>操作日志</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">你的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li><a href="/user">用户面板</a></li>
          <li class="active">操作日志</li>
        </ol>
      </div>
    </div>
    
    <div class="contentpanel panel-email">
      <div class="row">
         <div class="col-sm-3 col-lg-2">
          <ul class="nav nav-pills nav-stacked nav-email">
            <li class="active"><a href="#"><i class="fa fa-list"></i>我的操作记录</a></li>
            <li><a href="/user"><i class="fa fa-mail-reply"></i>返回用户面板</a></li>
          </ul>
        </div><!-- col-sm-3 -->
        <div class="col-sm-9 col-lg-10">
          <div class="panel panel-default">
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-email">
                  <tbody>
                    <?php
                      if ($logs['data']) {
                        $weekarray=array("日","一","二","三","四","五","六");
                        foreach ($logs['data']as $value) {
                          $timeDay = date("Ymd", $value['add_time']);
                          if (!isset($timeGroup[$timeDay])) {
                            if ($timeDay == date("Ymd", time())) {
                              $day = '<span style="color:green">今天</span>';
                            } else {
                              $day = date('Y-m-d', $value['add_time']).' 星期'.$weekarray[date("w",$value['add_time'])];
                            }
                            echo '<tr><td colspan="4"><span class="fa fa-calendar"></span> 创建时间：'.$day.'</td></tr>';
                          }
                        $timeGroup[$timeDay] = 1;
                    ?>
                    <tr class="unread">
                      <td></td>
                      <td><a href="" class="star"><i class="fa fa-dot-circle-o"></i></a></td>
                      <td class="description"><?php echo $users[$value['sender']]['realname'];?> <?php echo $value['text']; ?></td>
                      <td width="140"><span class="media-meta pull-right"><?php echo date("Y/m/d H:i", $value['add_time'])?></span></td>
                    </tr>
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
        
      </div><!--row -->
      <p class="text-right"><small>页面执行时间 <em>{elapsed_time}</em> 秒 使用内存 {memory_usage}</small></p>
    </div><!-- contentpanel -->
    
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>

<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/jquery.datatables.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/select2.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>

</body>
</html>
