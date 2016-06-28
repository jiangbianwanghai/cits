<div class="leftpanel">
  <div class="logopanel" align="center"><strong style="font-family: Arial, Helvetica; ">CITS-巧克力任务跟踪系统</strong> <sup><a target="_blank" href="https://github.com/jiangbianwanghai/cbts/releases" title="点击查看版本更新日志"><span class="badge badge-info">0.3.1</span></a></sup>
  </div><!-- logopanel -->
  <div class="leftpanelinner">
    <h5 class="sidebartitle">快捷导航</h5>
    <ul class="nav nav-pills nav-stacked nav-bracket">
      <li<?php if ($this->uri->segment(1, 'dashboard') == 'dashboard') echo ' class="active"';?>><a href="/"><i class="fa fa-home"></i> <span>我的面板</span></a></li>
      <li<?php if ($this->uri->segment(1, '') == 'plan') echo ' class="active"';?>><a href="/plan"><i class="fa fa-thumb-tack"></i> <span>计划管理</span></a></li>
      <li<?php if ($this->uri->segment(1, '') == 'bug') echo ' class="active"';?>><a href="/bug"><i class="fa fa-bug"></i> <span>Bug管理</span></a></li>
      <li<?php if ($this->uri->segment(1, '') == 'issue') echo ' class="active"';?>><a href="/issue"><i class="fa fa-tasks"></i> <span>任务管理</span></a></li>
      <li<?php if ($this->uri->segment(1, '') == 'test') echo ' class="active"';?>><a href="/test/plaza"><i class="fa fa-cloud-upload"></i> <span>提测管理</span></a></li>
      <li<?php if ($this->uri->segment(1, '') == 'analytics') echo ' class="active"';?>><a href="/analytics"><i class="fa fa-medkit"></i> <span>数据分析</span></a></li>
      <li<?php if ($this->uri->segment(1, '') == 'conf' && $this->uri->segment(2, '') != 'profile') echo ' class="active"';?>><a href="/conf/repos_list"><i class="fa fa-suitcase"></i> <span>代码库管理</span></a></li>
    </ul>
  </div><!-- leftpanelinner -->
</div><!-- leftpanel -->
<a href="http://form.mikecrm.com/yrR8O7" target="_blank" style="position:fixed;z-index:999;right:5px;bottom: 20px;display: inline-block;width: 30px;border-radius: 5px;color:white;font-size:14px;line-height:17px;background: #2476CE;box-shadow: 0 0 5px #666;word-wrap: break-word;padding:7px;border: 1px solid white;">使用反馈</a>
