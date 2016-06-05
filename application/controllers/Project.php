<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project extends CI_Controller {

	/**
	 * 项目列表
	 * 默认显示所有项目
	 */
	public function index()
	{

	}

	/**
	 * 添加项目
	 */
	public function add_ajax()
	{
		//验证输入
        $this->load->library(array('form_validation', 'curl', 'encryption'));
        $this->form_validation->set_rules('project_name', '项目团队全称', 'trim|required');
		$this->form_validation->set_rules('project_description', '描述', 'trim');
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $Post_data['project_name'] = $this->input->post('project_name');
        $Post_data['project_description'] = $this->input->post('project_description');
        $Post_data['add_user'] = UID;
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $api = $this->curl->post($system['api_host'].'/project/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
            	//刷新项目团队缓存文件
            	$this->refresh();
                exit(json_encode(array('status' => true, 'message' => '操作成功')));
            } else {
                exit(json_encode(array('status' => false, 'error' => '操作失败')));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
	}

	/**
	 * 常用项目设置
	 *
	 * 用户可以设置常用项目团队
	 */
	public function follow()
	{

	}

	/**
	 * 刷新缓存文件
	 */
	public function refresh()
	{
		$this->load->library('curl');
		$this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $api = $this->curl->get($system['api_host'].'/project/cache');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
            	$this->load->helper('file');
            	return write_file(APPPATH.'/cache/project.cache.php', serialize($output['data']));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
	}
}