<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class reset extends CI_Controller {

    /**
     * 重设密码面板
     */
    public function index() {

        $data['PAGE_TITLE'] = '重置你的密码';
        $data['token'] = $this->input->get('token', TRUE);
        $this->load->view('reset', $data);
    }

    public function send() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('token', 'token', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('password', '密码', 'trim|required|min_length[6]|max_length[16]',
            array(
                'required' => '%s 不能为空',
                'alpha_dash' => '密码[ '.$this->input->post('password').' ]不符合规则',
                'min_length' => '密码[ '.$this->input->post('password').' ]长度不够',
                'max_length' => '密码[ '.$this->input->post('password').' ]太长了'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        $email = $this->encryption->decrypt($this->input->post('token'));

        //验证邮箱是否存在
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $Post_data['email'] = $email;
        $Post_data['password'] = $this->input->post('password');
        $api = $this->curl->post($system['api_host'].'/user/change_password', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                exit(json_encode(array('status' => true, 'message' => '修改成功，请登录验证')));
            } else {
                exit(json_encode(array('status' => false, 'error' => '修改失败，可能是你的Token造成的')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':验证邮箱API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
}