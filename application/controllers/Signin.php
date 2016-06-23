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
        $this->load->library(array('form_validation', 'curl'));
        $this->form_validation->set_rules('username', '用户名', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('password', '密码', 'trim|required',
            array('required' => '%s 不能为空')
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //存在性验证
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $api = $this->curl->get($system['api_host'].'/user/signin_check?username='.$this->input->post('username').'&password='.md5($this->input->post('password')).'&access_token='.$system['access_token']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                exit(json_encode(array('status' => false, 'error' => $output['error'])));
            } else {
                //这是Cookie
                $auth = serialize(array('user_id' => $output['content']['uid'], 'user_name' => $output['content']['username'], 'real_name' => $output['content']['realname']));
                $this->input->set_cookie('cits_auth', $this->encryption->encrypt($auth), 86400*5);
                $this->input->set_cookie('cits_user_online', time(), 86400);

                //更新在线时间戳
                $this->load->model('Model_online', 'online', TRUE);
                $this->online->update_by_unique(array('uid' => $output['content']['uid'], 'act_time' => time()));

                //从个人信息中获取
                $api = $this->curl->get($system['api_host'].'/user/row?uid='.$output['content']['uid'].'&access_token='.$system['access_token']);
                if ($api['httpcode'] == 200) {
                    $output = json_decode($api['output'], true);
                    if ($output['status']) {
                        if ($output['content']['star_project']) {
                            $this->input->set_cookie('cits_star_project', $this->encryption->encrypt($output['content']['star_project']), 86400*5);
                        }
                    } else {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.$output['error']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':获取关注项目API异常.HTTP_CODE['.$api['httpcode'].']');
                    exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
                }
                
                exit(json_encode(array('status' => true, 'message' => '验证通过')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':验证用户登录信息API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
}