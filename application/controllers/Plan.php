<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 计划模块
 */
class Plan extends CI_Controller {

    /**
     * 项目ID
     */
    private $_projectid = 0;

    /**
     * 初始化，获取项目id
     */
    private function __init()
    {
        $projectid = $this->input->cookie('cits_curr_project');
        if ($projectid) {
            $projectid = $this->encryption->decrypt($projectid);
            if (!($projectid != 0 && ctype_digit($projectid))) {
                show_error('项目团队ID异常，请 <a href="/">返回首页</a> 重新选择项目团队', 500, '错误');
            } else {
                $this->_projectid = $projectid;
            }
        } else {
            show_error('无法获取项目团队信息（计划，任务，提测，bug四个模块操作前先在页面顶部选择项目），请 <a href="/">返回首页</a> 选择项目团队', 500, '错误');
        }
    }

    /**
     * 计划列表
     *
     * 默认显示所有项目
     */
    public function index()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '计划列表';

        //获取传值
        $curr_plan = $this->input->get('planid', TRUE);
        if ($curr_plan) {
            $curr_plan = $this->encryption->decrypt($curr_plan);
            if (!($curr_plan != 0 && ctype_digit($curr_plan))) {
                show_error('计划id异常', 500, '错误');
            }
        }

        $data['flow'] = $this->input->get('flow', TRUE);
        $data['type'] = $this->input->get('type', TRUE);

        //读取系统配置信息
        $this->load->library('curl');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $data['level'] = $this->config->item('level', 'extension');
        $data['workflow'] = $this->config->item('workflow', 'extension');
        $data['workflowfilter'] = $this->config->item('workflowfilter', 'extension');
        $data['tasktype'] = $this->config->item('tasktype', 'extension');

        //根据项目ID获取计划列表
        $data['planFolder'] = array();
        $data['curr_plan']['id'] = $data['curr_plan']['sha'] = 0;
        $data['curr_plan']['endtime'] = 0;
        $api = $this->curl->get($system['api_host'].'/plan/rows_by_projectid?id='.$this->_projectid);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['planFolder'] = $output['content']['data'];
                //当没有指定当前计划时，默认选中最后一个为当前计划
                if (!$curr_plan) {
                    foreach ($output['content']['data'] as $key => $value) {
                        $data['curr_plan'] = $value;
                        $data['curr_plan']['sha'] = $this->encryption->encrypt($value['id']);
                        break;
                    }
                } else {
                    foreach ($output['content']['data'] as $key => $value) {
                        if ($curr_plan == $value['id']) {
                            $data['curr_plan'] = $value;
                            $data['curr_plan']['sha'] = $this->encryption->encrypt($value['id']);
                            break;
                        }
                    }
                }
            }
        } else {
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }
        
        $data['rows'] = $data['accept_users'] = array();
        $data['total'] = 0;
        if ($data['curr_plan']) {
            //根据计划和项目id读取任务列表
            $api = $this->curl->get($system['api_host'].'/issue/rows_by_plan?projectid='.$this->_projectid.'&planid='.$data['curr_plan']['id'].'&offset=0');
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    $data['rows'] = $output['content']['data'];
                    $data['total'] = $output['content']['total'];
                }
            } else {
                show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
            }

            //根据计划和项目id读取任务列表
            $api = $this->curl->get($system['api_host'].'/accept/users_by_plan?projectid='.$this->_projectid.'&planid='.$data['curr_plan']['id'].'&offset=0');
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    foreach ($output['content'] as $key => $value) {
                        $accept_users[] = $value['accept_user'];
                    }
                    $data['accept_users'] = array_unique($accept_users);
                }
            } else {
                show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
            }
        }

        $this->load->helper('timediff');

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('plan', $data);
    }
}