<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-bug"></i> Bug管理 <span>我的Bug列表</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">你的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li><a href="/bug">Bug管理</a></li>
          <li class="active">我的Bug列表</li>
        </ol>
      </div>
    </div>
    
    <div class="contentpanel panel-email">
      <div class="row">
        <div class="col-sm-3 col-lg-2">
          <ul class="nav nav-pills nav-stacked nav-email">
            <li class="active"><a href="/bug"><i class="fa fa-bug"></i> Bug列表</a></li>
            <li><a href="/bug/index/to_me"><i class="glyphicon glyphicon-folder-close"></i> 我负责的</a></li>
            <li><a href="/bug/index/from_me"><i class="glyphicon glyphicon-folder-close"></i> 我创建的</a></li>
          </ul>
          <div class="mb30"></div>
          
          <h5 class="subtitle">快捷方式</h5>
          <ul class="nav nav-pills nav-stacked nav-email mb20">
            <li><a href="/bug/star"><i class="glyphicon glyphicon-star"></i> 星标</a></li>
          </ul>
        </div><!-- col-sm-3 -->
            
            <div class="col-sm-9 col-lg-10">
                <?php if ($profile['status'] == '-1') { ?>
                <div class="alert alert-warning">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <strong>抱歉~</strong> 该BUG已被 <?php echo $users[$profile['last_user']]['realname']; ?> 删除.
                </div>
                <?php } ?>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="pull-right">
                            <?php if ($profile['status'] == 1 && $profile['state'] == '0' && (UID == $profile['accept_user'])) {?>
                            <div class="btn-group mr10">
                                <button class="btn btn-sm btn-primary tooltips" type="button" title="确认BUG，你可以调整严重级别" id="checkin" ids="<?php echo $bugid;?>" data-toggle="modal" data-target="#myModal">确认BUG</button>
                                <button class="btn btn-sm btn-primary tooltips" type="button" title="如果BUG反馈无效，请说明理由" id="checkout" data-toggle="modal" data-target="#myModal2">无效反馈</button>
                            </div>
                            <?php }?>
                            <?php if ($profile['status'] == 1 && $profile['state'] == 1 && ($profile['add_user'] == UID || $profile['accept_user'] == UID)) { ?>
                            <div class="btn-group mr10">
                                <button class="btn btn-sm btn-primary" type="button" id="over" ids="<?php echo $bugid;?>">已处理</button>
                            </div>
                            <?php } ?>

                            <?php if ($profile['state'] == 3 && $profile['add_user'] == UID) { ?>
                            <div class="btn-group mr10">
                                <button class="btn btn-sm btn-primary" type="button" id="return" ids="<?php echo $bugid;?>">通过回归</button>
                            </div>
                            <?php } ?>

                            <?php if ($profile['status'] == 1 && ($profile['add_user'] == UID || $profile['accept_user'] == UID)) { ?>
                            <div class="btn-group mr10">
                                <button class="btn btn-sm btn-white" type="button" id="close" ids="<?php echo $bugid;?>"><i class="fa fa-power-off"></i> 关闭此BUG</button>
                            </div>
                            <?php } ?>

                            <?php if ($profile['status'] == 0 && ($profile['add_user'] == UID || $profile['accept_user'] == UID)) { ?>
                            <div class="btn-group mr10">
                                <button class="btn btn-sm btn-white" type="button" id="open" ids="<?php echo $bugid;?>"><i class="fa fa-power-off"></i> 重新打开</button>
                            </div>
                            <?php } ?>

                            
                        </div><!-- pull-right -->

                        <div class="btn-group mr10">
                          <button class="btn btn-sm btn-white tooltips" id="back" type="button" data-toggle="tooltip" title="回到上一页面"><i class="fa fa-reply"></i></button>
                          <button class="btn btn-sm btn-white tooltips" id="info" type="button" data-toggle="tooltip" title="额外信息"><i class="glyphicon glyphicon-exclamation-sign"></i></button>
                          <?php if ($profile['status'] != '-1' && (UID == $profile['add_user'] || UID == $profile['accept_user'])) {?><button class="btn btn-sm btn-white tooltips" id="del-bug" type="button" data-toggle="tooltip" ids="<?php echo $bugid;?>" title="删除"><i class="glyphicon glyphicon-trash"></i></button><?php } ?>
                        </div>
                        
                        <div class="read-panel">
                          <div class="media">
                            <div class="pull-left">
                              <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$profile['add_user']]['username']?>.jpg" align="absmiddle" title="<?php echo $users[$profile['add_user']]['realname'];?>"></div>
                            </div>
                            <div class="media-body">
                              <span class="media-meta pull-right"><?php echo timediff($profile['add_time'], time());?>前</span>
                              <h4 class="text-primary"><?php echo $users[$profile['add_user']]['realname'];?> 把这个BUG指派给了 <?php if ($profile['status'] == 1 && ($profile['add_user'] == UID || $profile['accept_user'] == UID)) { ?><a href="javascript:;" id="country" data-type="select2" data-value="<?php echo alphaid($profile['accept_user']);?>" data-title="更改受理人"></a><?php } else { echo $users[$profile['accept_user']]['realname']; } ?></h4>
                              <small class="text-muted">BUG反馈人</small>
                              <h4 class="email-subject" style="font-size:18px;">
                              <?php if ($profile['level']) {?><?php echo "<strong style='color:#ff0000;' title='".$level[$profile['level']]['alt']."'>".$level[$profile['level']]['name']."</strong> ";?><?php } ?><?php echo $profile['subject'];?>
                              <?php
                                if ($profile['status'] == 1) {
                                  echo '<span class="label label-info">开启</span>';
                                } elseif ($profile['status'] == 0) {
                                  echo '<span class="label label-default">关闭</span>';
                                }elseif ($profile['status'] == '-1') {
                                  echo '<span class="label label-default">删除</span>';
                                }
                                ?>
                                <?php if ($profile['state'] === '-1') {?>
                                <span class="label label-default">无效反馈</span>
                                <?php } ?>
                                <?php if ($profile['state'] === '0') {?>
                                <span class="label label-default">未确认</span>
                                <?php } ?>
                                <?php if ($profile['state'] === '1') {?>
                                <span class="label label-primary">已确认</span>
                                <?php } ?>
                                <?php if ($profile['state'] === '2') {?>
                                <span class="label label-warning">处理中</span>
                                <?php } ?>
                                <?php if ($profile['state'] === '3') {?>
                                <span class="label label-info">已处理</span>
                                <?php } ?>
                                <?php if ($profile['state'] === '5') {?>
                                <span class="label label-success">通过回归</span>
                                <?php } ?>
                              </h4>
                              <p><?php echo $profile['content'];?></p>
                              <p>所属任务：<a href="/issue/view/<?php echo alphaid($issue['id']);?>"><?php echo $issue['issue_name'];?></a></p>
                            </div>
                          </div>
                        </div><!-- read-panel -->
                    </div><!-- panel-body -->
                </div><!-- panel -->

                <ul class="nav nav-tabs nav-default">
                  <li class="active"><a data-toggle="tab" href="#all"><strong>评论</strong></a></li>
                  <li><a data-toggle="tab" href="#added" id="log-list" data-id="<?php echo $bugid;?>"><strong>操作日志</strong></a></li>
                </ul>
                <div class="tab-content">
                <div id="all" class="tab-pane active"> 
                  <?php
                    if ($comment['data']) {
                      foreach ($comment['data'] as $value) {
                  ?>
                  <div class="media" id="comment-<?php echo $value['id'];?>">
                    <div class="pull-left">
                      <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['add_user']]['username']?>.jpg" align="absmiddle" title="<?php echo $users[$value['add_user']]['realname'];?>"></div>
                    </div>
                    <div class="media-body">
                      <span class="media-meta pull-right"><?php echo timediff($value['add_time'], time());?><?php if ($value['add_user'] == UID && (time() - $value['add_time']) < 3600) {?><br /><a class="del" ids="<?php echo $value['id'];?>" href="javascript:;">删除</a><?php } ?></span>
                      <h4 class="text-primary"><?php echo $users[$value['add_user']]['realname'];?></h4>
                      <small class="text-muted"><?php if ($profile['add_user'] == $value['add_user']) { echo 'BUG反馈人'; } elseif ($profile['accept_user'] == $value['add_user']) { echo 'BUG受理人'; } else { echo '路人甲'; } ?></small>
                      <div><?php echo html_entity_decode($value['content']);?></div>
        
                    </div>
                  </div>
                  <?php
                      }
                    }
                  ?>  
                  <div id="box"></div>
                  <div class="media">
                    <div class="pull-left">
                      <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[UID]['username']?>.jpg" align="absmiddle" title="<?php echo $users[UID]['realname'];?>"></div>
                    </div>
                    <div class="media-body">
                      <input type="text" class="form-control" id="post-commit" placeholder="我要发表评论">
                      <div id="simditor" style="display:none;">
                        <textarea id="content" name="content"></textarea>
                        <div class="mb10"></div>
                        <input type="hidden" value="<?php echo alphaid($profile['id']);?>" id="bug_id" name="bug_id">
                        <button class="btn btn-primary" id="btnSubmit">提交</button>
                      </div>
                    </div>
                  </div>  
                </div><!-- tab-pane -->
                  
                <div id="added" class="tab-pane">
                  <div align="center"><img src="<?php echo STATIC_HOST; ?>/images/loaders/loader19.gif" /></div>
                </div><!-- tab-pane -->
              </div><!-- tab-content -->
            </div><!-- col-sm-9 -->
            
        </div><!-- row -->
      
    </div><!-- contentpanel -->
    
  </div><!-- mainpanel -->
  
