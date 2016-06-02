<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Signin extends CI_Controller {

	/**
     * 登录面板
     */
	public function index() {
		$data['PAGE_TITLE'] = '登录';
        $this->load->view('signin', $data);
    }

    /**
     * 登录信息验证
     */
    public function check() {

        //输入合法性验证
        $this->load->library(array('form_validation', 'curl', 'encryption'));
        $this->form_validation->set_rules('username', '用户名', 'trim|required');
        $this->form_validation->set_rules('password', '密码', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //存在性验证
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $api = $this->curl->get($system['api_host'].'/users/signin_check?username='.$this->input->post('username').'&password='.md5($this->input->post('password')));
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                exit(json_encode(array('status' => false, 'error' => $output['error'])));
            } else {
                $auth = serialize(array('user_id' => $output['data']['uid'], 'user_name' => $output['data']['username'], 'real_name' => $output['data']['realname']));
                $this->input->set_cookie('cits_auth', $this->encryption->encrypt($auth), 86400*5);
                $this->input->set_cookie('cits_user_online', time(), 86400);
                $this->load->model('Model_online', 'online', TRUE);
                $this->online->updateByUnique(array('uid' => $output['data']['uid'], 'act_time' => time()));
                exit(json_encode(array('status' => true, 'message' => '验证通过')));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
}