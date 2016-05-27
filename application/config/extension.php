<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* 配置文件
*/

//通用
$config['system'] = array(
    //静态资源主机地址
    'static_host' => 'http://static.cits.org.cn',
    //上传文件主机地址
    'file_host' => 'http://file.cits.org.cn',
    //禁止注册的用户名
    'forbidden_username' => array('admin', 'webmaster', 'administrator', 'manage'),
    //API主机地址
    'api_host' => 'http://api.cits.org.cn'
);

//翻页
$config['pages'] = array(
	'num_links' => 3,
    'full_tag_open' => '<ul class="pagination pull-right">',
    'full_tag_close' => '</ul>',
    'num_tag_open' => '<li>',
    'num_tag_close' => '</li>',
    'first_link' => '首页',
    'first_tag_open' => '<li>',
    'first_tag_close' => '</li>',
    'last_link' => '尾页',
    'last_tag_open' => '<li>',
    'last_tag_close' => '</li>',
    'prev_link' => '上一页',
    'prev_tag_open' => '<li>',
    'prev_tag_close' => '</li>',
    'next_link' => '下一页',
    'next_tag_open' => '<li>',
    'next_tag_close' => '</li>',
    'cur_tag_open' => '<li class="active"><a href="#">',
    'cur_tag_close' => '</a></li>',
    'total_rows' => 10,
    'per_page' => 15,
    'cur_page' => 1,
    'base_url' => '/'
);

//优先级和严重程度
$config['level'] = array(
    1=> array(
        'name'=>'!',
        'alt' => '较轻',
        'task' => '抽空处理'
    ),
    2=> array(
        'name'=>'!!',
        'alt' => '正常',
        'task' => '正常迭代'
    ),
    3=> array(
        'name'=>'!!!',
        'alt' => '严重',
        'task' => '优先处理'
    ),
    4=> array(
        'name'=>'!!!!',
        'alt' => '非常严重',
        'task' => '非常紧急'
    )
);

//工作流正向（用于入库）
$config['workflow'] = array(
    0 => array(
        'name' => '新建',
        'en_name' => 'new',
        'span_color' => 'default'
    ),
    1 => array(
        'name' => '开发中',
        'en_name' => 'dev',
        'span_color' => 'primary'
    ),
    2 => array(
        'name' => '开发完毕',
        'en_name' => 'over',
        'span_color' => 'info'
    ),
    3 => array(
        'name' => '修复中',
        'en_name' => 'fix',
        'span_color' => 'danger'
    ),
    4 => array(
        'name' => '修复完毕',
        'en_name' => 'fixed',
        'span_color' => 'danger'
    ),
    5 => array(
        'name' => '测试中',
        'en_name' => 'test',
        'span_color' => 'primary'
    ),
    6 => array(
        'name' => '测试通过',
        'en_name' => 'wait',
        'span_color' => 'warning'
    ),
    7 => array(
        'name' => '上线',
        'en_name' => 'online',
        'span_color' => 'success'
    )
);

//工作流反向（用于搜索筛选）
$config['workflowfilter'] = array(
    'new' => array( 'id' => '0', 'name' => '新建'),
    'dev' => array( 'id' => '1', 'name' => '开发中'),
    'over' => array( 'id' => '2', 'name' => '开发完毕'),
    'fix' => array( 'id' => '3', 'name' => '修复中'),
    'fixed' => array( 'id' => '4', 'name' => '修复完毕'),
    'test' => array( 'id' => '5', 'name' => '测试中'),
    'wait' => array( 'id' => '6', 'name' => '待上线'),
    'online' => array( 'id' => '7', 'name' => '上线')
);

//任务类型
$config['tasktype'] = array('task' => 1, 'bug' => 2);