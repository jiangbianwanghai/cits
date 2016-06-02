<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function index() {
		/*$this->load->library('encryption');
		$auth =  unserialize($this->encryption->decrypt($this->input->cookie('cits_auth')));
		print_r($auth);
		echo '<a href="/dashboard/logout">退出</a>';*/

		$data['PAGE_TITLE'] = '我的面板';

		//刷新在线用户列表
		$this->load->model('Model_online', 'online', TRUE);
		$onlineUsers = $this->online->users();
		$this->online->refreshOnline(UID);
		$data['online_users'] = $onlineUsers;
        $this->load->view('home', $data);
	}

	/**
	 * 退出
	 */
	public function logout() {
		$this->load->helper(array('cookie', 'url'));
        delete_cookie('cits_auth');
        delete_cookie('cits_user_online');
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->delByUid(UID);
        redirect('/', 'location');
	}
}