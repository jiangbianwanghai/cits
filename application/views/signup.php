<?php include('common_top.php');?>
<body class="signin">
<section>
  <div class="signinpanel"> 
    <div class="row">
      <div class="col-md-7">
        <div class="signin-info">
          <div class="logopanel">
              <h1><span>//</span> 巧克力任务跟踪系统 <span>//</span></h1>
          </div><!-- logopanel -->
          <div class="mb20"></div>
          <h5><strong>CITS - Chocolate Issue Tracker System</strong></h5>
          <ul style="line-height:27px;">
              <li><i class="fa fa-arrow-circle-o-right"></i> 邮箱最好使用您的企业邮箱，方便提醒邮件的接收</li>
              <li><i class="fa fa-arrow-circle-o-right"></i> 程序会根据你输入的邮箱自动获取邮箱名作为你的用户名</li>
              <li><i class="fa fa-arrow-circle-o-right"></i> 邮箱名和用户名均是唯一的，不能与系统中的其他用户重复</li>
              <li><i class="fa fa-arrow-circle-o-right"></i> CITS有一些保留字是不能注册为用户名的。比如：admin,webmaster,administrator,manage等。我们有权对你使用保留字的帐号进行修改。</li>
              <li><i class="fa fa-arrow-circle-o-right"></i> 用户名只可以是英文或英文加数字或者纯数字（比如：手机号）</li>
          </ul>
        </div><!-- signin0-info -->
      </div><!-- col-sm-7 -->
      <div class="col-md-5">
        <form method="post" action="/admin/login">
            <h4 class="nomargin">注册</h4>
            <p class="mt5 mb20">已有帐号，请移步 <a href="/signin">登录</a></p>
            <input name="email" id="email" type="text" class="form-control email" placeholder="建议使用工作邮箱" />
            <input name="username" id="username" type="text" class="form-control uname" placeholder="用户名" />
            <input name="password" id="password" type="password" class="form-control pword" placeholder="密码" />
            <button name="button" id="button" type="button" class="btn btn-success btn-block">确认注册</button> 
        </form>
      </div><!-- col-sm-5 -->
    </div><!-- row -->
    <div class="signup-footer">
      <div class="pull-left">
          &copy; 2016. All Rights Reserved.
      </div>
      <div class="pull-right">
          Page rendered in <strong>{elapsed_time}</strong> seconds.
      </div>
    </div>
  </div><!-- signin -->
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script type="text/javascript">

  //注册
  function signup() {
    $.ajax({
      type: "POST",
      url: "/signup/add",
      data:{'username':$("#username").val(), 'email':$("#email").val(), 'password':$("#password").val()},
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
          setTimeout(function(){
            location.href = '/';
          }, 1000);
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

  $(document).ready(function(){

    //提交按钮触发
    $("#button").click(function(){
      signup();
    });

    //回车键触发
    $('input:text:first').focus();
    var $inp = $('input');
    $inp.keypress(function (e) {
      var key = e.which; //e.which是按键的值 
      if (key == 13) { 
        signup();
      } 
    }); 

    //邮箱名作为用户名
    $("#username").focus(function() {
      var email = $("#email").val();
      email=email.substring(0,email.indexOf("@"));
      $(this).val(email);
    });

  });
</script>
</body>
</html>
