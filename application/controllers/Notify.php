<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 提醒
 */
class Notify extends CI_Controller {

    public function index() {

        $data['PAGE_TITLE'] = '我的提醒';

        //读取系统配置信息
        $this->load->helper('alphaid');
        $data['notify'] = array('total' => 0, 'data' => array());
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/notify/get_rows?uid='.UID);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['notify'] = $output['content'];
                foreach ($output['content']['data'] as $key => $value) {
                    $data['notify']['data'][$key]['sender'] = $value['log']['sender'];
                    $subject = '给你';
                    $subject = $value['log']['action'].'了';
                    if ($value['log']['target_type'] == '3') {
                        $subject .= '任务';
                        $url = '/issue/view/'.alphaid($value['log']['target']).'?is_read=yes';
                    }
                    if ($value['log']['action'] == '评论') {
                        $url .= '#comment-'.alphaid($value['log']['content']);
                    }
                    $data['notify']['data'][$key]['subject'] = $subject.' #<a href="'.$url.'">'.$value['log']['subject'].'</a>';
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取提醒API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;
        
        $this->load->view('notify', $data);
    }
}