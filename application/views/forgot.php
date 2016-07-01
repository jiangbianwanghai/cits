<?php include('common_top.php');?>
<body class="signin">
<section>
  <div class="signinpanel">
    <div class="row">
      <div class="col-md-3">
      </div><!-- col-sm-7 -->
      <div class="col-md-6">
      <form method="post" action="/forgot/send" id="form">
        <h4 class="nomargin">发送重置密码邮件</h4>
        <input name="email" id="email" type="text" class="form-control email" placeholder="邮件" />
        <button name="button" id="button" type="button" class="btn btn-success btn-block">发送</button>
      </form>
      </div><!-- col-sm-5 -->
    </div><!-- row -->
  </div><!-- signin -->
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script type="text/javascript">

  //验证登录函数
  function forgot() {
    $('#button').text('发送中...');
    $("#button").attr("disabled", true);
    $.ajax({
      type: "POST",
      url: "/forgot/send",
      data:{'email':$("#email").val()},
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          var url = $("#email").val();
          url=url.substring(url.indexOf("@")+1,100);
          $("#form").html('<h4 class="nomargin">'+data.message+', <a href="http://mail.'+url+'">请打开邮箱查看</h4>');
        } else {
          $('#button').text('发送');
          $('#button').removeAttr("disabled"); 
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

  $(document).ready(function(){
    
    //提交按钮触发
    $("#button").click(function(){
      forgot();
      return false;
    });

    //回车键触发
    $('input:text:first').focus();
    var $inp = $('input');
    $inp.keypress(function (e) {
      var key = e.which; //e.which是按键的值 
      if (key == 13) { 
        forgot();
        return false;
      } 
    }); 

  });
</script>

</body>
</html>