</section>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">确认BUG</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="col-sm-4">当前严重级别</label>
          <div class="col-sm-4">
            <select class="select2" data-placeholder="调整严重级别">
              <option value="1" <?php if ($profile['level'] == 1) echo 'selected="selected"';?>>[!]轻微</option>
              <option value="2" <?php if ($profile['level'] == 2) echo 'selected="selected"';?>>[!!]轻</option>
              <option value="3" <?php if ($profile['level'] == 3) echo 'selected="selected"';?>>[!!!]严重</option>
              <option value="4" <?php if ($profile['level'] == 4) echo 'selected="selected"';?>>[!!!!]非常严重</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary ajax-btn" data-dismiss="modal">提交</button>
      </div>
    </div><!-- modal-content -->
  </div><!-- modal-dialog -->
</div><!-- modal -->

<!-- Modal -->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">请说明反馈无效的理由</h4>
      </div>
      <div class="modal-body">
        <textarea class="form-control" id="msg" rows="5" placeholder="请说明反馈无效的理由"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary ajax-btn2" data-dismiss="modal">提交</button>
      </div>
    </div><!-- modal-content -->
  </div><!-- modal-dialog -->
</div><!-- modal -->

<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/module.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/uploader.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/hotkeys.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/simditor.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/jquery.datatables.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/simple-pinyin.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/select2.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/bootstrap-editable.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>

