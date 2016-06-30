<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 计划模块
 *
 * 先创建计划再创建任务，这样可以保证任务是有序的。
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

        $this->load->helper(array('timediff', 'alphaid'));

        //获取传值
        $curr_plan = $this->input->get('planid', TRUE);
        if ($curr_plan) {
            $curr_plan = alphaid($curr_plan, 1);
            if (!($curr_plan != 0 && ctype_digit($curr_plan))) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':计划id异常');
                show_error('计划id异常', 500, '错误');
            }
        }

        //获取筛选传值
        $data['flow'] = $this->input->get('flow', TRUE);
        $data['type'] = $this->input->get('type', TRUE);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取计划API异常.HTTP_CODE['.$api['httpcode'].']');
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
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务API异常.HTTP_CODE['.$api['httpcode'].']');
                show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
            }

            //根据计划和项目id读取参与计划的人员
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
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取参与人员API异常.HTTP_CODE['.$api['httpcode'].']');
                show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
            }
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('plan', $data);
    }

    /**
     * 添加计划
     */
    public function add_ajax()
    {
        $this->__init();

        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('plan_name', '计划全称', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('plan_description', '描述', 'trim');
        $this->form_validation->set_rules('startime', '开始时间', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('endtime', '结束时间', 'trim|required',
            array('required' => '%s 不能为空')
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //验证结束时间不能小于开始时间
        $endtime = strtotime($this->input->post('endtime'));
        $startime = strtotime($this->input->post('startime'));
        if ($endtime <= $startime) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':结束时间不能小于等于开始时间');
            exit(json_encode(array('status' => false, 'error' => '结束时间不能小于等于开始时间')));
        }

        //写入数据
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $Post_data['project_id'] = $this->_projectid;
        $Post_data['plan_name'] = $this->input->post('plan_name');
        $Post_data['plan_description'] = $this->input->post('plan_description');
        $Post_data['startime'] = $startime;
        $Post_data['endtime'] = $endtime;
        $Post_data['add_user'] = UID;
        $api = $this->curl->post($system['api_host'].'/plan/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                //写入操作日志
                $Post_data_handle['sender'] = UID;
                $Post_data_handle['action'] = '创建';
                $Post_data_handle['target'] = $output['content'];
                $Post_data_handle['target_type'] = 2;
                $Post_data_handle['type'] = 1;
                $Post_data_handle['subject'] = $this->input->post('plan_name');
                $api = $this->curl->post($system['api_host'].'/handle/write', $Post_data_handle);
                if ($api['httpcode'] == 200) {
                    $output = json_decode($api['output'], true);
                    if (!$output['status']) {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.$output['error']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
                }
                exit(json_encode(array('status' => true, 'message' => '创建成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':创建失败');
                exit(json_encode(array('status' => false, 'error' => '创建失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入计划信息API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 计算提测成功率
     *
     * 算法：
     */
    public function rate()
    {

    }
}