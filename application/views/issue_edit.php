<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-tasks"></i> 任务管理 <span>编辑任务</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">你的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li><a href="/issue">任务管理</a></li>
          <li class="active">编辑任务</li>
        </ol>
      </div>
    </div><!-- pageheader -->
    <div class="contentpanel">
      <div class="row">
        <div class="col-sm-3 col-lg-2">
          <h5 class="subtitle">快捷方式</h5>
          <ul class="nav nav-pills nav-stacked nav-email">
            <li><a href="/issue"><i class="glyphicon glyphicon-folder-close"></i> 任务列表</a></li>
            <li><a href="/issue/to_me"><i class="glyphicon glyphicon-folder-close"></i> 我负责的</a></li>
            <li><a href="/issue/from_me"><i class="glyphicon glyphicon-folder-close"></i> 我创建的</a></li>
          </ul>
        </div><!-- col-sm-3 -->
        <div class="col-sm-9 col-lg-10">
          <form method="POST" id="basicForm" action="/issue/edit_ajax" class="form-horizontal">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title">编辑任务</h4>
              <p>每个任务都应该包含在计划中</p>
            </div>
              <div class="panel-body">
                <div class="col-sm-8 col-lg-9">
                  <div class="form-group">
                    <label class="control-label">任务全称 <span class="asterisk">*</span></label>
                    <input type="text" id="issue_name" name="issue_name" value="<?php echo $profile['issue_name'];?>" class="form-control" placeholder="请输入任务名称" required />
                  </div>
                  <div class="form-group">
                    <label class="control-label">说明</label>
                    <textarea id="issue_summary" name="issue_summary" rows="3" class="form-control" placeholder="请输入任务描述"><?php echo $profile['issue_summary'];?></textarea>
                  </div>
                  <div class="form-group">
                    <label class="control-label">相关链接</label>
                    <textarea id="issue_url" name="issue_url" rows="3" class="form-control" placeholder="每行一个链接，可以添加多个"><?php
                      if ($profile['url']) {
                        foreach ($profile['url'] as $key => $value) {
                          echo $value.PHP_EOL;
                        }
                      }
                      ?></textarea>
                  </div>
                </div>
                <div class="col-sm-4 col-lg-3" style="padding-left:50px;">
                  <div class="form-group">
                    <label class="control-label">任务类型 <span class="asterisk">*</span></label>
                    <div>
                      <select id="type" name="type" class="select3" data-placeholder="请选择任务类型" required>
                        <option value=""></option>
                        <option value="2"<?php if ($profile['type'] == 2) { echo " selected=\"selected\"";}?>>BUG</option>
                        <option value="1"<?php if ($profile['type'] == 1) { echo " selected=\"selected\"";}?>>TASK</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label">紧急程度 <span class="asterisk">*</span></label>
                    <div>
                      <select id="level" name="level" class="select3" data-placeholder="请选择紧急程度" required>
                        <option value=""></option>
                        <?php
                        foreach ($level as $key => $value) {
                          $selected = '';
                          $key == $profile['level'] && $selected = " selected=\"selected\"";
                          echo '<option value="'.$key.'"'.$selected.'>'.$value['name'].' - '.$value['task'].'</option>';
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div><!-- panel-body -->
              <input type="hidden" name="issueid" value="<?php echo $issueid;?>" />
              <div class="panel-footer">
                <div class="row">
                  <button class="btn btn-primary" id="btnSubmit">提交</button>
                </div>
              </div>
          </div><!-- panel -->
          </form>
        </div><!-- col-md-9 -->
      </div><!--row -->
    <p class="text-right"><small>页面执行时间 <em>{elapsed_time}</em> 秒 使用内存 {memory_usage}</small></p>
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>

<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/module.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/uploader.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/hotkeys.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/simditor.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/simple-pinyin.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/select2.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script>

function validForm(formData,jqForm,options){
  return $("#basicForm").valid();
}

function callBack(data) {
  $("#btnSubmit").attr("disabled", true);
  if(data.status){
    jQuery.gritter.add({
      title: '提醒',
      text: data.message,
      class_name: 'growl-success',
      sticky: false,
      time: ''
    });
    setTimeout(function(){
      location.href = '/issue/view/'+data.content;
    }, 1000);
  } else {
    jQuery.gritter.add({
      title: '提醒',
      text: data.error,
      class_name: 'growl-danger',
      sticky: false,
      time: ''
    });
    setTimeout(function(){
      location.href = window.location.href;
    }, 2000);
  }
}

jQuery(document).ready(function(){

  $("#basicForm").submit(function(){
    $(this).ajaxSubmit({
      type:"post",
      url: "/issue/edit_ajax",
      dataType: "JSON",
      beforeSubmit:validForm,
      success:callBack
    });
    return false;
  });

  $("#basicForm").validate({
    highlight: function(element) {
      jQuery(element).closest('.form-group').removeClass('has-success').addClass('has-error');
    },
    success: function(element) {
      jQuery(element).closest('.form-group').removeClass('has-error');
    },
  });
  
  jQuery(".select2").select2({
      width: '100%'
  });

  jQuery(".select3").select2({
      width: '100%',
      minimumResultsForSearch: -1
  });

});
</script>
<script type="text/javascript">
   $(function(){
  toolbar = [ 'title', 'bold', 'italic', 'underline', 'strikethrough',
      'color', '|', 'ol', 'ul', 'code', 'table', '|',
      'link', 'image', 'hr', '|', 'indent', 'outdent' ];
  var editor = new Simditor( {
    textarea : $('#issue_summary'),
    placeholder : '这里输入内容...',
    toolbar : toolbar,  //工具栏
    defaultImage : '<?php echo STATIC_HOST.'/'; ?>simditor-2.3.6/images/image.png', //编辑器插入图片时使用的默认图片
    pasteImage: true,
    upload: {
        url: '/dashboard/upload',
        fileKey: 'upload_file', //服务器端获取文件数据的参数名  
        connectionCount: 3,  
        leaveConfirm: '正在上传文件'
      }
  });
   })
</script>

</body>
</html>
