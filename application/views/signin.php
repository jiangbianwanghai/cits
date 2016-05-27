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
              <li><i class="fa fa-arrow-circle-o-right"></i> 开发计划轻松掌握</li>
              <li><i class="fa fa-arrow-circle-o-right"></i> 任务执行情况跟踪</li>
              <li><i class="fa fa-arrow-circle-o-right"></i> 代码提测一键部署</li>
              <li><i class="fa fa-arrow-circle-o-right"></i> 过程数据跟踪分析</li>
              <li><i class="fa fa-arrow-circle-o-right"></i> 静态分析持续集成</li>
          </ul>
          <div class="mb20"></div>
          <strong>CITS不仅是一款任务管理工具，更是您工作改进的好帮手~</strong>
        </div><!-- signin0-info -->
      </div><!-- col-sm-7 -->
      <div class="col-md-5">
      <form method="post" action="/admin/login">
        <h4 class="nomargin">登录</h4>
        <p class="mt5 mb20">没有帐号，请移步 <a href="/signup">注册</a></p>
        <input name="username" id="username" type="text" class="form-control uname" placeholder="用户名" />
        <input name="password" id="password" type="password" class="form-control pword" placeholder="密码" />
        <button name="button" id="button" type="button" class="btn btn-success btn-block">登入</button>
      </form>
      </div><!-- col-sm-5 -->
    </div><!-- row -->
    <div class="signup-footer">
        <div class="pull-left">
            &copy; 2016. All Rights Reserved.
        </div>
        <div class="pull-right">
            有问题请联系: <a href="mailto:webmaster@jiangbianwanghai.com" target="_blank">江边望海</a>
        </div>
    </div>
  </div><!-- signin -->
</section>
<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script type="text/javascript">

  //
  function login() {
    $.ajax({
      type: "POST",
      url: "/signin/check",
      data:{'username':$("#username").val(), 'password':$("#password").val()},
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
          }, 500);
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
      login();
    });

    //回车键触发
    $('input:text:first').focus();
    var $inp = $('input');
    $inp.keypress(function (e) {
      var key = e.which; //e.which是按键的值 
      if (key == 13) { 
        login();
      } 
    }); 

  });
</script>

</body>
</html>