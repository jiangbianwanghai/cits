<?php
//载入项目缓存文件
$project = array();
if (file_exists(APPPATH.'/cache/project.cache.php')) {
  $project = file_get_contents(APPPATH.'/cache/project.cache.php');
  $project = unserialize($project);
}
//载入用户缓存文件
$users = array();
if (file_exists(APPPATH.'/cache/users.conf.php')) {
  require APPPATH.'cache/users.conf.php';
}
?>
<div class="headerbar">
  <a class="menutoggle"><i class="fa fa-bars"></i></a>
  <div class="topnav">
    <ul class="nav nav-horizontal">
      <li class="active"><a href="/"><i class="fa fa-home"></i> <span>我的面板</span></a></li>
      <li class="nav-parent"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-list"></i> <span id="curr-project">
        <?php
        if ($this->input->cookie('projectId')&& isset($project[$this->input->cookie('projectId')])) {
          echo $project[$this->input->cookie('projectId')]['project_name']; 
        } else { echo '请选择项目团队'; }
        ?></span> <span class="caret"></span></a>
        <ul class="dropdown-menu children">
          <?php
          if ($project) {
            foreach ($project as $key => $value) {
              echo "<li><a href=\"javascript:;\" class=\"set-project\" md5=\"".$value['md5']."\" project=\"".$value['project_name']."\">".$value['project_name']."</a></li>";
            }
            echo "<li class=\"divider\"></li>";
          }
          ?>
          <li><a href="javascript:;" data-toggle="modal" data-target="#myModal-project">添加项目团队</a></li>
        </ul>
      </li>
      <li>
        <?php
        $weekarray=array("日","一","二","三","四","五","六");
        echo "<a href=\"javascript:;\">今天是：".date("Y-m-d", time())." 星期".$weekarray[date("w",time())]." （".date("Y", time())."年的第 ".intval(date("W", time()))." 周）</a>";
        ?>
      </li>
    </ul>
  </div><!-- topnav -->
  <div class="header-right">
    <ul class="headermenu">
      <li>
        <div class="btn-group">
          <button class="btn btn-default dropdown-toggle tp-icon" data-toggle="dropdown">
            <i class="glyphicon glyphicon-globe"></i>
            <span class="badge">5</span>
          </button>
          <div class="dropdown-menu dropdown-menu-head pull-right">
            <h5 class="title">你有5个新提醒</h5>
            <ul class="dropdown-list gen-list">
              <li class="new">
                <a href="">
                <span class="thumb"><img src="<?php echo AVATAR_HOST; ?>/changxiaoyang.jpg" alt="常晓阳" /></span>
                <span class="desc">
                  <span class="name">李研发 <span class="badge badge-success">未读</span></span>
                  <span class="msg">创建了一个计划（planId#123）</span>
                </span>
                </a>
              </li>
              <li class="new"><a href="">查看所有通知</a></li>
            </ul>
          </div>
        </div>
      </li>
      <li>
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <img src="<?php echo AVATAR_HOST.'/'.USER_NAME.'.jpg'; ?>" alt="<?php echo REAL_NAME; ?>" />
            <?php echo REAL_NAME; ?>
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
            <li><a href="/dashboard/logout"><i class="glyphicon glyphicon-log-out"></i> 退出</a></li>
          </ul>
        </div>
      </li>
      <li>
        <button id="chatview" class="btn btn-default tp-icon chat-icon">
            <i class="glyphicon glyphicon-comment"></i>
        </button>
      </li>
    </ul>
  </div><!-- header-right -->
</div><!-- headerbar -->
<!-- Modal -->
<div class="modal fade" id="myModal-project" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <form method="POST" id="addProject" action="/project/add_ajax" class="form-horizontal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">创建项目团队</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="col-sm-3 control-label">名称 <span class="asterisk">*</span></label>
          <div class="col-sm-9">
            <input type="text" name="project_name" id="project_name" class="form-control project_name" required />
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-3 control-label">简介 <span class="asterisk">*</span></label>
          <div class="col-sm-9">
            <textarea rows="5" class="form-control" id="project_description" name="project_description" required></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button class="btn btn-primary" id="btnSubmit-project">提交</button>
      </div>
    </div><!-- modal-content -->
  </div><!-- modal-dialog -->
  </form>
</div><!-- modal -->
