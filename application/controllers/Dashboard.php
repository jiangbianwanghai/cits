<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 我的面板
 */
class Dashboard extends CI_Controller {

    public function index() {

        $data['PAGE_TITLE'] = '我的面板';

        //刷新在线用户列表
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
}