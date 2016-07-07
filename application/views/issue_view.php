<?php include('common_top.php');?>
<body class="leftpanel-collapsed">
<section>
  <?php include('common_leftpanel.php');?>
  <div class="mainpanel">
    <?php include('common_headerbar.php');?>
    <?php
    //载入代码库缓存文件
    $repos = array();
    if (file_exists(APPPATH.'cache/repos.cache.php')) {
      $repos = unserialize(file_get_contents(APPPATH.'cache/repos.cache.php'));
    }
    ?>
    <div class="pageheader">
      <h2><i class="fa fa-pencil"></i> 任务管理 <span>任务详情</span></h2>
      <div class="breadcrumb-wrapper">
        <span class="label">你的位置:</span>
        <ol class="breadcrumb">
          <li><a href="/">CITS</a></li>
          <li><a href="/issue">任务管理</a></li>
          <li class="active">任务详情</li>
        </ol>
      </div>
    </div><!-- pageheader -->
    <div class="contentpanel">
      <div class="row">
        <div class="col-sm-3 col-lg-2">
          <ul class="nav nav-pills nav-stacked nav-email">
            <li><a href="/issue"><i class="glyphicon glyphicon-folder-close"></i> 任务列表</a></li>
            <li><a href="/issue/to_me"><i class="glyphicon glyphicon-folder-close"></i> 我负责的</a></li>
            <li><a href="/issue/from_me"><i class="glyphicon glyphicon-folder-close"></i> 我创建的</a></li>
          </ul>
        </div><!-- col-sm-3 -->
        <div class="col-sm-9 col-lg-10">
      <?php if ($issue_profile['status'] == '-1') { ?>
      <div class="alert alert-warning">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong>抱歉~</strong> 该任务已被删除.
      </div>
      <?php } ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <div class="pull-right">
            <div class="btn-group mr10">
                <?php if ($issue_profile['status'] == 1) { ?>
                <a href="/issue/edit/<?php echo $issueid;?>" class="btn btn-sm btn-white"><i class="fa fa-pencil mr5"></i> 编辑</a>
                <a href="javascript:;" id="del" reposid="<?php echo $issueid;?>" class="btn btn-sm btn-white"><i class="fa fa-trash-o mr5"></i> 删除</a>
                <?php } ?>
            </div>
          </div>
          
          <div class="panel-title"><?php if ($issue_profile['type'] == 2) {?><i class="fa fa-bug tooltips" data-toggle="tooltip" title="BUG"></i><?php } ?><?php if ($issue_profile['type'] == 1) {?><i class="fa fa-magic tooltips" data-toggle="tooltip" title="TASK"></i><?php } ?> <?php if ($issue_profile['level']) { ?><?php echo "<strong style='color:#ff0000;'>".$level[$issue_profile['level']]['name']."</strong> ";?><?php } ?> <?php if ($issue_profile['status'] == '-1') { ?><s><?php echo $issue_profile['issue_name'];?></s><?php } else { ?><?php echo $issue_profile['issue_name'];?><?php } ?> <?php if ($issue_profile['resolve']) { ?> <span class="label label-success">已解决</span><?php }?> <?php if ($issue_profile['status'] == 0) {?> <span class="label label-default">已关闭</span><?php }?></div>
          <small>创建人：<?php echo $users[$issue_profile['add_user']]['realname'];?> 创建时间：<?php echo date("Y-m-d H:i:s", $issue_profile['add_time']); ?></small>
        </div>
        <div class="panel-body">
          <h5 class="subtitle subtitle-lined">进度</h5>
          <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <tbody>
              <tr>
                <td>
                  <?php if ($accept_user && isset($accept_user['1'])) { ?>
                  <span class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$accept_user['1']['accept_user']]['username'];?>.jpg" align="absmiddle" title=""></span> <a href="/conf/profile/<?php echo $accept_user['1']['accept_user'];?>" target="_blank"><?php echo $users[$accept_user['1']['accept_user']]['realname'];?></a>
                  <?php } else { echo 'N/A'; } ?>
                </td>
                <td colspan="<?php if ($bug['total']) {echo 4;}else{echo 2;}?>">
                  <?php if ($accept_user && isset($accept_user['2'])) { ?>
                  <span class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$accept_user['2']['accept_user']]['username'];?>.jpg" align="absmiddle" title=""></span> <a href="/conf/profile/<?php echo $accept_user['2']['accept_user'];?>" target="_blank"><?php echo $users[$accept_user['2']['accept_user']]['realname'];?></a>
                  <?php } else { echo 'N/A'; } ?>
                </td>
                <td colspan="2">
                  <?php if ($accept_user && isset($accept_user['3'])) { ?>
                  <span class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$accept_user['3']['accept_user']]['username'];?>.jpg" align="absmiddle" title=""></span> <a href="/conf/profile/<?php echo $accept_user['3']['accept_user'];?>" target="_blank"><?php echo $users[$accept_user['3']['accept_user']]['realname'];?></a>
                  <?php } else { echo 'N/A'; } ?>
                </td>
                <td>
                  <?php if ($accept_user && isset($accept_user['4'])) { ?>
                  <span class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$accept_user['4']['accept_user']]['username'];?>.jpg" align="absmiddle" title=""></span> <a href="/conf/profile/<?php echo $accept_user['4']['accept_user'];?>" target="_blank"><?php echo $users[$accept_user['4']['accept_user']]['realname'];?></a>
                  <?php } else { echo 'N/A'; } ?>
                </td>
              </tr>
              <tr>
                <td class="blue" width="150px">新建</td>
                <!-- #开发-我要开发# -->
                <?php if ($issue_profile['workflow'] >= 1) {?>
                <td class="blue">开发中</td>
                <?php } elseif ($issue_profile['accept_user'] == UID) {?>
                <td style="text-align:center;" id="td-dev"><a href="javascript:;" ids="<?php echo $issueid; ?>" class="label label-danger dev">我要开发</a></td>
                <?php } else { ?>
                <td style="text-align:center;">开发中</td>
                <?php }?>
                <!-- #开发-开发完毕# -->
                <?php if ($issue_profile['workflow'] >= 2) {?>
                <td class="blue">开发完毕</td>
                <?php } else {?>
                <?php if ($issue_profile['workflow']  == 1 && $issue_profile['accept_user'] == UID) {?>
                <td style="text-align:center;" width="200px" id="td-over">
                  <a href="/commit/add/<?php echo $issueid;?>" class="label label-danger">提交代码</a> 
                  <a href="javascript:;" ids="<?php echo $issueid; ?>" class="label label-primary over">开发完毕</a>
                </td>
                <?php } else {?>
                <td style="text-align:center;">开发完毕</td>
                <?php } ?>
                <?php } ?>
                <!-- #开发-修复中# -->
                <?php if ($bug['total']) {?>
                <?php if ($issue_profile['workflow'] >= 3) {?>
                <td class="blue">修复中</td>
                <?php } else {?>
                <td style="text-align:center;">修复中</td>
                <?php } ?>

                <?php if ($issue_profile['workflow'] >= 4) {?>
                <td class="blue">修复完毕</td>
                <?php } else {?>
                <?php if ($issue_profile['workflow'] == 3 && $accept_user && isset($accept_user['2']) && $accept_user['2']['accept_user'] == UID) { ?>
                <td style="text-align:center;" width="200px" id="td-fix">
                  <a href="/commit/add/<?php echo $issueid;?>" class="label label-danger">提交代码</a> 
                  <a href="javascript:;" ids="<?php echo $issueid; ?>" class="label label-primary fix">修复完毕</a>
                </td>
                <?php }else { ?>
                <td style="text-align:center;">修复完毕</td>
                <?php } ?>
                <?php } ?>
                <?php } ?>

                <!-- #测试-测试中# -->
                <?php if ($issue_profile['workflow'] >= 5) {?>
                <td class="blue">测试中</td>
                <?php } else {?>
                <?php if (($issue_profile['workflow'] == 2 || $issue_profile['workflow'] == 4)&& $accept_user && isset($accept_user['3']) && $accept_user['3']['accept_user'] == UID) {?>
                <td style="text-align:center;" id="td-test"><a href="javascript:;" ids="<?php echo $issueid; ?>" class="label label-danger test">我要测试</a></td>
                <?php } elseif ($issue_profile['workflow'] == 2 && $accept_user && !isset($accept_user['3']) && $accept_user['2']['accept_user'] == UID) {?>
                <td style="text-align:center;"><a href="javascript:;" id="test_user" data-type="select2" data-value="0" data-title="指定受理人"></a></td>
                <?php } else {?>
                <td style="text-align:center;">测试中</td>
                <?php } ?>
                <?php } ?>

                <!-- #测试-测试通过# -->
                <?php if ($issue_profile['workflow'] >= 6) { ?>
                  <td class="blue">测试通过</td>
                <?php } else { ?>
                  <?php if (($issue_profile['workflow'] >=3 && $issue_profile['workflow'] <= 5) && $accept_user && isset($accept_user['3']) && $accept_user['3']['accept_user'] == UID) {?>
                  <td style="text-align:center;" width="200px" id="td-wait">
                    <a href="/bug/add/<?php echo $issueid;?>" class="label label-danger">反馈BUG</a> 
                    <a href="javascript:;" ids="<?php echo $issueid; ?>" class="label label-primary waits">测试通过</a>
                  </td>
                  <?php } else {?>
                  <td style="text-align:center;">测试通过</td>
                  <?php } ?>
                <?php } ?>

                <!-- #上线# -->
                <?php if ($issue_profile['workflow'] == 7) { ?>
                <td class="blue">已上线</td>
                <?php } else { ?>
                <?php if ($issue_profile['workflow'] == 6 && $accept_user && isset($accept_user['4']) && $accept_user['4']['accept_user'] == UID) {?>
                <td style="text-align:center;" id="td-online"><a href="javascript:;" ids="<?php echo $issueid; ?>" class="label label-danger onlines">通知上线</a></td>
                <?php } elseif ($issue_profile['workflow'] == 6 && $accept_user && !isset($accept_user['4']) && $accept_user['3']['accept_user'] == UID) {?>
                <td style="text-align:center;"><a href="javascript:;" id="test_user" data-type="select2" data-value="0" data-title="指定受理人"></a></td>
                <?php } else {?>
                <td style="text-align:center;">上线</td>
                <?php } ?>
                <?php } ?>

              </tr>
            </tbody>
          </table>
          </div><!-- table-responsive -->
          <br />
          <h5 class="subtitle subtitle-lined">描述</h5>
          <p><?php echo $issue_profile['issue_summary'];?></p>
          <br />
          <div align="right">
          <?php if (($issue_profile['workflow'] == 1 || $issue_profile['workflow'] == 3) && isset($accept_user['2']) && $accept_user['2']['accept_user'] != UID) { ?>
          <a href="/commit/add/<?php echo $issueid;?>" class="label label-danger">其他人可以点击此处提交代码</a>
          <?php } ?>
          <?php if (($issue_profile['workflow'] >=3 && $issue_profile['workflow'] <= 5) && isset($accept_user['3']) && $accept_user['3']['accept_user'] != UID) { ?>
           <a href="/bug/add/<?php echo $issueid;?>" class="label label-danger">其他人可以点击此处反馈BUG</a>
          <?php } ?>
          </div>
          <h5 class="subtitle subtitle-lined">开发信息 <span class="badge badge-info"><?php echo $commit['total'];?></span></h5>
          <div class="table-responsive mb30">
            <table class="table table-email">
              <tbody>
                <?php
                  if ($commit['data']) {
                    foreach ($commit['data'] as $value) {
                      if (!isset($timeGroup[$value['repos_id']])) {
                        $timeGroup[$value['repos_id']] = 1;
                        echo '<tr><td colspan="8"><span class="fa fa-cloud-upload"></span> '.$repos[$value['repos_id']]['repos_name'].'</td></tr>';
                      }
                ?>
                <tr id="tr-<?php echo $value['id'];?>" class="unread">
                  <td><a href="/conf/profile/<?php echo $value['add_user'];?>" class="pull-left"><div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['add_user']]['username']?>.jpg" align="absmiddle" title="添加人：<?php echo $users[$value['add_user']]['realname'];?>"></div></a></td>
                  <td></td>
                  <td><?php if ($value['status'] == '-1') { echo '<s><a title="'.$repos[$value['repos_id']]['repos_url'].'" href="/test/repos/'.$value['repos_id'].'">'.$repos[$value['repos_id']]['repos_name'].'</a></s>'; } else { echo '<a title="'.$repos[$value['repos_id']]['repos_url'].'" href="/test/repos/'.$value['repos_id'].'">'.$repos[$value['repos_id']]['repos_name'].'</a>'; }?> @<?php echo $value['test_flag'];?><?php if ($timeGroup[$value['repos_id']] == 1) { echo ' <span class="badge badge-danger">当前</span>'; } ?>
                  </td>
                  <td width="120">
                    <?php if ($value['rank'] == 0) {?>
                    <button class="btn btn-default btn-xs"><i class="fa fa-coffee"></i> 开发环境</button>
                    <?php } ?>
                    <?php if ($value['rank'] == 1) {?>
                    <button class="btn btn-primary btn-xs tooltips" data-toggle="tooltip" title="<?php echo $env[$value['env']]['ip'];?>"><?php if ($value['state'] == 5) { ?><i class="fa fa-exclamation-circle"></i> <s><?php echo $env[$value['env']]['name'];?></s><?php } else {?><i class="fa fa-check-circle"></i> <?php echo $env[$value['env']]['name'];?><?php } ?></button>
                    <?php } ?>
                    <?php if ($value['rank'] == 2) {?>
                    <button class="btn btn-success btn-xs"><i class="fa fa-check-circle"></i> 生产环境</button>
                    <?php } ?>
                  </td>
                  <td width="90">
                    <?php if ($value['state'] == 0) {?>
                    <button class="btn btn-default btn-xs"><i class="fa fa-coffee"></i> 待测</button>
                    <?php } ?>
                    <?php if ($value['state'] == 1) {?>
                    <button class="btn btn-primary btn-xs"><i class="fa fa-clock-o"></i> 测试中…</button>
                    <?php } ?>
                    <?php if ($value['state'] == -3) {?>
                    <button class="btn btn-danger btn-xs"><i class="fa fa-exclamation-circle"></i> 不通过</button>
                    <?php } ?>
                    <?php if ($value['state'] == 3) {?>
                    <button class="btn btn-success btn-xs"><i class="fa fa-check-circle"></i> 通过</button>
                    <?php } ?>
                    <?php if ($value['state'] == 5) {?>
                    <button class="btn btn-success btn-xs"><i class="fa fa-exclamation-circle"></i> 已被后续版本覆盖</button>
                    <?php } ?>
                  </td>
                  <td width="240">
                    <?php if ($value['status'] == 1) {?>
                    <div class="btn-group nomargin">
                      <?php if ($value['rank'] == 0) { ?>
                      <div class="btn-group nomargin">
                        <button type="button" class="btn btn-white btn-sm dropdown-toggle" data-toggle="dropdown">
                          占用测试环境 <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                          <?php
                          foreach ($env as $k => $v) {
                            echo '<li><a href="javascript:;" class="zhanyong" testid="'.$value['id'].'" data-value="'.$k.'">'.$v['name'].'('.$v['ip'].')</a></li>';
                          }
                          ?>
                        </ul>
                      </div>
                      <?php } ?>
                      <?php if ($value['rank'] == 1) { ?>
                      <div class="btn-group nomargin">
                        <button type="button" class="btn btn-white btn-sm dropdown-toggle" data-toggle="dropdown">
                          更改提测状态 <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                          <li><a href="javascript:;" class="wait" testid="<?php echo $value['id']?>">我暂时不测了</a></li>
                          <li><a href="javascript:;" class="pass" testid="<?php echo $value['id']?>">测试不通过</a></li>
                          <li><a href="javascript:;" class="launch" testid="<?php echo $value['id']?>">测试通过待上线</a></li>
                          <li><a href="javascript:;" class="online" testid="<?php echo $value['id']?>">代码已上线</a></li>
                        </ul>
                      </div>
                      <?php } ?>
                      <?php if ($value['rank'] < 2) { ?>
                      <div class="btn-group nomargin">
                        <button type="button" class="btn btn-white btn-sm dropdown-toggle" data-toggle="dropdown">
                          获取部署代码 <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                          <li><a href="javascript:;" class="deploy" env="192.168.8.190" br="<?php echo str_replace('branches/', '', $value['br']);?>" rev="<?php echo $value['test_flag'];?>" repos="<?php echo $repos[$value['repos_id']]['repos_name'];?>" merge="<?php echo $repos[$value['repos_id']]['merge']?>" ids="<?php echo $value['id']?>">190（测试环境01）</a></li>
                          <li><a href="javascript:;" class="deploy" env="192.168.8.192" br="<?php echo str_replace('branches/', '', $value['br']);?>" rev="<?php echo $value['test_flag'];?>" repos="<?php echo $repos[$value['repos_id']]['repos_name'];?>" merge="<?php echo $repos[$value['repos_id']]['merge']?>" ids="<?php echo $value['id']?>">192（测试环境02）</a></li>
                          <li><a href="javascript:;" class="deploy" env="192.168.8.193" br="<?php echo str_replace('branches/', '', $value['br']);?>" rev="<?php echo $value['test_flag'];?>" repos="<?php echo $repos[$value['repos_id']]['repos_name'];?>" merge="<?php echo $repos[$value['repos_id']]['merge']?>" ids="<?php echo $value['id']?>">193（测试环境03）</a></li>
                        </ul>
                      </div>
                      <?php } ?>
                    </div>
                    </div>
                    <?php }?>
                  </td>
                  <td width="150">
                    <?php if ($issue_profile['status'] == 1) {?>
                    <?php if ($value['tice'] < 1) {?>
                    <a class="btn btn-white btn-xs" href="/test/edit/<?php echo $issueid;?>/<?php echo $value['id'];?>"><i class="fa fa-pencil"></i> 编辑</a>
                    <a class="btn btn-white btn-xs delete-row" href="javascript:;" issueid="<?php echo $issueid;?>" testid="<?php echo $value['id'];?>"><i class="fa fa-trash-o"></i> 删除</a>
                    <?php }?>
                    <?php }?> 
                  </td>
                  <td width="140"><span class="media-meta pull-right"><?php echo date("Y/m/d H:i", $value['add_time'])?></span></td>
                </tr>
                <tr id="abc-<?php echo $value['id'];?>" style="display:none;">
                  <td colspan="8" id="deploy-<?php echo $value['id'];?>" curr="0"><input type="text" class="form-control input-sm">
                  <small>提醒：可以使用快捷键 <code>Ctrl+C</code> 将部署代码复制到剪切板上</small></td>
                </tr>
                <?php if ($value['test_summary']) { ?>
                <tr><td colspan="8" style="background-color:#fff"><div style="padding:10px;line-height:1.2em"><blockquote style="font-size:14px;"><i class="fa fa-quote-left"></i><p><?php echo $value['test_summary']; ?></p><small><?php echo $users[$value['add_user']]['realname']; ?>：提测 <?php echo $repos[$value['repos_id']]['repos_name']; ?> 的注意事项</cite></small></blockquote></div></td></tr>
                <?php } ?>
                <?php
                    $timeGroup[$value['repos_id']]++;
                    }
                  } else {
                ?>
                <tr><td align="center">无提测信息</td></tr>
                <?php } ?>
              </tbody>
            </table>
            </div><!-- table-responsive -->
          <h5 class="subtitle subtitle-lined">信息</h5>
          <div class="table-responsive">
          <table class="table table-striped mb30">
            <tbody>
              <tr>
                <td width="100px">创建人</td>
                <td><?php echo $issue_profile['add_user'] ? '<a href="/conf/profile/'.$issue_profile['add_user'].'">'.$users[$issue_profile['add_user']]['realname'].'</a>' : '-';?></td>
                <td width="100px">创建时间</td>
                <td><?php echo $issue_profile['add_time'] ? date("Y/m/d H:i:s", $issue_profile['add_time']) : '-';?></td>
              </tr>
              <tr>
                <td width="100px">当前受理人</td>
                <td><a href="javascript:;" id="country" data-type="select2" data-value="<?php echo alphaid($issue_profile['accept_user']);?>" data-title="更改受理人"></a></td>
                <td width="100px">受理时间</td>
                <td><?php echo $issue_profile['last_time'] ? date("Y/m/d H:i:s", $issue_profile['last_time']) : '-';?></td>
              </tr>
              <tr>
                <td width="100px">最后修改人</td>
                <td><?php echo $issue_profile['last_user'] ? '<a href="/conf/profile/'.$issue_profile['last_user'].'">'.$users[$issue_profile['last_user']]['realname'].'</a>' : '-';?></td>
                <td width="120px">最后修改时间</td>
                <td><?php echo $issue_profile['last_time'] ? date("Y/m/d H:i:s", $issue_profile['last_time']) : '-';?></td>
              </tr>
              <tr>
                <td width="100px">所属计划</td>
                <td><?php if ($plan_profile) { echo '<a href="/plan?planId='.urlencode($this->encryption->encrypt($plan_profile['id'])).'" target="_blank">'.$plan_profile['plan_name'].'</a>'; }?></td>
                <td width="120px">贡献者</td>
                <td>
                  <?php
                  if ($accept_user) {
                    foreach ($accept_user as $key => $value) {
                      $acceptUsersDup[] = $value['accept_user'];
                    }
                    $acceptUsersDup = array_unique($acceptUsersDup);
                    foreach ($acceptUsersDup as $key => $value) {
                      echo ' <a href="/conf/profile/'.$value.'">'.$users[$value]['realname'].'</a>';
                    }
                  }
                  ?>
                </td>
              </tr>
              <tr>
                <td width="100px">相关链接</td>
                <td>
                  <?php
                  if ($issue_profile['url']) {
                    if (strrpos($issue_profile['url'], '{')) {
                     $url = unserialize($issue_profile['url']);
                      foreach ($url as $key => $value) {
                        echo "<a href=\"".$value."\" target=\"_blank\">链接".($key+1)."</a> ";
                      }
                    } else {
                      echo "<a href=\"".$issue_profile['url']."\" target=\"_blank\">链接</a>";
                    }
                  }
                  ?>
                </td>
                <td width="120px">提测成功率</td>
                <td><span class="label label-info"><?php echo $rate; ?></span> <i class="glyphicon glyphicon-question-sign tooltips" title="提测成功率越低，代表质量越差。"></i></td>
              </tr>
            </tbody>
          </table>
          </div><!-- table-responsive -->
          <?php if ($bug['total']) {?>
          <h5 class="subtitle subtitle-lined">发现的BUG  <span class="badge badge-info"><?php echo $bug['total'];?></span></h5>
          <div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <?php
                  if ($bug['data']) {
                    foreach ($bug['data'] as $value) {
                ?>
                  <tr>
                    <td width="50px">
                      <?php
                      if ($value['status'] == 1) {
                        echo '<span class="label label-info">开启</span>';
                      } elseif ($value['status'] == 0) {
                        echo '<span class="label label-default">关闭</span>';
                      }elseif ($value['status'] == '-1') {
                        echo '<span class="label label-default">删除</span>';
                      }
                      ?>
                    </td>
                    <td width="30px"><i class="fa fa-bug tooltips" data-toggle="tooltip" title="Bug"></i></td>
                    <td width="50px">
                      <a href="/conf/profile/<?php echo $value['add_user'];?>" class="pull-left" target="_blank">
                        <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['add_user']]['username']?>.jpg" align="absmiddle" title="反馈人：<?php echo $users[$value['add_user']]['realname'];?>"></div>
                      </a>
                    </td>
                    <td width="50px">
                      <a href="/conf/profile/<?php echo $value['accept_user'];?>" class="pull-left" target="_blank">
                        <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['accept_user']]['username']?>.jpg" align="absmiddle" title="受理人：<?php echo $users[$value['accept_user']]['realname'];?>"></div>
                      </a>
                    </td>
                    <td><?php if ($value['level']) { ?><?php echo "<strong style='color:#ff0000;'>".$level[$value['level']]['name']."</strong> ";?><?php } ?><a href="/bug/view/<?php echo alphaid($value['id']);?>"><?php echo $value['subject']?></a></td>
                    
                    <td width="80px">
                      <?php if ($value['state'] === '0') {?>
                      <span class="label label-default">未确认</span>
                      <?php } ?>
                      <?php if ($value['state'] === '1') {?>
                      <span class="label label-primary">已确认</span>
                      <?php } ?>
                      <?php if ($value['state'] === '2') {?>
                      <span class="label label-primary">已确认</span>
                      <?php } ?>
                      <?php if ($value['state'] === '3') {?>
                      <span class="label label-info">已处理</span>
                      <?php } ?>
                      <?php if ($value['state'] === '5') {?>
                      <span class="label label-success">通过回归</span>
                      <?php } ?>
                      <?php if ($value['state'] === '-1') {?>
                      <span class="label label-default">无效反馈</span>
                      <?php } ?>
                    </td>
                  </tr>
                  <?php
                      }
                    } else {
                  ?>
                  <tr><td colspan="6" align="center">无提测信息</td></tr>
                  <?php } ?>
              </tbody>
            </table>
          </div><!-- table-responsive -->
          <?php } ?>
        </div>
      </div>
                    
      <ul class="nav nav-tabs nav-default">
        <li class="active"><a data-toggle="tab" href="#all"><strong>评论</strong></a></li>
        <li><a data-toggle="tab" href="#added" id="log-list" data-id="<?php echo $issueid; ?>"><strong>操作日志</strong></a></li>
      </ul>
      <div class="tab-content">
        <div id="all" class="tab-pane active"> 
          <div class="panel">
            <div class="panel-body">
              <?php
                if ($comment['data']) {
                  foreach ($comment['data'] as $value) {
              ?>
              <div class="media">
                <div class="pull-left">
                  <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[$value['add_user']]['username']?>.jpg" align="absmiddle" title="<?php echo $users[$value['add_user']]['realname'];?>"></div>
                </div>
                <div class="media-body">
                  <span class="media-meta pull-right"><?php echo timediff($value['add_time'], time());?><?php if ($value['add_user'] == UID) {?><br /><a class="del" ids="<?php echo alphaid($value['id']);?>" href="javascript:;">删除</a><?php } ?></span>
                  <h6 class="text-muted"><?php echo $users[$value['add_user']]['realname'];?></h6>
                  <small class="text-muted"><?php if ($value['add_user'] == $issue_profile['accept_user']) { echo '当前受理人'; } else { echo '路人甲'; }?></small>
                  <div id="comment-<?php echo alphaid($value['id']);?>"><?php echo html_entity_decode($value['content']);?></div>
                </div>
              </div>
              <?php
                  }
                }
              ?>
              <div id="box"></div>
              <div class="media">
                <div class="pull-left">
                  <div class="face"><img alt="" src="<?php echo AVATAR_HOST.'/'.$users[UID]['username']?>.jpg" align="absmiddle" title="<?php echo $users[UID]['realname'];?>"></div>
                </div>
                <div class="media-body">
                  <input type="text" class="form-control" id="post-commit" placeholder="我要发表评论">
                  <div id="simditor" style="display:none;">
                    <textarea id="content" name="content"></textarea>
                    <div class="mb10"></div>
                    <input type="hidden" value="<?php echo $issueid;?>" id="issue_id" name="issue_id">
                    <button class="btn btn-primary" id="btnSubmit">提交</button>
                  </div>
                </div>
              </div>
            </div><!-- row -->  
          </div><!-- panel-body -->     
        </div><!-- tab-pane -->
          
        <div id="added" class="tab-pane">
            <div align="center"><img src="<?php echo STATIC_HOST; ?>/images/loaders/loader19.gif" /></div>
        </div><!-- tab-pane -->
      </div><!-- tab-content -->
    </div><!-- panel -->
  </div>
  <p class="text-right"><small>页面执行时间 <em>{elapsed_time}</em> 秒 使用内存 {memory_usage}</small></p>  
