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
                个人面板预留位置
              </blockquote>
              <div class="row">
                <div class="col-xs-6 text-center">
                  <span>注册时间：</span>
                </div>
                <div class="col-xs-6 text-center">
                  <span>最后登录时间：</span>
                </div>
              </div>
            </div>
          </div><!-- panel -->
        </div>
        <div class="col-sm-6 col-md-9">
          <div class="panel panel-default">
            <div class="panel-body">
              <div id="container" style="min-width: 310px; height: 230px; margin: 0 auto"></div>
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
          text: '时间范围：最近30天',
          x: -20 //center
      },
      xAxis: {
          categories: ['1', '2', '3', '4', '5', '6',
              '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17',
              '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28',
              '29', '30', '31']
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
          data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 0, 23.3, 0, 0, 9.6,7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6, 10, 12,13,14,32,44,29,19]
      }, {
          name: '处理的BUG',
          data: [0, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 0, 12, 0, 8.6, 2.5,0, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5,3,4,8,9,12,33,23,10]
      }]
  });
});
</script>
</body>
</html>
