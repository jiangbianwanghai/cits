
<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<style type="text/css">
th,td{white-space:nowrap;}
</style>
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-suitcase"></i> 实时大盘 <span>BUG列表</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">你的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li><a href="/heartbeat">实时大盘</a></li>
          <li class="active">BUG列表</li>
        </ol>
      </div>
    </div>
    
    <div class="contentpanel">
      <div class="row">
        <div class="col-sm-3 col-lg-2">
        <?php include('hb_sider.php');?>
        </div><!-- col-sm-3 -->
        <div class="col-sm-9 col-lg-10">
          <div class="panel panel-default">
            <div class="panel-body">
              <h5 class="subtitle mb5">BUG列表</h5>
              <?php if (($rows['total']-$offset) < $per_page) { $per_page_end = $rows['total']-$offset; } else { $per_page_end = $per_page; }?>
              <p class="text-muted">查询结果：<?php echo ($offset+1).' - '.($per_page_end+$offset).' of '.$rows['total'];?></p>
              <div class="table-responsive" style="overflow-x:auto;">
                <table class="table table-hover table-bordered">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>当前进度</th>
                      <th>任务名称</th>
                      <th>所属任务</th>
                      <th>所属项目团队</th>
                      <th>添加人</th>
                      <th>添加时间</th>
                      <th>当前受理人</th>
                      <th>受理时间</th>
                      <th>最后修改人</th>
                      <th>修改时间</th>
                      <th>状态</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if ($rows['data']) {
                      foreach ($rows['data'] as $key => $value) {
                    ?>
                    <tr>
                      <td><?php echo ($offset + $key + 1); ?></td>
                      <td><?php echo '<span class="label label-'.$bugflow[$value['state']]['span_color'].'">'.$bugflow[$value['state']]['name'].'</span>'; ?></td>
                      <td><?php if ($value['level']) {?><?php echo "<strong style='color:#ff0000;' title='".$level[$value['level']]['alt']."'>".$level[$value['level']]['name']."</strong> ";?><?php } ?> <a href="/bug/view/<?php echo alphaid($value['id']);?>"><?php echo $value['subject'];?></a></td>
                      <td><?php if ($value['issue_id'] && isset($issuearr[$value['issue_id']])) { echo '<a href="/issue/view/'.alphaid($value['issue_id']).'">'.$issuearr[$value['issue_id']]['issue_name'].'</a>'; } ?></td>
                      <td><?php if ($value['project_id'] && isset($project[$value['project_id']])) { echo $project[$value['project_id']]['project_name']; } ?></td>
                      <td><?php echo $users[$value['add_user']]['realname']; ?></td>
                      <td><?php echo $value['add_time'] ? date('Y/m/d H:i:s', $value['add_time']) : '-'; ?></td>
                      <td><?php echo $value['accept_user'] ? $users[$value['accept_user']]['realname'] : '-'; ?></td>
                      <td><?php echo $value['accept_time'] ? date('Y/m/d H:i:s', $value['accept_time']) : '-'; ?></td>
                      <td><?php echo $value['last_user'] ? $users[$value['last_user']]['realname'] : '-'; ?></td>
                      <td><?php echo $value['last_time'] ? date('Y/m/d H:i:s', $value['last_time']) : '-'; ?></td>
                      <td><?php if ($value['status'] == 1) echo '正常'; ?><?php if ($value['status'] == 0) echo '关闭'; ?><?php if ($value['status'] == -1) echo '已删除'; ?></td>
                    </tr>
                    <?php
                        }
                      } else {
                    ?>
                      <tr><td colspan="12" align="center">无数据~</td></tr>
                    <?php
                      }
                    ?>
                  </tbody>
                </table>
              </div><!-- table-responsive -->
              <?php echo $pages;?>
            </div><!-- panel-body -->
          </div><!-- panel -->
        </div><!-- col-md-9 -->
      </div><!--row -->
      <p class="text-right"><small>页面执行时间 <em>{elapsed_time}</em> 秒 使用内存 {memory_usage}</small></p>
    </div><!-- contentpanel -->
    
  </div><!-- mainpanel -->
  
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>

</body>
</html>
