<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <?php
    //载入代码库缓存文件
    $repos = array();
    if (file_exists(APPPATH.'cache/repos.cache.php')) {
      $repos = unserialize(file_get_contents(APPPATH.'cache/repos.cache.php'));
    }
    ?>
    <div class="pageheader">
      <h2><i class="fa fa-cloud-upload"></i> 提测管理 <span><?php echo $repos[$repos_id]['repos_name'].'的'; ?>提测列表</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">你的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li><a href="/commit">提测管理</a></li>
          <li class="active"><?php echo $repos[$repos_id]['repos_name'].'的'; ?>提测列表</li>
        </ol>
      </div>
    </div>
  <div class="contentpanel panel-email">
    <div class="row">
      <div class="col-sm-3 col-lg-2">
        <ul class="nav nav-pills nav-stacked nav-email">
          <li<?php if ($this->uri->segment(1, 'index') == 'commit' && $this->uri->segment(2, 'index') == 'index' && $this->uri->segment(3, 'all') == 'all') {?> class="active"<?php } ?>><a href="/commit"><i class="fa fa-cloud-upload"></i> 提测列表</a></li>
          <li<?php if ($folder == 'to_me') { ?> class="active"<?php } ?>><a href="/commit/index/to_me"><i class="glyphicon glyphicon-folder-<?php echo $folder == 'to_me' ? 'open' : 'close'; ?>"></i> 我负责的</a></li>
          <li<?php if ($folder == 'from_me') { ?> class="active"<?php } ?>><a href="/commit/index/from_me"><i class="glyphicon glyphicon-folder-<?php echo $folder == 'from_me' ? 'open' : 'close'; ?>"></i> 我创建的</a></li>
        </ul>
        <div class="mb30"></div>
        <h5 class="subtitle">快捷方式</h5>
        <ul class="nav nav-pills nav-stacked nav-email mb20">
          <li<?php if ($this->uri->segment(2, '') == 'star') {?> class="active"<?php } ?>><a href="/commit/star"><i class="glyphicon glyphicon-star"></i> 星标</a></li>
        </ul>
      </div><!-- col-sm-3 -->
      <div class="col-sm-9 col-lg-10">
        <div class="panel panel-default">
          <div class="panel-body">
            <?php if ($this->uri->segment(2, 'index') == 'index' || $this->uri->segment(2, 'index') == 'repos') {?>
            <div class="pull-right">
              <div class="btn-group">
                <div class="btn-group nomargin">
                  <button data-toggle="dropdown" class="btn btn-sm btn-white dropdown-toggle tooltips" type="button" title="根据处理状态筛选">
                    <i class="glyphicon glyphicon-folder-<?php if ($state == 'all') { echo 'close'; } else { echo 'open'; } ?> mr5"></i> <?php if ($state == 'all') { echo '处理状态筛选'; } else { echo $commitstatefilter[$state]['name']; } ?>
                    <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu">
                    <?php if ($state != 'all') {?>
                    <li><a href="/commit/repos/<?php echo $id;?>/all/<?php echo $status; ?>"><i class="glyphicon glyphicon-folder-close mr5"></i> 查看全部状态</a></li>
                    <?php } ?>
                    <?php 
                    foreach ($commitstate as $key => $value) {
                      if ($state != $value['en_name']) {
                        echo '<li><a href="/commit/repos/'.$id.'/'.$value['en_name'].'/'.$status.'"><i class="glyphicon glyphicon-folder-close mr5"></i> '.$value['name'].'</a></li>';
                      }
                    }
                    ?>
                  </ul>
                </div>
                <div class="btn-group nomargin">
                  <button data-toggle="dropdown" class="btn btn-sm btn-white dropdown-toggle tooltips" type="button" title="根据信息状态筛选">
                    <i class="glyphicon glyphicon-folder-<?php if ($status == 'all') { echo 'close'; } else { echo 'open'; } ?> mr5"></i> <?php if ($status == 'all') { echo '信息状态筛选'; } else { echo $commitstatusfilter[$status]['name']; } ?>
                    <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu">
                    <?php if ($status != 'all') {?>
                    <li><a href="/commit/repos/<?php echo $id;?>/<?php echo $state;?>"><i class="glyphicon glyphicon-folder-close mr5"></i> 查看全部状态</a></li>
                    <?php } ?>
                    <?php 
                    foreach ($commitstatus as $key => $value) {
                      if ($status != $value['en_name']) {
                        echo '<li><a href="/commit/repos/'.$id.'/'.$state.'/'.$value['en_name'].'"><i class="glyphicon glyphicon-folder-close mr5"></i> '.$value['name'].'</a></li>';
                      }
                    }
                    ?>
                  </ul>
                </div>
              </div>
            </div><!-- pull-right -->
            <?php } ?>
            <h5 class="subtitle mb5"><?php echo $repos[$repos_id]['repos_name'].'的'; ?>提测列表</h5>
            <?php if (($rows['total']-$offset) < $per_page) { $per_page_end = $rows['total']-$offset; } else { $per_page_end = $per_page; }?>
            <p class="text-muted">查询结果：<?php echo ($offset+1).' - '.($per_page_end+$offset).' of '.$rows['total'];?></p>
            <div class="table-responsive">
              <table class="table table-email">
                <tbody>
                  <?php
                    if ($rows['data']) {
                      $weekarray=array("日","一","二","三","四","五","六");
                      if (file_exists('./cache/users.conf.php'))
                          require './cache/users.conf.php';
                      foreach ($rows['data'] as $value) {
                        $timeDay = date("Ymd", $value['add_time']);
                        if (!isset($timeGroup[$timeDay])) {
                          if ($timeDay == date("Ymd", time())) {
                            $day = '<span style="color:green">今天</span>';
                          } else {
                            $day = date('Y-m-d', $value['add_time']).' 星期'.$weekarray[date("w",$value['add_time'])];
                          }
                          echo '<tr><td colspan="9"><span class="fa fa-calendar"></span> 创建时间：'.$day.'</td></tr>';
                        }
                        $timeGroup[$timeDay] = 1;
                  ?>
                  <tr class="unread">
                    <td>
                      <div class="ckbox ckbox-success">
                          <input type="checkbox" id="checkbox<?php echo alphaid($value['id']);?>">
                          <label for="checkbox<?php echo alphaid($value['id']);?>"></label>
                      </div>
                    </td>
                    <td>
                      <a href="javascript:;" bugid="<?php echo alphaid($value['id']);?>" class="star<?php if ($this->uri->segment(2, '') == 'star') { echo ' star-checked'; } else { if (in_array($value['id'], $star)) echo ' star-checked'; }?>"><i class="glyphicon glyphicon-star"></i></a>
                    </td>
                    <td width="50px">
                      <a href="/conf/profile/<?php echo $value['add_user'];?>" class="pull-left" target="_blank">
                        <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['add_user']]['username']?>.jpg" align="absmiddle" title="提测人：<?php echo $users[$value['add_user']]['realname'];?>"></div>
                      </a>
                    </td>
                    <td width="40px">
                      <?php if (!$value['accept_user']) {?>
                      N/A
                      <?php } else { ?>
                      <a href="#" class="pull-left">
                        <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['accept_user']]['username']?>.jpg" align="absmiddle" title="受理人：<?php echo $users[$value['accept_user']]['realname'];?>"></div>
                      </a>
                      <?php } ?>
                    </td>
                    <td width="50px">
                      <?php echo '<span class="label label-'.$commitstatus[$value['status']]['span_color'].'">'.$commitstatus[$value['status']]['name'].'</span>'; ?>
                    </td>
                    <td width="70px">
                      <?php echo '<span class="label label-'.$commitstate[$value['state']]['span_color'].'">'.$commitstate[$value['state']]['name'].'</span>'; ?>
                    </td>
                    <td>
                      <?php echo $repos[$value['repos_id']]['repos_name'].'@'.$value['br'].'#'.$value['test_flag'];?>
                    </td>
                    <td>
                      <a href="/issue/view/<?php echo alphaid($value['issue']['id']);?>"><?php echo $value['issue']['issue_name'];?></a>
                    </td>
                    <td><span class="media-meta pull-right"><?php echo date("Y/m/d H:i", $value['add_time'])?></span></td>
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
              <?php echo $pages;?>
            </div><!-- table-responsive -->
          </div><!-- panel-body -->
        </div><!-- panel -->   
      </div><!-- col-sm-9 -->
    </div><!-- row -->
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
<script>
jQuery(document).ready(function(){
  
  "use strict"

  //Check
  jQuery('.ckbox input').click(function(){
      var t = jQuery(this);
      if(t.is(':checked')){
          t.closest('tr').addClass('selected');
      } else {
          t.closest('tr').removeClass('selected');
      }
  });
  
  // Star
  $('.star').click(function(){
      if(!jQuery(this).hasClass('star-checked')) {
          jQuery(this).addClass('star-checked');
          var id = jQuery(this).attr('bugid');
          $.ajax({
            type: "GET",
            dataType: "JSON",
            url: "/commit/star_add/"+id,
            success: function(data){
              if (data.status) {
                jQuery.gritter.add({
                  title: '提醒',
                  text: data.message,
                  class_name: 'growl-success',
                  sticky: false,
                  time: ''
                });
              } else {
                jQuery.gritter.add({
                  title: '提醒',
                  text: data.error,
                  class_name: 'growl-danger',
                  sticky: false,
                  time: ''
                });
              } 
            }
          });
      } else {
        jQuery(this).removeClass('star-checked');
        var id = jQuery(this).attr('bugid');
        $.ajax({
          type: "GET",
          dataType: "JSON",
          url: "/commit/star_del/"+id,
          success: function(data){
            if (data.status) {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                class_name: 'growl-success',
                sticky: false,
                time: ''
              });
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.error,
                class_name: 'growl-danger',
                sticky: false,
                time: ''
              });
            } 
          }
        });
      }
      return false;
  });

});
</script>

</body>
</html>
