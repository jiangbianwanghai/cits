<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <div class="pageheader">
      <h2><i class="glyphicon glyphicon-user"></i> 上传头像 <span>请选择您的头像</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">我的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li class="active">上传头像</li>
        </ol>
      </div>
    </div><!-- pageheader -->

    <div class="contentpanel">
      <div class="row">
        <input type="file" name="head_photo" id="head_photo" value="">  
        <input type="hidden" name="photo_pic" id="photo_pic" value="">
        <!--头像显示-->
        <div id="show_photo" style="border:1px solid #f7f7f7;width:66px;height:66px;"><img id="head_photo_src" src="<?php echo AVATAR_HOST.'/liqiming.jpg'; ?>"></div>
      </div>
      
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->
  <?php include('common_tab.php');?>
</section>
<script src="<?php echo STATIC_HOST; ?>/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="<?php echo STATIC_HOST; ?>/js/ajaxfileupload.js"></script>
<script type="text/javascript" src="<?php echo STATIC_HOST; ?>/js/artDialog4.1.6/jquery.artDialog.js?skin=default"></script>
<script type="text/javascript" src="<?php echo STATIC_HOST; ?>/js/artDialog4.1.6/plugins/iframeTools.js"></script>
<script>
$(document).ready(function(e){
    
  $('#head_photo').on('change',function(){ 
    ajaxFileUploadview('head_photo','photo_pic',"/user/avatar_upload");
  }); 

});

function show_head(head_file){
  
  //插入数据库
  //$.post("{:U('Home/Index/update_head')}",{head_file:head_file},function(result){   
    $("#head_photo_src").attr('src',head_file);     
  //}); 

}

//文件上传带预览
function ajaxFileUploadview(imgid,hiddenid,url){
    
    
    $.ajaxFileUpload
    ({
      url:url,
      secureuri:false,
      fileElementId:imgid,
      dataType: 'json',
      data:{name:'logan', id:'id'},
      success: function (data, status)
      {
        if(typeof(data.error) != 'undefined')
        {
          if(data.error != '')
          {
            var dialog = art.dialog({title:false,fixed: true,padding:0});
            dialog.time(2).content("<div class='tips'>"+data.error+"</div>");
          }else{

            var resp = data.msg;            
            if(resp != '0000'){
              var dialog = art.dialog({title:false,fixed: true,padding:0});
              dialog.time(2).content("<div class='tips'>"+data.error+"</div>");
              return false;
            }else{
              $('#'+hiddenid).val(data.imgurl);           

              art.dialog.open("/user/avatar_crop?img="+data.imgurl,{
                title: '裁剪头像',
                width:'580px',
                height:'400px'
              });           
              
              //dialog.time(3).content("<div class='msg-all-succeed'>上传成功！</div>");
            }
                  

            
            
          }
        }
      },
      error: function (data, status, e)
      {
        var dialog = art.dialog({title:false,fixed: true,padding:0});
        dialog.time(3).content("<div class='tips'>"+e+"</div>");
      }
    })

    return false;
  }

</script>
</body>
</html>
