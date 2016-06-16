<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 项目模块
 */
class Project extends CI_Controller {

    /**
     * 项目列表
     *
     * 默认显示所有项目
     */
    public function index()
    {
        $data['PAGE_TITLE'] = '项目团队列表';

        //读取系统配置信息
        $this->load->library('curl');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');

        //获取星标项目
        $data['star'] = array();
        if ($this->input->cookie('cits_star_project')) {
            $data['star'] = unserialize($this->encryption->decrypt($this->input->cookie('cits_star_project'))); //从Cookie中获取
        } else {
            //从个人信息中获取
            $api = $this->curl->get($system['api_host'].'/users/row?uid='.UID);
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    if ($output['data']['star_project']) {
                        $this->input->set_cookie('cits_star_project', $this->encryption->encrypt($output['data']['star_project']), 86400*5);
                        $data['star'] =  unserialize($output['data']['star_project']);
                    }
                }
            } else {
                show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
            }
        }

        //读取项目团队数据
        $data['rows'] = array();
        $api_url = $system['api_host'].'/project/rows';
        $data['folder'] = $this->uri->segment(3, 'all');
        if ($data['folder'] == 'my') {
            $api_url = $system['api_host'].'/project/rows?uid='.UID;
        }
        $api = $this->curl->get($api_url);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['rows'] = $output['content']['data'];
            }
        } else {
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('project', $data);
    }

    /**
     * 添加项目
     */
    public function add_ajax()
    {
        //验证输入
        $this->load->library(array('form_validation', 'curl', 'encryption'));
        $this->form_validation->set_rules('project_name', '项目团队全称', 'trim|required');
        $this->form_validation->set_rules('project_description', '描述', 'trim|required');
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
                exit(json_encode(array('status' => true, 'message' => '创建成功')));
            } else {
                exit(json_encode(array('status' => false, 'error' => '创建失败')));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 添加关注项目
     *
     * 关注项目完成后会对Cookie和用户的表进行更新
     */
    public function star_add()
    {
        //验证传值的合法性
        $id = $this->input->post('id');

        //解密并验证数据合法性
        $this->load->library(array('curl', 'encryption'));
        $id = $this->encryption->decrypt($id);
        if (!($id != 0 && ctype_digit($id))) {
            exit(json_encode(array('status' => false, 'error' => '参数格式错误')));
        }

        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $Post_data['id'] = $id;
        $Post_data['uid'] = UID;

        //更新到用户表中
        $api = $this->curl->post($system['api_host'].'/users/star_project_add', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                //更新Cookie
                $this->input->set_cookie('cits_star_project', $this->encryption->encrypt($output['data']), 86400*5);
                exit(json_encode(array('status' => true, 'message' => '添加关注成功')));
            } else {
                exit(json_encode(array('status' => false, 'error' => '添加标记失败'.$output['error'])));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 删除关注项目
     *
     * 关注项目完成后会对Cookie和用户的表进行更新
     */
    public function star_del()
    {
        //验证传值的合法性
        $id = $this->input->post('id');

        //解密并验证数据合法性
        $this->load->library(array('curl', 'encryption'));
        $id = $this->encryption->decrypt($id);
        if (!($id != 0 && ctype_digit($id))) {
            exit(json_encode(array('status' => false, 'error' => '参数格式错误')));
        }
        
        $this->load->library('curl');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $Post_data['id'] = $id;
        $Post_data['uid'] = UID;

        //更新到用户表中
        $api = $this->curl->post($system['api_host'].'/users/star_project_del', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                //更新Cookie
                if ($output['data']) {
                    $this->input->set_cookie('cits_star_project', $this->encryption->encrypt($output['data']), 86400*5);
                } else {
                    $this->load->helper('cookie');
                    delete_cookie('cits_star_project');
                }
                exit(json_encode(array('status' => true, 'message' => '取消关注成功')));
            } else {
                exit(json_encode(array('status' => false, 'error' => '取消关注失败'.$output['error'])));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 刷新项目团队缓存文件
     */
    public function refresh()
    {
        $this->load->library(array('curl', 'encryption'));
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $api = $this->curl->get($system['api_host'].'/project/cache');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->load->helper('file');
                foreach ($output['data'] as $key => $value) {
                    $rows[$value['id']] = $value;
                    $rows[$value['id']]['sha'] = $this->encryption->encrypt($value['id']);
                }
                echo write_file(APPPATH.'/cache/project.cache.php', serialize($rows));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
    
}