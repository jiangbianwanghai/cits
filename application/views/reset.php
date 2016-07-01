<?php include('common_top.php');?>
<body class="signin">
<section>
  <div class="signinpanel">
    <div class="row">
      <div class="col-md-3">
      </div><!-- col-sm-7 -->
      <div class="col-md-6">
      <form method="post" action="/reset/send">
        <h4 class="nomargin">重设密码</h4>
        <input name="password" id="password" type="password" class="form-control pword" placeholder="请输入你的新密码" />
        <input type="hidden" id="token" name="token" value="<?php echo $token;?>" />
        <button name="button" id="button" type="button" class="btn btn-success btn-block">提交</button>
      </form>
      </div><!-- col-sm-5 -->
    </div><!-- row -->
  </div><!-- signin -->
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script type="text/javascript">
  function reset() {
    $('#button').text('提交中...');
    $("#button").attr("disabled", true);
    $.ajax({
      type: "POST",
      url: "/reset/send",
      data:{'password':$("#password").val(), 'token':$("#token").val()},
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          alert(data.message);
          location.href = '/signin';
        } else {
          $('#button').text('提交');
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
      reset();
      return false;
    });

    //回车键触发
    $('input:text:first').focus();
    var $inp = $('input');
    $inp.keypress(function (e) {
      var key = e.which; //e.which是按键的值 
      if (key == 13) { 
        reset();
        return false;
      } 
    }); 

  });
</script>

</body>
</html>
