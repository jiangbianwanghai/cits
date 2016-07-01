<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class forgot extends CI_Controller {

    /**
     * 重设密码面板
     */
    public function index() {
        $data['PAGE_TITLE'] = '发送重置密码邮件';
        $this->load->view('forgot', $data);
    }

    public function send() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', '邮箱', 'trim|required|valid_email',
            array(
                'required' => '%s 不能为空',
                'valid_email' => '%s 格式错误'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //验证邮箱是否存在
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/user/get_row_by_email?email='.$this->input->post('email'));
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':系统不存在此邮箱');
                exit(json_encode(array('status' => false, 'error' => '系统不存在此邮箱')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':验证邮箱API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        if ($output['content']['forgot'] == 1 && ((time() - $output['content']['reset_email_time']) < 300)) {
            exit(json_encode(array('status' => false, 'error' => '5分钟后才可以发送第二封')));
        }

        //发送重置邮件
        $subject = '重置我的密码 - CITS';
        $message = '重置链接：<a target="_blank" href="http://cits.gongchang.net/reset?token='.urlencode($this->encryption->encrypt($this->input->post('email'))).'">http://cits.gongchang.net/reset?token='.urlencode($this->encryption->encrypt($this->input->post('email'))).'</a>';
        $this->load->library('email');
        $this->config->load('extension', TRUE);
        $email = $this->config->item('email', 'extension');
        $this->email->initialize($email);
        $this->email->from($email['smtp_user']);
        $this->email->to($this->input->post('email'));
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
        //更改记录状态
        $api = $this->curl->get($system['api_host'].'/user/reset?email='.$this->input->post('email').'&forgot=1');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':更改记录状态');
                exit(json_encode(array('status' => false, 'error' => '更改记录状态')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':验证邮箱API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '更改数据库状态API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
        exit(json_encode(array('status' => true, 'message' => '重置邮件已经发送')));
    }
}