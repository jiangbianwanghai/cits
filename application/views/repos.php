<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-pencil"></i> 代码库管理 <span>代码库列表</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">你的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li><a href="/repos">代码库管理</a></li>
          <li class="active">代码库列表</li>
        </ol>
      </div>
    </div>
    
    <div class="contentpanel panel-email">
      
      <div class="row">
        
         <div class="col-sm-3 col-lg-2">
          <ul class="nav nav-pills nav-stacked nav-email">
            <li class="active"><a href="/repos"><i class="fa fa-list"></i>代码库列表</a></li>
            <li><a href="/repos/add"><i class="fa fa-plus"></i>添加代码库</a></li>
            <li><a href="javascript:;" id="refresh"><i class="fa fa-refresh"></i>刷新代码库</a></li>
          </ul>
        </div><!-- col-sm-3 -->
        <div class="col-sm-9 col-lg-10">
        <div class="panel panel-default">
          <div class="panel-body">
            <h5 class="subtitle mb5">代码库列表</h5>
            <?php if (($rows['total']-$offset) < $per_page) { $per_page_end = $rows['total']-$offset; } else { $per_page_end = $per_page; }?>
            <p class="text-muted">查询结果：<?php echo ($offset+1).' - '.($per_page_end+$offset).' of '.$rows['total'];?></p>
          <div class="table-responsive">
          <table class="table table-hidaction table-hover">
            <thead>
              <tr>
                <th width="200">代码库名称</th>
                <th>代码库地址</th>
                <th width="80"></th>
                <th width="120"></th>
              </tr>
            </thead>
            <tbody>
              <?php
                if ($rows['data']) {
                  foreach ($rows['data'] as $value) {
              ?>
              <tr id="tr-<?php echo alphaid($value['id']);?>">
              <td style="padding-top: 20px;"><a href="/test/repos/<?php echo alphaid($value['id']);?>"><?php echo $value['repos_name'];?></a></td>
              <td><input type="text" value="<?php echo $value['repos_url'];?>" id="readonlyinput" readonly="readonly" title="<?php echo $value['repos_url'];?>" data-toggle="tooltip" data-trigger="hover" class="form-control tooltips" /></td>
              <td style="padding-top: 20px"><a href="javascript:;" class="view label label-warning" data-toggle="modal" data-target="#code_detail" testid="<?php echo alphaid($value['id']);?>">查看详情</a>
              </td>
                <td class="table-action" style="padding-top: 20px;">
                  <a href="/repos/edit/<?php echo alphaid($value['id']);?>"><i class="fa fa-pencil"></i> 编辑</a>
                  <a href="javascript:;" class="delete-row" reposid="<?php echo alphaid($value['id']);?>"><i class="fa fa-trash-o"></i> 删除</a>
                </td>
              </tr>
              <?php
                  }
                }
              ?>
            </tbody>
          </table>
          </div><!-- table-responsive -->
          <?php echo $pages;?>
          </div>
          </div>
        </div><!-- col-md-6 -->
        
      </div><!--row -->
      <p class="text-right"><small>页面执行时间 <em>{elapsed_time}</em> 秒 使用内存 {memory_usage}</small></p>
    </div><!-- contentpanel -->
    
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>

<div class="modal fade" id="code_detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">代码库详情</h4>
      </div>
      <div class="modal-body"> 
        <div align="center"><img src="<?php echo STATIC_HOST; ?>/images/loaders/loader19.gif" /></div>           
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div><!-- modal-content -->
  </div><!-- modal-dialog -->
</div><!-- modal -->

<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/jquery.datatables.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/select2.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>
<script>
$(document).ready(function(){
  $(".delete-row").click(function(){
    var c = confirm("确认要删除吗？");
    if(c) {
      id = $(this).attr("reposid");
      $.ajax({
        type: "GET",
        url: "/repos/del/"+id,
        dataType: "JSON",
        success: function(data){
          if (data.status) {
            $("#tr-"+id).fadeOut(function(){
              $("#tr-"+id).remove();
            });
            return false;
          } else {
            jQuery.gritter.add({
              title: '提醒',
              text: data.message,
              class_name: 'growl-danger',
              sticky: false,
              time: ''
            });
          };
        }
      });
    }
  });

  $("#refresh").click(function(){
    $.ajax({
      type: "GET",
      url: "/repos/refresh",
      dataType: "JSON",
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
        };
      }
    });
  });

  $(".view").click(function(){
    id = $(this).attr("testid");
      $.ajax({
        type: "GET",
        url: "/repos/view/"+id,
        dataType: "JSON",
        success: function(data){
          if (data.status) {
            $(".modal-body").html('<table class="table table-striped"><tbody><tr><td width="150px">名称：</td><td>'+data.message.repos_name+'</td></tr><tr><td width="150px">别称：</td><td>'+data.message.repos_name_other+'</td></tr><tr><td width="150px">代码库地址：</td><td>'+data.message.repos_url+'</td></tr><tr><td width="150px">代码库描述：</td><td>'+data.message.repos_summary+'</td></tr><tr><td width="150px">是否要合并：</td><td>'+data.message.merge+'</td></tr><tr><td width="150px">添加人：</td><td>'+data.message.add_user+'</td></tr><tr><td width="150px">添加时间：</td><td>'+data.message.add_time+'</td></tr><tr><td width="150px">修改人：</td><td>'+data.message.last_user+'</td></tr><tr><td width="150px">修改时间：</td><td>'+data.message.last_time+'</td></tr></tbody></table>');
          } else {
           $(".modal-body").html(data.message);
          }
          
        }
      });
  });

});
</script>

</body>
</html>
