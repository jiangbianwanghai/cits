<?php
class acl{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function index()
    {
        $this->CI->config->load('extension', TRUE);
        $system = $this->CI->config->item('system', 'extension');
        define('STATIC_HOST', $system['static_host']);//初始化样式地址
        define('FILE_HOST', $system['file_host']);//初始化文件上传地址
        define('AVATAR_HOST', $system['avatar_host']);//初始化头像地址

        //例外访问页面（登录和注册页面不需要验证权限）
        if (!in_array($this->CI->uri->segment(1), array('signin', 'signup', 'forgot', 'reset', 'tower'))) {
            if (!$this->CI->input->cookie('cits_auth')) {
                $this->CI->load->helper('url');
                $this->CI->input->set_cookie(array('name' => 'cits_redirect_url', 'value' => $_SERVER['REQUEST_URI'], 'expire' => 86400));
                redirect('/signin', 'location');
            }
        }

        //获取用户Cookie
        $this->CI->load->library('encryption');
        $auth =  unserialize($this->CI->encryption->decrypt($this->CI->input->cookie('cits_auth')));
        define('UID', $auth['user_id']);
        define('USER_NAME', $auth['user_name']);
        define('REAL_NAME', $auth['real_name']);
    }
}
