<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function index() {
		$this->load->library('encryption');
		$auth =  unserialize($this->encryption->decrypt($this->input->cookie('cits_auth')));
		print_r($auth);
		echo '<a href="/dashboard/logout">退出</a>';
	}

	/**
	 * 退出
	 */
	public function logout() {
		$this->load->helper(array('cookie', 'url'));
        delete_cookie('cits_auth');
        redirect('/', 'location');
	}
}