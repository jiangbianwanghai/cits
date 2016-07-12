<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户模块
 */
class User extends CI_Controller {

    /**
     * 用户列表
     *
     * 默认显示所有项目
     */
    public function index()
    {
        $data['PAGE_TITLE'] = '用户面板';

        $this->load->view('user', $data);
    }

    /**
     * 刷新用户缓存文件
     */
    public function refresh()
    {
        $this->load->library('encryption');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/user/cache');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->load->helper('file');
                foreach ($output['content']['data'] as $key => $value) {
                    $rows[$value['uid']] = $value;
                    $rows[$value['uid']]['sha'] = $this->encryption->encrypt($value['uid']);
                }
                write_file(APPPATH.'/cache/user.cache.php', serialize($rows));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 操作记录
     */
    public function log()
    {
        $data['PAGE_TITLE'] = '操作记录';

        $offset = $this->uri->segment(3, 0);

        //读取系统配置信息
        $this->load->helper('alphaid');
        $data['logs'] = array('total' => 0, 'data' => array());
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $config = $this->config->item('pages', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/handle/get_rows?uid='.UID.'&offset='.$offset.'&limit='.$config['per_page']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['logs'] = $output['content'];
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        //分页
        $this->load->library('pagination');
        $config['total_rows'] = $data['logs']['total'];
        $config['cur_page'] = $offset;
        $config['base_url'] = '/user/log';
        $this->pagination->initialize($config);
        $data['pages'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        $data['per_page'] = $config['per_page'];

        $this->load->view('user_log', $data);
    }
}