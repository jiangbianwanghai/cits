<div class="rightpanel">
  <!-- Nav tabs -->
  <ul class="nav nav-tabs nav-justified">
    <li class="active"><a href="#rp-alluser" data-toggle="tab"><i class="fa fa-users"></i></a></li>
    <li><a href="#rp-favorites" data-toggle="tab"><i class="fa fa-heart"></i></a></li>
    <li><a href="#rp-history" data-toggle="tab"><i class="fa fa-clock-o"></i></a></li>
    <li><a href="#rp-settings" data-toggle="tab"><i class="fa fa-gear"></i></a></li>
  </ul>
  <!-- Tab panes -->
  <div class="tab-content">
    <div class="tab-pane active" id="rp-alluser">
      <h5 class="sidebartitle">在线用户</h5>
      <ul class="chatuserlist">
        <?php
        foreach ($online_users as $key => $value) {
        ?>
        <li class="online">
          <div class="media">
            <a href="#" class="pull-left media-thumb"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['uid']]['username'].'.jpg'; ?>" class="media-object"></a>
            <div class="media-body">
              <strong><?php echo $users[$value['uid']]['realname']; ?></strong>
              <small><?php echo $users[$value['uid']]['username']; ?></small>
            </div>
          </div><!-- media -->
        </li>
        <?php } ?>
      </ul>
    </div>
  </div><!-- tab-content -->
</div><!-- rightpanel -->