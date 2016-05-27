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

        //初始化样式地址
        define('STATIC_HOST', $system['static_host']);
        //初始化文件上传地址
        define('FILE_HOST', $system['file_host']);
        //例外访问页面（登录和注册页面不需要验证权限）
        if (!in_array($this->CI->uri->segment(1), array('signin', 'signup'))) {
            if (!$this->CI->input->cookie('cits_auth')) {
                $this->CI->load->helper('url');
                redirect('/signin', 'location');
            }
        }
    }
}
