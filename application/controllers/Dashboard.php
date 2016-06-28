<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 我的面板
 */
class Dashboard extends CI_Controller {

    public function index() {

        $data['PAGE_TITLE'] = '我的面板';

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;
        
        $this->load->view('home', $data);
    }

    /**
     * 退出
     */
    public function logout() {
        $this->load->helper(array('cookie', 'url'));
        delete_cookie('cits_auth'); //删除用户信息
        delete_cookie('cits_user_online'); //删除在线时间戳
        delete_cookie('cits_star_project'); //删除关注项目

        //删除在线状态
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->del_by_uid(UID);
        redirect('/', 'location');
    }

    /**
     * 图片上传
     */
    public function upload()
    {
        if($_FILES['upload_file']) {
            $dir_name = date("Ymd", time());
            $this->config->load('extension', TRUE);
            $system = $this->config->item('system', 'extension');
            $dir = $system['file_dir'].'/'.$dir_name;
            if (!is_dir($dir)) mkdir($dir, 0777);
            $config['upload_path'] = $dir; 
            $config['file_name'] = 'IMG_'.time();
            $config['overwrite'] = TRUE;
            $config["allowed_types"] = 'jpg|jpeg|png|gif';
            $config["max_size"] = 2048;
            $this->load->library('upload', $config);

            if(!$this->upload->do_upload('upload_file')) {               
                $error = $this->upload->display_errors();
                echo '{"success": false,"msg": "'.$error.'"}';
            } else {
                $data['upload_data']=$this->upload->data();
                $img=$data['upload_data']['file_name'];
                echo '{"success": true,"file_path": "'.$system['file_host'].'/'.$dir_name.'/'.$img.'"}';                              
            }  
        }
    }

    /**
     * 读取提醒信息
     */
    public function get_notify()
    {
        //获得消息记录
        $this->load->helper('alphaid');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/notify/get_rows?uid='.UID.'&access_token='.$system['access_token']);
        if ($api['httpcode'] == 200) {
            $users = array();
            if (file_exists(APPPATH.'/cache/user.cache.php')) {
              $users = file_get_contents(APPPATH.'/cache/user.cache.php');
              $users = unserialize($users);
            }
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                foreach ($output['content']['data'] as $key => $value) {
                    $output['content']['data'][$key]['user_realname'] = $users[$value['user']]['realname'];
                    $output['content']['data'][$key]['log_action'] = $value['log']['action'];
                    $output['content']['data'][$key]['log_subject'] = $value['log']['subject'];
                    
                    if ($value['log']['target_type'] == '3') {
                        $url = 'issue/view/'.alphaid($value['log']['target']).'?is_read=yes';
                        $output['content']['data'][$key]['log_target_type'] = '任务';
                    }
                    $output['content']['data'][$key]['log_url'] = $url;
                    $output['content']['data'][$key]['log_target'] = $value['log']['target'];
                    $output['content']['data'][$key]['log_sender_username'] = $users[$value['log']['sender']]['username'];
                    $output['content']['data'][$key]['log_sender_realname'] = $users[$value['log']['sender']]['realname'];
                }
                $output['content']['datas'] = $output['content']['data'];
                unset($output['content']['data']);
                exit(json_encode($output['content']));
            } else {
                exit(json_encode(array('total' => 0)));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取提醒API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
}