<script type="text/javascript">
$(function(){
  toolbar = [ 'title', 'bold', 'italic', 'underline', 'strikethrough',
    'color', '|', 'ol', 'ul', 'blockquote', 'code', 'table', '|',
    'link', 'image', 'hr', '|', 'indent', 'outdent' ];
  var editor = new Simditor({
    textarea : $('#content'),
    toolbar : toolbar,  //工具栏
    defaultImage : '<?php echo STATIC_HOST.'/'; ?>static/simditor-2.3.6/images/image.png', //编辑器插入图片时使用的默认图片
    pasteImage: true,
    upload: {
        url: '/dashboard/upload',
        fileKey: 'upload_file', //服务器端获取文件数据的参数名  
        connectionCount: 3,  
        leaveConfirm: '正在上传文件'
      }
  });

  $("#btnSubmit").click(function(){
    content = $("#content").val();
    content = htmlEncode(content);
    bug_id = $("#bug_id").val();
    if (!content) {
      editor.focus();
      return false;
    }
    $.ajax({
      type: "POST",
      url: "/bug/coment_add_ajax",
      data: "content="+content+"&bug_id="+bug_id,
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          $("#box").append('<div class="media"><div class="pull-left"><div class="face"><img alt="" src="'+data.message.avatar+'" align="absmiddle" title="'+data.message.realname+'"></div></div><div class="media-body"><span class="media-meta pull-right">'+data.message.addtime+'</span><h4 class="text-primary">'+data.message.realname+'</h4><small class="text-muted">'+data.message.usertype+'</small><p>'+data.message.content+'</p></div></div>');
          editor.setValue('');
        } else {
          alert('fail');
        };
      }
    });
  });

  //读取bug相关的操作日志
  $("#log-list").click(function(){
    id = $(this).attr("data-id");
      $.ajax({
        type: "GET",
        url: "/bug/log_list/"+id,
        dataType: "JSON",
        success: function(data){
          if (data.total) {
            var log_list = '';
            for(var p in data.comment){
              log_list += '<tr class="unread"><td></td><td><a href="" class="star"><i class="fa fa-dot-circle-o"></i></a></td><td><div class="media"><a href="#" class="face"><img alt="" src="'+data.comment[p].avatar+'"></a><div class="media-body"><span class="media-meta pull-right">'+data.comment[p].friendtime+'前</span><h4 class="text-primary">'+data.comment[p].realname+'</h4><small class="text-muted"></small><p class="email-summary">'+data.comment[p].content+'</p></div></div></td></tr>';
            }
            $("#added").html('<div class="table-responsive"><table class="table table-email"><tbody>'+log_list+'</tbody></table></div>');
          } else {
            $("#added").html('<div align="center">暂无操作日志</div>');
          }
        }
      });
  });

  // Select 2 (dropdown mode)
  var countries = [];
  $.each({<?php foreach($users as $val) { ?>"<?php echo alphaid($val['uid']);?>": "<?php echo $val['realname'];?>",<?php } ?> }, function(k, v) {
      countries.push({id: k, text: v});
  });

  jQuery('#country').editable({
        inputclass: 'sel-xs',
        source: countries,
        type: 'text',
        pk: 1,
        ajaxOptions: {
          type: 'GET'
        },
        url: '/bug/change_accept/<?php echo alphaid($profile["id"]);?>',
        send: 'always',
        select2: {
            width: 150,
            placeholder: '更改受理人',
            allowClear: true
        },
    });

  $(".del").click(function(){
    var c = confirm('你确定要删除吗？');
      if(c) {
        id = $(this).attr("ids");
        $.ajax({
          type: "GET",
          url: "/bug/del_comment/"+id,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              setTimeout(function () {
                $("#comment-"+id).hide();
              }, 500);
            } else {
              alert('fail');
            }
          }
        });
      }
  });

  $("#over").click(function(){
    var c = confirm('你确定已经修好了此BUG吗？如果有代码变动别忘记提交代码');
      if(c) {
        id = $(this).attr("ids");
        $.ajax({
          type: "GET",
          url: "/bug/over/"+id,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              tip(data.message, '/bug/view/'+id, 'success', 1000);
            } else {
              alert('fail');
            } 
          }
        });
      }
  });

  $("#return").click(function(){
    var c = confirm('你确定要已经回归测试了此BUG吗？');
      if(c) {
        id = $(this).attr("ids");
        $.ajax({
          type: "GET",
          url: "/bug/returnbug/"+id,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              tip(data.message, '/bug/view/'+id, 'success', 1000);
            } else {
              alert('fail');
            } 
          }
        });
      }
  });

  $(".ajax-btn").click(function(){
    level = $('.select2 option:selected').val();
    bug_id = $("#bug_id").val();
    $.ajax({
      type: "GET",
      dataType: "JSON",
      url: "/bug/checkin/"+bug_id+"/"+level,
      success: function(data){
        if (data.status) {
          tip(data.message, '/bug/view/'+bug_id, 'success', 1000);
        } else {
          alert('fail');
        } 
      }
    });
  });

  //反馈无效
  $(".ajax-btn2").click(function(){
    content = $.trim($('#msg').val());
    if (!content) {
      alert('请说明反馈无效的理由');
      return false;
    }
    bug_id = $("#bug_id").val();
    $.ajax({
      type: "POST",
      dataType: "JSON",
      url: "/bug/checkout/",
      data: "content="+content+"&bug_id="+bug_id,
      success: function(data){
        if (data.status) {
          tip(data.message, '/bug/view/'+bug_id, 'success', 1000);
        } else {
          alert('fail');
        } 
      }
    });
  });

  $('#post-commit').click(function () {
      $(this).hide();
      $('#simditor').show();
    });

  //返回上一页面
  $("#back").click(function(){
    window.history.back();
  });

  $("#info").click(function(){
    alert('功能开发中...');
  });

  $("#del-bug").click(function(){
    var c = confirm("确认要删除吗？");
    if(c) {
      id = $(this).attr("ids");
      $.ajax({
        type: "GET",
        dataType: "JSON",
        url: "/bug/del/"+id,
        success: function(data){
          if (data.status) {
            tip(data.message, '/bug/view/'+id, 'success', 1000);
          } else {
            alert('fail');
          } 
        }
      });
    }
  });

  $("#close").click(function(){
    var c = confirm("确认要关闭吗？");
    if(c) {
      id = $(this).attr("ids");
      $.ajax({
        type: "GET",
        dataType: "JSON",
        url: "/bug/close/"+id,
        success: function(data){
          if (data.status) {
            tip(data.message, '/bug/view/'+id, 'success', 1000);
          } else {
            alert(data.message);
          } 
        }
      });
    }
  });

  $("#open").click(function(){
    var c = confirm("确认要重新打开吗？");
    if(c) {
      id = $(this).attr("ids");
      $.ajax({
        type: "GET",
        dataType: "JSON",
        url: "/bug/open/"+id,
        success: function(data){
          if (data.status) {
            tip(data.message, '/bug/view/'+id, 'success', 1000);
          } else {
            alert(data.message);
          } 
        }
      });
    }
  });

  //调整严重级别
  jQuery(".select2").select2({
    width: '100%',
    minimumResultsForSearch: -1
  });

  //图片等比例缩放
  $('.media-body img').each(function() {  
    $(this).css({"cursor":"pointer"});
    var maxWidth =$(".media-body").width();
    var maxHeight =1500;  
    var ratio = 0; 
    var width = $(this).width();  
    var height = $(this).height();  
    if(width > maxWidth){    
      ratio = maxWidth / width;   
      $(this).css("width", maxWidth);    
      height = height * ratio;   
      $(this).css("height", height);  
    } 
    if(height > maxHeight){    
      ratio = maxHeight / height;  
      $(this).css("height", maxHeight);  
      width = width * ratio;   
      $(this).css("width", width); 
    }
  });

  $('.media-body img').click(function() {
    window.open($(this).attr('src'));  
  });

});

//消息提醒通用组建配置
function tip(message, url, color, sec) {
  jQuery.gritter.add({
    title: '提醒',
    text: message,
    class_name: 'growl-'+color,
    sticky: false,
    time: ''
  });
  setTimeout(function(){
    location.href = url;
  }, sec);
}
</script>

</body>
</html>
