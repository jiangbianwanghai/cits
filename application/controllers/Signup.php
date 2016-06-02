<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 注册
 */
class Signup extends CI_Controller {

    /**
     * 注册面板
     */
	public function index() {
		$data['PAGE_TITLE'] = '注册';
        $this->load->view('signup', $data);
    }

    /**
     * 写入注册信息
     */
    public function add() {

        //验证输入
        $this->load->library(array('form_validation', 'curl', 'encryption'));
        $this->form_validation->set_rules('email', '邮箱', 'trim|required|valid_email|min_length[5]|max_length[50]');
        $this->form_validation->set_rules('username', '用户名', 'trim|required|alpha_dash|min_length[3]|max_length[30]');
        $this->form_validation->set_rules('password', '密码', 'trim|required|min_length[6]|max_length[16]');
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //验证用户名是否符合要求
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        if (in_array($this->input->post('username'), $system['forbidden_username'])) {
            exit(json_encode(array('status' => false, 'error' =>'此为保留用户名，不能注册')));
        }

        //唯一性验证
        //邮箱
        $api = $this->curl->get($system['api_host'].'/users/check_email?email='.$this->input->post('email'));
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                exit(json_encode(array('status' => false, 'error' => $output['error'])));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
        //用户名
        $api = $this->curl->get($system['api_host'].'/users/check_username?username='.$this->input->post('username'));
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                exit(json_encode(array('status' => false, 'error' => $output['error'])));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //写入数据
        $Post_data['email'] = $this->input->post('email');
        $Post_data['username'] = $this->input->post('username');
        $Post_data['password'] = md5($this->input->post('password'));
        $api = $this->curl->post($system['api_host'].'/users/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $auth = serialize(array('user_id' => $output['data'], 'user_name' => $this->input->post('username'), 'real_name' => $this->input->post('username')));
                $this->input->set_cookie('cits_auth', $this->encryption->encrypt($auth), 43200); //缓存半天，再登录让他完善其他信息
                $this->input->set_cookie('cits_user_online', time(), 43200);
                $this->load->model('Model_online', 'online', TRUE);
                $this->online->updateByUnique(array('uid' => $output['data'], 'act_time' => time()));
                exit(json_encode(array('status' => true, 'message' => '注册成功')));
            } else {
                exit(json_encode(array('status' => false, 'error' => '注册失败')));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
}