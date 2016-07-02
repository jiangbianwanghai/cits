<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 提醒
 */
class Notify extends CI_Controller {

    public function index() {

        $data['PAGE_TITLE'] = '我的提醒';

        $offset = $this->uri->segment(3, 0);

        //读取系统配置信息
        $this->load->helper('alphaid');
        $data['notify'] = array('total' => 0, 'data' => array());
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $config = $this->config->item('pages', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/notify/get_rows?uid='.UID.'&offset='.$offset.'&limit='.$config['per_page']);
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
                        $url = '/issue/view/'.alphaid($value['log']['target']);
                    }
                    if ($value['log']['action'] == '评论') {
                        $url .= '#comment-'.alphaid($value['log']['content']);
                    }
                    $end = '';
                    if ($value['log']['action'] == '变更') {
                        $end = ' 的工作流状态为 '.$value['log']['content'];
                    }
                    $data['notify']['data'][$key]['subject'] = $subject.' #<a href="javascript:;" data-url="'.$url.'" data-id="'.alphaid($value['id']).'" class="notify-read">'.$value['log']['subject'].'</a>'.$end;
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

        //分页
        $this->load->library('pagination');
        $config['total_rows'] = $data['notify']['total'];
        $config['cur_page'] = $offset;
        $config['base_url'] = '/notify/index';
        $this->pagination->initialize($config);
        $data['pages'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        $data['per_page'] = $config['per_page'];
        
        $this->load->view('notify', $data);
    }

    public function read()
    {
        $id = $this->input->get('id', TRUE);
        $this->load->helper('alphaid');
        $id = alphaid($id, 1);

        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $Post_data['id'] = $id;
        $api = $this->curl->post($system['api_host'].'/notify/change_read', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':已读提醒操作失败');
                exit(json_encode(array('status' => false, 'message' => '已阅失败')));
            } else {
                $this->load->helper('url');
                $token = $this->input->get('token', TRUE);
                if ($token) {
                    $url = $this->encryption->decrypt($token);
                    redirect($url, 'location');exit();
                }
                exit(json_encode(array('status' => true, 'message' => '已阅')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':已读提醒API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'message' => '已阅API接口调取失败')));
        }
    }
}