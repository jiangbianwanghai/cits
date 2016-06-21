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
    public function upload() {
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
}