</div><!-- contentpanel -->
</div><!-- mainpanel -->
  
</section>

<div class="modal fade bs-example-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
            <h4 class="modal-title">提测详情</h4>
        </div>
        <div class="modal-body"><div class="modal-body-inner"></div></div>
    </div>
  </div>
</div>

<?php include('common_js.php');?>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/module.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/uploader.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/hotkeys.js"></script>
<script src="<?php echo STATIC_HOST; ?>/simditor-2.3.6/scripts/simditor.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/jquery.datatables.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/simple-pinyin.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/select2.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/bootstrap-editable.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/bootstrap-datetimepicker.min.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/moment.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/jquery.countdown.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/custom.js"></script>
<script src="<?php echo STATIC_HOST; ?>/js/cits.js"></script>
<script>
  function changeIssueStatus(obj1,obj2,obj3) {
    $(obj1).click(function(){
      var c = confirm(obj3);
      if(c) {
        id = $(this).attr("reposid");
        $.ajax({
          type: "GET",
          url: "/issue/"+obj2+"/"+id,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-success',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
              setTimeout(function(){
                location.href = data.url;
              }, 2000);
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-danger',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
            };
          }
        });
      }
    });
  }

  function changeTestStatus(obj1,obj2,obj3) {
    $(obj1).click(function(){
      var c = confirm(obj3);
      if(c) {
        id = $(this).attr("testid");
        $.ajax({
          type: "GET",
          url: "/test/"+obj2+"/"+id,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-success',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
              setTimeout(function(){
                location.href = data.url;
              }, 1000);
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.error,
                  class_name: 'growl-danger',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
            };
          }
        });
      }
    });
  }

  $(document).ready(function(){

    $('#post-commit').click(function () {
      $(this).hide();
      $('#simditor').show();
    });

    $('#deadline').countdown('<?php echo date("Y-m-d H:i", $issue_profile['deadline']);?>', function(event) {
      $(this).html(event.strftime('%D days %H:%M:%S'));
    });

    $("#del").click(
      changeIssueStatus('#del','del','确认要删除吗？')
    );
    $("#close").click(
      changeIssueStatus('#close','close','确认要关闭吗？')
    );
    $("#resolve").click(
      changeIssueStatus('#resolve','resolve','确认验证通过并告知任务添加人吗？')
    );
    $(".zhanyong").click(function(){
      var c = confirm('确认要占用这个环境吗？');
      if(c) {
        id = $(this).attr("testid");
        env = $(this).attr('data-value');
        $.ajax({
          type: "GET",
          url: "/test/env?testId="+id+"&envId="+env,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-success',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
              setTimeout(function(){
                location.href = "/issue/view/<?php echo $issueid;?>";
              }, 1000);
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.error,
                  class_name: 'growl-danger',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
            };
          }
        });
      }
    });
    $(".online").click(function(){
      var c = confirm('确认改为上线状态吗？');
      if(c) {
        id = $(this).attr("testid");
        $.ajax({
          type: "GET",
          url: "/test/change_tice/"+id+"/online",
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-success',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
              setTimeout(function(){
                location.href = data.url;
              }, 2000);
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-danger',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
            };
          }
        });
      }
    });
    $(".wait").click(function(){
      var c = confirm('你确定不测了，将测试环境让给他人吗？');
      if(c) {
        id = $(this).attr("testid");
        $.ajax({
          type: "GET",
          url: "/test/change_tice/"+id+"/wait",
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-success',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
              setTimeout(function(){
                location.href = data.url;
              }, 2000);
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-danger',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
            };
          }
        });
      }
    });
    $(".pass").click(function(){
      var c = confirm('你确定将状态改为不通过？');
      if(c) {
        id = $(this).attr("testid");
        $.ajax({
          type: "GET",
          url: "/test/change_tice/"+id+"/pass",
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-success',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
              setTimeout(function(){
                location.href = data.url;
              }, 2000);
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-danger',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
            };
          }
        });
      }
    });

    $(".launch").click(function(){
      var c = confirm('你确定要更改成测试通过待上线吗？');
      if(c) {
        id = $(this).attr("testid");
        $.ajax({
          type: "GET",
          url: "/test/change_tice/"+id+"/launch",
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
                location.href = data.url;
              }, 2000);
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

    $(".panel-edit").click(function(){
      var c = confirm('你确定要打开已经关闭的任务吗？');
      if(c) {
        id = $(this).attr("reposid");
        $.ajax({
          type: "GET",
          url: "/issue/open/"+id,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-success',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
              setTimeout(function(){
                location.href = data.url;
              }, 2000);
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-danger',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
            };
          }
        });
      }
    });

    $(".tice").click(function(){
      $(this).attr("disabled", true);
      id = $(this).attr("testid");
      $.ajax({
        type: "GET",
        url: "/test/tice/"+id,
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
              location.href = data.url;
            }, 2000);
          } else {
            jQuery.gritter.add({
              title: '提醒',
              text: data.message,
                class_name: 'growl-danger',
              sticky: false,
              time: ''
            });
            setTimeout(function(){
              location.href = data.url;
            }, 2000);
          };
        }
      });
    });

    //发布到生产环境
    $(".cap_production").click(function(){
      $(this).attr("disabled", true);
      id = $(this).attr("testid");
      $.ajax({
        type: "GET",
        url: "/test/cap_production/"+id,
        dataType: "JSON",
        success: function(data){
          if (data.status) {
            jQuery.gritter.add({
              title: '提醒',
              text: data.message,
                class_name: 'growl-success',
                image: '/static/images/screen.png',
              sticky: false,
              time: ''
            });
            setTimeout(function(){
              location.href = data.url;
            }, 2000);
          } else {
            jQuery.gritter.add({
              title: '提醒',
              text: data.message,
                class_name: 'growl-danger',
                image: '/static/images/screen.png',
              sticky: false,
              time: ''
            });
            setTimeout(function(){
              location.href = data.url;
            }, 2000);
          };
        }
      });
    });

    $(".delete-row").click(function(){
      var c = confirm("确认要删除吗？");
      if(c) {
        testid = $(this).attr("testid");
        issueid = $(this).attr("issueid");
        $.ajax({
          type: "GET",
          url: "/test/del/"+testid+"/"+issueid,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              $("#tr-"+testid).fadeOut(function(){
                $("#tr-"+testid).remove();
              });
              return false;
            } else {
              jQuery.gritter.add({
                title: '提醒',
                text: data.message,
                  class_name: 'growl-danger',
                  image: '/static/images/screen.png',
                sticky: false,
                time: ''
              });
            };
          }
        });
      }
    });

    // Select 2 (dropdown mode)
    var countries = [];
    $.each({<?php foreach($users as $val) { ?>"<?php echo alphaid($val['uid']);?>": "<?php echo $val['realname'];?>",<?php } ?> }, function(k, v) {
        countries.push({id: k, text: v});
    });

    //指定测试人员
    jQuery('#test_user').editable({
        inputclass: 'sel-xs',
        source: countries,
        type: 'text',
        pk: 1,
        ajaxOptions: {
          type: 'GET'
        },
        url: '/issue/change_accept/<?php echo $issueid;?>',
        send: 'always',
        select2: {
            width: 150,
            placeholder: '更改受理人',
            allowClear: true
        },
    });
    
    jQuery('#country').editable({
        inputclass: 'sel-xs',
        source: countries,
        type: 'text',
        pk: 1,
        ajaxOptions: {
          type: 'GET'
        },
        url: '/issue/change_accept/<?php echo $issueid;?>',
        send: 'always',
        select2: {
            width: 150,
            placeholder: '更改受理人',
            allowClear: true
        },
    });

    jQuery('.country').editable({
        inputclass: 'sel-xs',
        source: countries,
        type: 'text',
        pk: 1,
        ajaxOptions: {
          type: 'GET'
        },
        url: '/test/change_accept',
        send: 'always',
        select2: {
            width: 150,
            placeholder: '更改受理人',
            allowClear: true
        },
    });

    $(".view").click(function(){
      id = $(this).attr("testid");
        $.ajax({
          type: "GET",
          url: "/test/view/"+id,
          success: function(data){
            $(".modal-title").text('提测说明');
            $(".modal-body-inner").removeClass('height300');
            $(".modal-body-inner").html(data);
          }
        });
    });

    $(".log").click(function(){
      id = $(this).attr("testid");
        $.ajax({
          type: "GET",
          url: "/test/log/"+id,
          success: function(data){
            $(".modal-title").text('更新日志');
            $(".modal-body-inner").addClass('height300');
            $(".modal-body-inner").html(data);
          }
        });
    });

    //读取任务相关的操作日志
    $("#log-list").click(function(){
      id = $(this).attr("data-id");
        $.ajax({
          type: "GET",
          url: "/issue/log_list/"+id,
          dataType: "JSON",
          success: function(data){
            if (data.total) {
              var log_list = '';
              for(var p in data.comment){
                log_list += '<tr class="unread"><td></td><td><a href="" class="star"><i class="fa fa-dot-circle-o"></i></a></td><td><div class="media"><a href="#" class="face"><img alt="" src="'+data.comment[p].avatar+'"></a><div class="media-body"><span class="media-meta pull-right">'+data.comment[p].friendtime+'前</span><h4 class="text-primary">'+data.comment[p].realname+'</h4><small class="text-muted"></small><p class="email-summary">'+data.comment[p].content+'</p></div></div></td></tr>';
              }
              $("#added").html('<div class="table-responsive"><table class="table table-email"><tbody>'+log_list+'</tbody></table></div>');
            } else {
              $("#added").html('<div align="center">暂无操作日志</div>');
            }
          }
        });
    });

  });
