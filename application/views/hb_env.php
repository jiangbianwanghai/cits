
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
              <h5 class="subtitle mb5">代码查询(每10分钟更新，只显示当天通过cap脚本发布的代码) <label><input id="less" type="checkbox" > 显示有变更的 </label></h5>
              <div class="table-responsive" style="overflow-x:auto;">
                <table class="table table-hover table-bordered">
                  <thead>
                    <tr>
                      <th>#</th>
          <?php foreach($server as $s){ echo "<th>$s</th>";} ?>
                    </tr>
                  </thead>
                  <tbody>
          <?php foreach($repo as $k=>$r){ ?>
              <tr><?php echo "<td>$k</td>"; foreach($server as $s){ if(isset($r[$s])){echo "<td>$r[$s]</td>";}else{echo "<td><span class=\"label label-default\">N/A</span></td>"; }} ?></tr>
          <?php } ?>
                  </tbody>
                </table>
              </div><!-- table-responsive -->
            </div><!-- panel-body -->
          </div><!-- panel -->
        </div><!-- col-md-9 -->
      </div><!--row -->
      <p class="text-right"><small>页面执行时间 <em>{elapsed_time}</em> 秒 使用内存 {memory_usage}</small></p>
    </div><!-- contentpanel -->
    
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>
<script>
function more(){
$('.table tr').each(function () {
    var $tr = $(this);
    if ($tr.find('.label-default').length === 4) {
        $tr.show();
    }
});
var date = new Date();
date.setTime(date.getTime() + (86400 * 30 * 1000));
$.cookie('less', '0', {expires: date});
}
function less(){
$('.table tr').each(function () {
    var $tr = $(this);
    if ($tr.find('.label-default').length === 4) {
        $tr.hide();
    }
});
var date = new Date();
date.setTime(date.getTime() + (86400 * 30 * 1000));
$.cookie('less', '1', {expires: date});
}

if(document.cookie.indexOf('less=1') > -1){$('#less').attr("checked", 'checked'); less(); }
if(document.cookie.indexOf('less=0') > -1){$('#less').attr("checked", false); more(); }

$('#less').click(function (){if (this.checked == true) {less();}else{more();}});

</script>
</body>
</html>
