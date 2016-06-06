<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-group"></i> 项目团队列表 <span>以下是所有的项目团队</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">我的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li class="active">项目团队</li>
        </ol>
      </div>
    </div><!-- pageheader -->

    <div class="contentpanel panel-email">
      <div class="row">
        <div class="col-sm-3 col-lg-2">
          <h5 class="subtitle">快捷方式</h5>
          <ul class="nav nav-pills nav-stacked nav-email mb20">
            <li><a href="/project"><i class="glyphicon glyphicon-folder-close"></i> 所有项目团队</a></li>
            <li><a href="/project/my"><i class="glyphicon glyphicon-folder-close"></i> 我的项目团队</a></li>
          </ul>
        </div>
        <div class="col-sm-9 col-lg-10">
          <div class="panel panel-default">
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-email">
                  <tbody>
                    <?php
                      if ($rows) {
                        $weekarray=array("日","一","二","三","四","五","六");
                        foreach ($rows as $value) {
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
                    ?>
                    <tr class="unread" id="item-<?php echo $value['id'];?>">
                      <td>
                        <div class="ckbox ckbox-success">
                          <input type="checkbox" class="chk" name="itemchk" id="checkbox<?php echo $value['id'];?>" value="<?php echo $value['id'];?>">
                          <label for="checkbox<?php echo $value['id'];?>"></label>
                        </div>
                      </td>
                      <td>
                        <a href="javascript:;" item-id="<?php echo $value['id'];?>" class="star<?php if ($this->uri->segment(2, '') == 'star') { echo ' star-checked'; } else { if (isset($star[$value['id']])) echo ' star-checked'; }?>"><i class="glyphicon glyphicon-star"></i></a>
                      </td>
                      <td class="description" projectid="<?php echo $value['id'];?>"><?php echo $value['project_name']; ?></td>
                      <td align="center" width="50">
                        <a href="/conf/profile/<?php echo $value['add_user'];?>" class="pull-left">
                          <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['add_user']]['username'].'.jpg'; ?>" align="absmiddle" title="项目团队创始人：<?php echo $users[$value['add_user']]['realname'];?>"></div>
                        </a>
                      </td>
                      <td width="140"><span class="media-meta pull-right"><?php echo date("Y/m/d H:i", $value['add_time'])?></span></td>
                    </tr>
                    <tr id="description-<?php echo $value['id'];?>" style="display:none;"><td colspan="5" style="background-color:#fff;"><div style="padding:10px;line-height:1.2em"><blockquote style="font-size:14px;"><i class="fa fa-quote-left"></i><p><?php echo $value['project_discription']; ?></p><small><?php echo $value['project_name']; ?> 的简介</small></blockquote></div></td></tr>
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
            </div><!-- panel-body -->
          </div><!-- panel -->
        </div>
      </div>
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>
<?php include('common_js.php');?>
<script>
$(document).ready(function(){
  //开启和关闭项目团队简介
  $('.description').click(function(){
    var id = $(this).attr('projectid');
    if(!$(this).hasClass('open')) {
      $("#description-"+id).show();
      $(this).addClass('open');
      $("#item-"+id).removeClass('unread');
    } else {
      $("#description-"+id).hide();
      $(this).removeClass('open');
      $("#item-"+id).addClass('unread');
    }
  });

  //星标
  $('.star').click(function(){
    if(!jQuery(this).hasClass('star-checked')) {
        jQuery(this).addClass('star-checked');
        var id = jQuery(this).attr('item-id');
        $.ajax({
          type: "GET",
          dataType: "JSON",
          url: "/project/star_add?id="+id,
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
      var id = jQuery(this).attr('item-id');
      $.ajax({
        type: "GET",
        dataType: "JSON",
        url: "/project/star_del?id="+id,
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