</script>

<script type="text/javascript">
$(function(){
  toolbar = [ 'title', 'bold', 'italic', 'underline', 'strikethrough',
    'color', '|', 'ol', 'ul', 'blockquote', 'code', 'table', '|',
    'link', 'image', 'hr', '|', 'indent', 'outdent' ];
  var editor = new Simditor({
    textarea : $('#content'),
    toolbar : toolbar,  //工具栏
    defaultImage : '<?php echo STATIC_HOST.'/'; ?>static/simditor-2.3.6/images/image.png', //编辑器插入图片时使用的默认图片
    pasteImage: true,
    upload: {
        url: '/dashboard/upload',
        fileKey: 'upload_file', //服务器端获取文件数据的参数名  
        connectionCount: 3,  
        leaveConfirm: '正在上传文件'
      }
  });

  $("#btnSubmit").click(function(){
    content = $("#content").val();
    content = htmlEncode(content);
    issue_id = $("#issue_id").val();
    if (!content) {
      editor.focus();
      return false;
    }
    $.ajax({
      type: "POST",
      url: "/issue/coment_add_ajax",
      data: "content="+content+"&issue_id="+issue_id,
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          $("#box").append('<div class="media"><div class="pull-left"><div class="face"><img alt="" src="'+data.message.avatar+'" align="absmiddle" title="'+data.message.realname+'"></div></div><div class="media-body"><span class="media-meta pull-right">'+data.message.addtime+'</span><h6 class="text-muted">'+data.message.realname+'</h6><small class="text-muted">'+data.message.usertype+'</small><p>'+data.message.content+'</p></div></div>');
          editor.setValue('');
        } else {
          alert('fail');
        };
      }
    });
  });

  $(".del").click(function(){
    var c = confirm('你确定要删除吗？');
      if(c) {
        id = $(this).attr("ids");
        $.ajax({
          type: "GET",
          url: "/issue/del_comment/"+id,
          dataType: "JSON",
          success: function(data){
            if (data.status) {
              setTimeout(function () {
                $("#comment-"+id).hide();
              }, 500);
            } else {
              alert('fail');
            }
          }
        });
      }
  });

  $("#watch").click(function(){
    id = $(this).attr("issueid");
    $.ajax({
      type: "GET",
          url: "/issue/watch/"+id+"/1",
      dataType: "JSON",
      success: function(data){
        if (data.status) {
            jQuery('#watch').hide();
            jQuery('#unwatch').show();
        } else {
          alert('操作失败');
        }
      }
    });
  });

  $("#unwatch").click(function(){
    id = $(this).attr("issueid");
    $.ajax({
      type: "GET",
          url: "/issue/watch/"+id+"/0",
      dataType: "JSON",
      success: function(data){
        if (data.status) {
            jQuery('#unwatch').hide();
            jQuery('#watch').show();
        } else {
          alert('操作失败');
        }
      }
    });
  });

  $('.deploy').click(function(){
    var id = $(this).attr('ids');
    var env = $(this).attr('env');
    var repos = $(this).attr('repos');
    var br = $(this).attr('br');
    var rev = $(this).attr('rev');
    var obj = "#deploy-"+id;
    var curr = $(obj).attr('curr');
    $(obj).attr('curr', env);
    var merge = $(this).attr('merge');
    if (merge == 1) {
      var cap = "cd ~/"+env+"/"+repos+"/ && cap staging deploy br="+br+" rev="+rev+" issue=<?php echo $issueid; ?>";
    } else if (repos == 'gc.style-conf') {
      var cap = "cd ~/"+env+"/"+repos+"/ && cap staging deploy rev="+rev+" issue=<?php echo $issueid; ?>";
    } else {
      var cap = '此代码库不适合使用capistrano部署，请使用CAP部署';
    }
    
    $(obj+' input').val(cap).select();
    if(!$(obj).hasClass('open')) {
      $("#abc-"+id).show();
      $(obj).addClass('open');
      $(obj+' input').select();
    } else {
      if (curr == env) {
        $("#abc-"+id).hide();
        $(obj).removeClass('open');
        $("#abc-"+id).css({"display":"none"});
      }
    }
    return false;
  });

  //我要开发
  $(".dev").click(function(){
    $(this).attr("disabled", true);
    id = $(this).attr("ids");
    $.ajax({
      type: "GET",
      url: "/issue/change_flow/"+id+"/dev",
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          $(this).hide();
          $("#td-dev").addClass('blue');
          $("#td-dev").text('开发中');
          tip(data.message, window.location.href, 'success', 2000);
        } else {
          tip(data.message, window.location.href, 'danger', 5000);
        };
      }
    });
  });

  //我要测试
  $(".test").click(function(){
    $(this).attr("disabled", true);
    id = $(this).attr("ids");
    $.ajax({
      type: "GET",
      url: "/issue/change_flow/"+id+"/test",
      dataType: "JSON",
      success: function(data){
        if (data.status) {
          $(this).hide();
          $("#td-test").addClass('blue');
          $("#td-test").text('测试中');
          tip(data.message, window.location.href, 'success', 2000);
        } else {
          tip(data.message, window.location.href, 'danger', 5000);
        };
      }
    });
  });

  //开发完毕
  $(".over").click(function(){
    var c = confirm('你确定已经完成代码信息提交了吗？');
    if(c) {
      $(this).attr("disabled", true);
      id = $(this).attr("ids");
      $.ajax({
        type: "GET",
        url: "/issue/change_flow/"+id+"/over",
        dataType: "JSON",
        success: function(data){
          if (data.status) {
            $(this).hide();
            $("#td-over").addClass('blue');
            $("#td-over").text('开发完毕');
            tip(data.message, window.location.href, 'success', 2000);
          } else {
            tip(data.message, window.location.href, 'danger', 5000);
          };
        }
      });
    }
  });

  //修复完毕
  $(".fix").click(function(){
    var c = confirm('你确定已经完成所有BUG修复并提交相应代码了吗？');
    if(c) {
      $(this).attr("disabled", true);
      id = $(this).attr("ids");
      $.ajax({
        type: "GET",
        url: "/issue/change_flow/"+id+"/fixed",
        dataType: "JSON",
        success: function(data){
          if (data.status) {
            $(this).hide();
            $("#td-fix").addClass('blue');
            $("#td-fix").text('修复完毕');
            tip(data.message, data.url, 'success', 2000);
          } else {
            tip(data.message, data.url, 'danger', 5000);
          };
        }
      });
    }
  });

  //测试通过
  $(".waits").click(function(){
    var c = confirm('你确定已经验证通过了吗？');
    if(c) {
      $(this).attr("disabled", true);
      id = $(this).attr("ids");
      $.ajax({
        type: "GET",
        url: "/issue/change_flow/"+id+"/wait",
        dataType: "JSON",
        success: function(data){
          if (data.status) {
            $(this).hide();
            $("#td-wait").addClass('blue');
            $("#td-wait").text('测试通过');
            tip(data.message, data.url, 'success', 2000);
          } else {
            tip(data.message, data.url, 'danger', 5000);
          };
        }
      });
    }
  });

  //已上线
  $(".onlines").click(function(){
    var c = confirm('你确定已经完成上线了吗？');
    if(c) {
      $(this).attr("disabled", true);
      id = $(this).attr("ids");
      $.ajax({
        type: "GET",
        url: "/issue/change_flow/"+id+"/online",
        dataType: "JSON",
        success: function(data){
          if (data.status) {
            $(this).hide();
            $("#td-online").addClass('blue');
            $("#td-online").text('已上线');
            tip(data.message, data.url, 'success', 2000);
          } else {
            tip(data.message, data.url, 'danger', 5000);
          };
        }
      });
    }
  });

  var ss = window.location.href.split("#");
  $('#'+ss[1]).css('background','#f1f1f1')

});

//消息提醒通用组建配置
function tip(message, url, color, sec) {
  jQuery.gritter.add({
    title: '提醒',
    text: message,
    class_name: 'growl-'+color,
    sticky: false,
    time: ''
  });
  setTimeout(function(){
    location.href = url;
  }, sec);
}
</script>

</body>
</html>
