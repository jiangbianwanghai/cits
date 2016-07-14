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
              <blockquote class="serif italic text-center">
                工作岗位：研发人员
              </blockquote>
              <div class="row">
                <div class="col-xs-6 text-center">
                  <span>注册时间：<br /><?php echo date("Y/m/d H:i:s", $profile['add_time']); ?></span>
                </div>
                <div class="col-xs-6 text-center">
                  <span>最后登录时间：<br /><?php if ($profile['last_login_time']) { echo date("Y/m/d H:i:s", $profile['last_login_time']); } else { echo '-'; } ?></span>
                </div>
              </div>
            </div>
          </div><!-- panel -->
        </div>
        <div class="col-sm-6 col-md-9">
          <div class="panel panel-default">
            <div class="panel-body">
              <div id="container" style="min-width: 310px; height: 250px; margin: 0 auto"></div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>
<script src="<?php echo STATIC_HOST; ?>/chart/js/highcharts.js"></script>
<script src="<?php echo STATIC_HOST; ?>/chart/js/modules/exporting.js"></script>
<script type="text/javascript">
$(function () {
  $('#container').highcharts({
      title: {
          text: '统计',
          x: -20 //center
      },
      xAxis: {
          categories: [<?php foreach ($report as $key => $value) {
            echo "'".$value['perday']."',";
          }?>]
      },
      yAxis: {
          title: {
              text: '数量'
          },
          plotLines: [{
              value: 0,
              width: 1,
              color: '#808080'
          }]
      },
      credits: {
          enabled:false
      },
      exporting: {
          enabled:false
      },
      series: [{
          name: '受理的任务数',
          data: [<?php foreach ($report as $key => $value) {
            echo $value['issue'].',';
          }?>]
      }, {
          name: '处理的BUG',
          data: [<?php foreach ($report as $key => $value) {
            echo $value['bug'].',';
          }?>]
      }]
  });
});
</script>
</body>
</html>
