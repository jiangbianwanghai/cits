<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-home"></i> 我的面板 <span>显示关于你的所有任务</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">我的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li class="active">我的面板</li>
        </ol>
      </div>
    </div><!-- pageheader -->

    <div class="contentpanel">
      <div class="row">
        <div class="col-sm-6 col-md-3">
          <div class="panel panel-default widget-profile">
            <div class="panel-heading">
              <div class="cover"><img src="<?php echo STATIC_HOST; ?>/images/photos/photo2.png" alt="Cover Photo" /></div>
            </div>
            <div class="panel-body">
              <img src="<?php echo AVATAR_HOST.'/'.USER_NAME.'.jpg'; ?>" class="widget-profile-img thumbnail" width="80" height="80" alt="80x80" />
              <div class="widget-profile-title">
                <h4><?php echo REAL_NAME; ?></h4>
                <small><i class="fa fa-map-marker"></i> <?php echo USER_NAME; ?></small>
              </div>
              <div class="row">
                <div class="col-xs-6 text-center">
                  <span>我的操作记录</span>
                </div>
                <div class="col-xs-6 text-center">
                  <span>我的统计数据</span>
                </div>
              </div>
            </div>
          </div><!-- panel -->
        </div>
        <div class="col-sm-6 col-md-3">
          <div class="panel panel-default panel-alt widget-messaging">
          <div class="panel-heading">
              <div class="panel-btns">
                <a href="/issue/index/to_me" class="panel-edit"><i class="fa fa-ellipsis-h"></i></a>
              </div><!-- panel-btns -->
              <h3 class="panel-title">我受理的任务</h3>
            </div>
            <div class="panel-body" id="issue-to-me">
              <div align="center"><img src="<?php echo STATIC_HOST; ?>/images/loaders/loader19.gif" /></div>
            </div><!-- panel-body -->
          </div><!-- panel -->
        </div><!-- col-sm-6 -->

        <div class="col-sm-6 col-md-3">
          <div class="panel panel-default panel-alt widget-messaging">
          <div class="panel-heading">
              <div class="panel-btns">
                <a href="/bug/index/to_me" class="panel-edit"><i class="fa fa-ellipsis-h"></i></a>
              </div><!-- panel-btns -->
              <h3 class="panel-title">我收到的BUG</h3>
            </div>
            <div class="panel-body" id="bug-to-me">
              <div align="center"><img src="<?php echo STATIC_HOST; ?>/images/loaders/loader19.gif" /></div>
            </div><!-- panel-body -->
          </div><!-- panel -->
        </div><!-- col-sm-6 -->

        <div class="col-sm-6 col-md-3">
          <div class="panel panel-default panel-alt widget-messaging">
          <div class="panel-heading">
              <div class="panel-btns">
                <a href="/commit/index/from_me" class="panel-edit"><i class="fa fa-ellipsis-h"></i></a>
              </div><!-- panel-btns -->
              <h3 class="panel-title">我的提测记录</h3>
            </div>
            <div class="panel-body" id="commit-from-me">
              <div align="center"><img src="<?php echo STATIC_HOST; ?>/images/loaders/loader19.gif" /></div>
            </div><!-- panel-body -->
          </div><!-- panel -->
        </div><!-- col-sm-6 -->

      </div>
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>
<script type="text/javascript">
jQuery(document).ready(function() {
  //读取指派给我的bug列表
  setTimeout(function () {
    $.ajax({
      type: "GET",
      url: "/dashboard/get_bug_to_me",
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          var list = '';
          for(var p in data.output.data){
            list += '<li><small class="pull-right">'+data.output.data[p].add_time+'</small><h4 class="sender">'+(parseInt(p)+1)+'.<a href="/bug/view/'+data.output.data[p].id+'">'+data.output.data[p].subject+'</a> <span class="label label-'+data.output.data[p].bugstatus_color+'">'+data.output.data[p].bugstatus_name+'</span> <span class="label label-'+data.output.data[p].bugstate_color+'">'+data.output.data[p].bugstate_name+'</span></h4><small>'+data.output.data[p].issue_name+'</small></li>';
          }
          $("#bug-to-me").html('<ul>'+list+'</ul>');
        } else {
          $("#bug-to-me").html('<ul><li><p align="center">'+data.message+'</p></li></ul>');
        }
      }
    });
  }, 200);

  //读取我的提测记录
  setTimeout(function () {
    $.ajax({
      type: "GET",
      url: "/dashboard/get_commit_from_me",
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          var list = '';
          for(var p in data.output.data){
            list += '<li><small class="pull-right">'+data.output.data[p].add_time+'</small><h4 class="sender">'+(parseInt(p)+1)+'.<a href="/issue/view/'+data.output.data[p].issue_id+'#tr-'+data.output.data[p].id+'">'+data.output.data[p].subject+'</a> <span class="label label-'+data.output.data[p].commitstatus_color+'">'+data.output.data[p].commitstatus_name+'</span> <span class="label label-'+data.output.data[p].commitstate_color+'">'+data.output.data[p].commitstate_name+'</span></h4><small>'+data.output.data[p].issue_name+'</small></li>';
          }
          $("#commit-from-me").html('<ul>'+list+'</ul>');
        } else {
          $("#commit-from-me").html('<ul><li><p align="center">'+data.message+'</p></li></ul>');
        }
      }
    });
  }, 100);

  //读取我受理的BUG
  setTimeout(function () {
    $.ajax({
      type: "GET",
      url: "/dashboard/get_issue_to_me",
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          var list = '';
          for(var p in data.output.data){
            list += '<li><small class="pull-right">'+data.output.data[p].add_time+'</small><h4 class="sender">'+(parseInt(p)+1)+'.<a href="/issue/view/'+data.output.data[p].id+'">'+data.output.data[p].issue_name+'</a> <span class="label label-'+data.output.data[p].issuestatus_color+'">'+data.output.data[p].issuestatus_name+'</span> <span class="label label-'+data.output.data[p].workflow_color+'">'+data.output.data[p].workflow_name+'</span></h4><small>'+data.output.data[p].plan_name+'</small></li>';
          }
          $("#issue-to-me").html('<ul>'+list+'</ul>');
        } else {
          $("#issue-to-me").html('<ul><li><p align="center">'+data.message+'</p></li></ul>');
        }
      }
    });
  }, 0);
});
</script>
</body>
</html>
