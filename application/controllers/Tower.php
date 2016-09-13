<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tower extends CI_Controller {

	public function index()
	{
		$str = file_get_contents("php://input");
		//file_put_contents(APPPATH.'cache/tower.log', $str, FILE_APPEND);
        if ($str) {
            $this->config->load('extension', TRUE);
            $system = $this->config->item('system', 'extension');
            $this->load->library('curl', array('token'=>$system['access_token']));

            $Post_data['origin'] = 'tower';
            $Post_data['content'] = $str;
            $api = $this->curl->post($system['api_host'].'/webhooks/write', $Post_data);
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    exit(json_encode(array('status' => true, 'message' => '写入成功', 'content' => $id)));
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入失败：'.$output['error']);
                    exit(json_encode(array('status' => false, 'error' => '写入失败：'.$output['error'])));
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入信息API异常.HTTP_CODE['.$api['httpcode'].']');
                exit(json_encode(array('status' => false, 'error' => '写入信息API.HTTP_CODE['.$api['httpcode'].']')));
            }
        }
	}
}
