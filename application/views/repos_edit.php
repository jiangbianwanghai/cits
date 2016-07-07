<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="fa fa-pencil"></i> 代码库管理 <span>编辑代码库</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">你的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li><a href="/repos">代码库管理</a></li>
          <li class="active">编辑代码库</li>
        </ol>
      </div>
    </div>
    
    <div class="contentpanel">
      
      <div class="row">
        
         <div class="col-sm-3 col-lg-2">
          <ul class="nav nav-pills nav-stacked nav-email">
            <li><a href="/repos"><i class="fa fa-list"></i>代码库列表</a></li>
            <li class="active"><a href="/repos/edit_ajax"><i class="fa fa-plus"></i>添加代码库</a></li>
          </ul>
        </div><!-- col-sm-3 -->
        <div class="col-sm-9 col-lg-10">
          <form method="POST" id="basicForm" action="/repos/add_ajax" class="form-horizontal">
          <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">编辑代码库</h4>
                <p>请认真填写下面的选项</p>
              </div>
              <div class="panel-body">
                <div class="form-group">
                  <label class="col-sm-3 control-label">代码库名称 <span class="asterisk">*</span></label>
                  <div class="col-sm-9">
                    <input type="text" value="<?php echo $profile['repos_name'];?>" id="repos_name" name="repos_name" class="form-control" placeholder="请输入代码库名称" required />
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">代码库别名</label>
                  <div class="col-sm-9">
                    <input type="text" value="<?php echo $profile['repos_name_other'];?>" id="repos_name_other" name="repos_name_other" class="form-control" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="col-sm-3 control-label">代码库地址 <span class="asterisk">*</span></label>
                  <div class="col-sm-9">
                    <input type="text" value="<?php echo $profile['repos_url'];?>" id="repos_url" name="repos_url" class="form-control" placeholder="请输入代码库地址" required />
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">说明 <span class="asterisk">*</span></label>
                  <div class="col-sm-9">
                    <textarea id="repos_summary" name="repos_summary" rows="5" class="form-control" placeholder="请简要说明代码库的作用" required><?php echo $profile['repos_summary'];?></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">提测前合并 <span class="asterisk">*</span></label>
                  <div class="col-sm-9">
                    <div class="rdio rdio-primary">
                      <input type="radio" id="you" value="1" <?php if ($profile['merge'] === '1') { echo "checked";}?> name="merge" required />
                      <label for="you">需要</label>
                    </div><!-- rdio -->
                    <div class="rdio rdio-primary">
                      <input type="radio" value="0" id="wu" <?php if ($profile['merge'] === '0') { echo "checked";}?> name="merge">
                      <label for="wu">不需要</label>
                    </div><!-- rdio -->
                    <label class="error" for="merge"></label>
                  </div>
                </div>
              </div><!-- panel-body -->
              <div class="panel-footer">
                <div class="row">
                  <div class="col-sm-9 col-sm-offset-3">
                    <input type="hidden" name="id" value="<?php echo $id;?>" />
                    <button class="btn btn-primary" id="btnSubmit">提交</button>
                  </div>
                </div>
              </div>
            
          </div><!-- panel -->
          </form>
          
          
        </div><!-- col-md-6 -->
        
      </div><!--row -->
      
    </div><!-- contentpanel -->
    
  </div><!-- mainpanel -->
  
</section>

<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/module.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/uploader.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/hotkeys.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/simditor.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>
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
      location.href = '/repos';
    }, 2000);
  } else {
    $('#btnSubmit').removeAttr("disabled");
    jQuery.gritter.add({
      title: '提醒',
      text: data.error,
        class_name: 'growl-danger',
      sticky: false,
      time: ''
    });
  }
}

jQuery(document).ready(function(){

  $("#basicForm").submit(function(){
    $(this).ajaxSubmit({
      type:"post",
      url: "/repos/edit_ajax",
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

});
</script>
<script type="text/javascript">
$(function(){
  toolbar = [ 'title', 'bold', 'italic', 'underline', 'strikethrough',
      'color', '|', 'ol', 'ul', 'blockquote', 'code', 'table', '|',
      'link', 'image', 'hr', '|', 'indent', 'outdent' ];
  var editor = new Simditor( {
    textarea : $('#repos_summary'),
    toolbar : toolbar,  //工具栏
    defaultImage : '<?php echo STATIC_HOST.'/'; ?>/simditor-2.3.6/images/image.png', //编辑器插入图片时使用的默认图片
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
