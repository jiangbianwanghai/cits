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
        $this->load->library(array('form_validation', 'curl'));
        $this->form_validation->set_rules('email', '邮箱', 'trim|required|valid_email|min_length[5]|max_length[50]',
            array(
                'required' => '%s 不能为空',
                'valid_email' => '邮箱[ '.$this->input->post('email').' ]不符合规则',
                'min_length' => '邮箱[ '.$this->input->post('email').' ]长度不够',
                'max_length' => '邮箱[ '.$this->input->post('email').' ]太长了'
            )
        );
        $this->form_validation->set_rules('username', '用户名', 'trim|required|alpha_dash|min_length[3]|max_length[30]',
            array(
                'required' => '%s 不能为空',
                'alpha_dash' => '用户名[ '.$this->input->post('username').' ]不符合规则',
                'min_length' => '用户名[ '.$this->input->post('username').' ]长度不够',
                'max_length' => '用户名[ '.$this->input->post('username').' ]太长了'
            )
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

        //验证用户名是否符合要求
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        if (in_array($this->input->post('username'), $system['forbidden_username'])) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':此为保留用户名，不能注册');
            exit(json_encode(array('status' => false, 'error' =>'此为保留用户名，不能注册')));
        }

        //唯一性验证
        //邮箱
        $api = $this->curl->get($system['api_host'].'/user/check_email?email='.$this->input->post('email').'&access_token='.$system['access_token']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.$output['error']);
                exit(json_encode(array('status' => false, 'error' => $output['error'])));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':验证邮箱API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
        //用户名
        $api = $this->curl->get($system['api_host'].'/user/check_username?username='.$this->input->post('username').'&access_token='.$system['access_token']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.$output['error']);
                exit(json_encode(array('status' => false, 'error' => $output['error'])));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':验证用户名API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //写入数据
        $Post_data['email'] = $this->input->post('email');
        $Post_data['username'] = $this->input->post('username');
        $Post_data['password'] = md5($this->input->post('password'));
        $Post_data['access_token'] = $system['access_token'];
        $api = $this->curl->post($system['api_host'].'/user/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                //设置登录Cookie
                $auth = serialize(array('user_id' => $output['content'], 'user_name' => $this->input->post('username'), 'real_name' => $this->input->post('username')));
                $this->input->set_cookie('cits_auth', $this->encryption->encrypt($auth), 43200); //缓存半天，再登录让他完善其他信息
                
                //更新在线时间戳
                $this->input->set_cookie('cits_user_online', time(), 43200);
                $this->load->model('Model_online', 'online', TRUE);
                $this->online->update_by_unique(array('uid' => $output['content'], 'act_time' => time()));

                //刷新用户缓存
                $this->refresh();
                exit(json_encode(array('status' => true, 'message' => '注册成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':注册失败');
                exit(json_encode(array('status' => false, 'error' => '注册失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入用户信息API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 刷新用户缓存文件
     */
    public function refresh()
    {
        $this->load->library(array('curl'));
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $api = $this->curl->get($system['api_host'].'/user/cache?access_token='.$system['access_token']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->load->helper('file');
                foreach ($output['content']['data'] as $key => $value) {
                    $rows[$value['uid']] = $value;
                    $rows[$value['uid']]['sha'] = $this->encryption->encrypt($value['uid']);
                }
                write_file(APPPATH.'/cache/user.cache.php', serialize($rows));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取用户缓存API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
}