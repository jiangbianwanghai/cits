<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 计划模块
 */
class Issue extends CI_Controller {

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

        $data['PAGE_TITLE'] = 'bug列表';

        $this->load->helper('alphaid');

        $folder = $this->uri->segment(3, 'all');
        if (in_array($folder, array('all', 'to_me', 'from_me'))) {
            $folder = $this->uri->segment(3, 'all');
        } else {
            $folder = 'all';
        }
        $data['folder'] = $folder;
        $data['planid'] = $planid = $this->uri->segment(4, 'all');
        $data['flow'] = $this->uri->segment(5, 'all');
        $data['type'] = $this->uri->segment(6, 'all');
        $data['status'] = $this->uri->segment(7, 'all');
        $offset = $this->uri->segment(8, 0);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['level'] = $this->config->item('level', 'extension');
        $data['workflow'] = $this->config->item('workflow', 'extension');
        $data['workflowfilter'] = $this->config->item('workflowfilter', 'extension');
        $data['issuestatusfilter'] = $this->config->item('issuestatusfilter', 'extension');
        $data['issuestatus'] = $this->config->item('issuestatus', 'extension');
        $data['tasktype'] = $this->config->item('tasktype', 'extension');
        $data['tasktypefilter'] = $this->config->item('tasktypefilter', 'extension');
        $config = $this->config->item('pages', 'extension');

        //读取计划list
        $data['planarr'] = array();
        $api = $this->curl->get($system['api_host'].'/plan/rows_by_projectid?id='.$this->_projectid);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                foreach ($output['content']['data'] as $key => $value) {
                    $data['planarr'][alphaid($value['id'])] = $value;
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取计划API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //根据任务ID获取任务信息
        $filter = 'project_id,'.$this->_projectid;
        if ($data['flow'] && $data['flow'] != 'all') {
            $filter .= '|workflow,'.$data['workflowfilter'][$data['flow']]['id'];
        }
        if ($planid && $planid != 'all') {
            $planid = alphaid($planid, 1);
            $filter .= '|plan_id,'.$planid;
        }
        if ($data['status'] && $data['status'] != 'all') {
            $filter .= '|status,'.$data['issuestatusfilter'][$data['status']]['id'];
        }
        if ($data['type'] && $data['type'] != 'all') {
            $filter .= '|type,'.$data['tasktypefilter'][$data['type']];
        }
        if ($folder == 'to_me') {
            $filter .= '|accept_user,'.UID;
        }
        if ($folder == 'from_me') {
            $filter .= '|add_user,'.UID;
        }

        $ids = array();
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/issue/rows?offset='.$offset.'&limit='.$config['per_page'].'&filter='.$filter);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['rows'] = $output['content'];
                foreach ($output['content']['data'] as $key => $value) {
                    $ids[] = $value['id'];
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //读取关注数据
        $data['star'] = array();
        $api = $this->curl->get($system['api_host'].'/star/get_rows_by_type?uid='.UID.'&type=1');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                foreach ($output['content']['data'] as $key => $value) {
                    $data['star'][] = $value['star_id'];
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':关注API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('关注API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //分页
        $this->load->library('pagination');
        $config['total_rows'] = $data['rows']['total'];
        $config['cur_page'] = $offset;
        $config['base_url'] = '/issue/index/'.$folder.'/'.$data['planid'].'/'.$data['flow'].'/'.$data['type'].'/'.$data['status'];
        $this->pagination->initialize($config);
        $data['pages'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        $data['per_page'] = $config['per_page'];

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('issue', $data);
    }

    /**
     * 创建任务面板
     *
     * 创建任务面板，需要先解析计划ID
     */
    public function add()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '创建任务';

        //获取传值
        $planid = $data['planid'] = $this->input->get('planid', TRUE);
        $this->load->helper('alphaid');
        $planid = alphaid($planid, 1);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $data['level'] = $this->config->item('level', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        //根据项目ID获取计划列表
        $api = $this->curl->get($system['api_host'].'/plan/rows_by_projectid?id='.$this->_projectid);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['planRows'] = $output['content']['data'];
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取计划API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('issue_add', $data);
    }

    /**
     * 写入任务信息
     *
     * 操作：写入任务信息，写入操作日志，通知受理人
     */
    public function add_ajax()
    {
        $this->__init();

        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('planid', '所属计划ID', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('type', '类型', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '类型[ '.$this->input->post('type').' ]不符合规则',
                'max_length' => '类型[ '.$this->input->post('type').' ]太长了'
            )
        );
        $this->form_validation->set_rules('level', '优先级', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '优先级[ '.$this->input->post('level').' ]不符合规则',
                'max_length' => '优先级[ '.$this->input->post('level').' ]太长了'
            )
        );
        $this->form_validation->set_rules('issue_name', '任务标题', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('issue_summary', '任务详情', 'trim');
        $this->form_validation->set_rules('issue_url', '相关链接', 'trim');
        $this->form_validation->set_rules('accept_user', '受理人ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '类型[ '.$this->input->post('accept_user').' ]不符合规则'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //解析planid
        $planid = $this->input->post('planid');
        $this->load->helper('alphaid');
        $planid = alphaid($planid, 1);

        //写入数据
        $Post_data['issue_name'] = $this->input->post('issue_name');
        $Post_data['issue_summary'] = $this->input->post('issue_summary');
        $Post_data['project_id'] = $this->_projectid;
        $Post_data['plan_id'] = $planid;
        $Post_data['level'] = $this->input->post('level');
        $Post_data['type'] = $this->input->post('type');
        $Post_data['add_user'] = UID;
        $Post_data['accept_user'] = $this->input->post('accept_user');

        //如果有相关链接就序列化它
        if ($this->input->post('issue_url')) {
            $Post_data['url'] = serialize(array_filter(explode(PHP_EOL, $this->input->post('issue_url'))));
        }
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->post($system['api_host'].'/issue/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {

                //写入操作日志
                $Post_data_handle['sender'] = UID;
                $Post_data_handle['action'] = '创建';
                $Post_data_handle['target'] = $output['content'];
                $Post_data_handle['target_type'] = 3;
                $Post_data_handle['type'] = 1;
                $Post_data_handle['subject'] = $this->input->post('issue_name');
                $api = $this->curl->post($system['api_host'].'/handle/write', $Post_data_handle);
                if ($api['httpcode'] == 200) {
                    $output_handle = json_decode($api['output'], true);
                    if (!$output_handle['status']) {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志异常-'.$output_handle['error']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
                }
                
                //写入受理表
                $Accept_user[1] = UID;
                $Accept_user[2] = $this->input->post('accept_user');
                foreach ($Accept_user as $key => $value) {
                    $Post_data_accept['accept_user'] = $value;
                    $Post_data_accept['issue_id'] = $output['content'];
                    $Post_data_accept['flow'] = $key;
                    $api = $this->curl->post($system['api_host'].'/accept/write', $Post_data_accept);
                    if ($api['httpcode'] == 200) {
                        $output_accept = json_decode($api['output'], true);
                        if (!$output_accept['status']) {
                            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入受理人异常-'.$output_accept['error']);
                        }
                    } else {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入受理人API异常.HTTP_CODE['.$api['httpcode'].']');
                    }
                }

                //写入订阅表
                $Subscription_user = array(UID, $this->input->post('accept_user'));
                foreach ($Subscription_user as $key => $value) {
                    $Post_data_subscription['target'] = $output['content'];
                    $Post_data_subscription['target_type'] = 3;
                    $Post_data_subscription['user'] = $value;
                    $api = $this->curl->post($system['api_host'].'/subscription/write', $Post_data_subscription);
                    if ($api['httpcode'] == 200) {
                        $output_subscription = json_decode($api['output'], true);
                        if (!$output_subscription['status']) {
                            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入订阅异常-'.$output_subscription['error']);
                        }
                    } else {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入订阅API异常.HTTP_CODE['.$api['httpcode'].']');
                    }
                }

                //发送提醒
                $api = $this->curl->get($system['queue_host'].'/?name=notify&opt=put&data='.$output_handle['content'].'|'.$output['content'].'|3|'.UID.'&auth=mypass123'); //格式:log_id|target_id|target_type|senderi_id
                if ($api['httpcode'] == 200) {
                    if ($api['output'] != 'HTTPSQS_PUT_OK') {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入提醒异常-'.$api['output']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':打队列API异常.HTTP_CODE['.$api['httpcode'].']');
                }
                
                $this->load->helper('alphaid');
                exit(json_encode(array('status' => true, 'message' => '创建成功', 'content' => alphaid($output['content']))));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':创建失败：'.$output['error']);
                exit(json_encode(array('status' => false, 'error' => '创建失败：'.$output['error'])));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入任务API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 编辑任务
     *
     * 根据任务ID读取任务信息。
     */
    public function edit()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '编辑任务';

        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $data['issueid'] = $id;
        $id = alphaid($id, 1);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['level'] = $this->config->item('level', 'extension');

        //根据项目ID获取计划列表
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['profile'] = $output['content'];
                $data['profile']['url'] && $data['profile']['url'] = unserialize($data['profile']['url']);
                $data['PAGE_TITLE'] = $data['profile']['issue_name'].' - '.$data['PAGE_TITLE'];
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':编辑的任务不存在.id[ '.$id.' ]');
                show_error('编辑的任务不存在', 500, '错误');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务信息异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('issue_edit', $data);
    }

    /**
     * 写入编辑信息
     *
     * 步骤：写入编辑后的任务信息，写入操作日志，通知关注任务的人任务信息变更了。
     */
    public function edit_ajax()
    {
        $this->__init();

        //解析任务id并验证
        $this->load->helper('alphaid');
        $id = $alphaid = $this->input->post('issueid');
        $id = alphaid($id, 1);
        if (!($id != 0 && ctype_digit($id))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务ID[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '任务id格式错误')));
        }

        //验证任务是否存在
        $this->load->library('form_validation');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $profile = $output['content'];
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务不存在.id[ '.$id.' ]');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务信息异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        } 

        //验证输入
        $this->form_validation->set_rules('type', '类型', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '类型[ '.$this->input->post('type').' ]不符合规则',
                'max_length' => '类型[ '.$this->input->post('type').' ]太长了'
            )
        );
        $this->form_validation->set_rules('level', '优先级', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '优先级[ '.$this->input->post('level').' ]不符合规则',
                'max_length' => '优先级[ '.$this->input->post('level').' ]太长了'
            )
        );
        $this->form_validation->set_rules('issue_name', '任务标题', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('issue_summary', '任务详情', 'trim');
        $this->form_validation->set_rules('issue_url', '相关链接', 'trim');
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $Post_data['issue_id'] = $id;
        $Post_data['issue_name'] = $this->input->post('issue_name');
        $Post_data['issue_summary'] = $this->input->post('issue_summary');
        $Post_data['plan_id'] = $profile['plan_id'];
        $Post_data['level'] = $this->input->post('level');
        $Post_data['type'] = $this->input->post('type');
        $Post_data['last_user'] = UID;
        //如果有相关链接就序列化它
        if ($this->input->post('issue_url')) {
            $Post_data['url'] = serialize(array_filter(explode(PHP_EOL, $this->input->post('issue_url'))));
        }
        $api = $this->curl->post($system['api_host'].'/issue/update', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                //写入操作日志
                $Post_data_handle['sender'] = UID;
                $Post_data_handle['action'] = '编辑';
                $Post_data_handle['target'] = $id;
                $Post_data_handle['target_type'] = 3;
                $Post_data_handle['type'] = 1;
                $Post_data_handle['subject'] = $profile['issue_name'];
                $Post_data_handle['content'] = serialize($profile);
                $api = $this->curl->post($system['api_host'].'/handle/write', $Post_data_handle);
                if ($api['httpcode'] == 200) {
                    $output_handle = json_decode($api['output'], true);
                    if ($output_handle['status']) {
                        //发送通知提醒
                        $api = $this->curl->get($system['queue_host'].'/?name=notify&opt=put&data='.$output_handle['content'].'|'.$id.'|3|'.UID.'&auth=mypass123'); //格式:log_id|target_id|target_type|sender_id
                        if ($api['httpcode'] == 200) {
                            if ($api['output'] != 'HTTPSQS_PUT_OK') {
                                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入通知异常-'.$api['output']);
                            }
                        } else {
                            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':打队列API异常.HTTP_CODE['.$api['httpcode'].']');
                        }
                    } else {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志异常-'.$output_handle['error']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
                }
                exit(json_encode(array('status' => true, 'message' => '更新成功', 'content' => $alphaid)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':更新失败：'.$output['error']);
                exit(json_encode(array('status' => false, 'error' => '更新失败：'.$output['error'])));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':更新任务API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 删除任务
     *
     * 为了保证数据的完整性，防止误删采取软删除。
     */
    public function del()
    {
        $this->__init();

    }

    /**
     * 任务详情
     *
     * 显示任务详情，读取相关的提测列表，读取相关的BUG列表。
     */
    public function view()
    {
        $this->__init();

        //解析url传值
        $this->load->helper(array('alphaid', 'timediff'));
        $id = $this->uri->segment(3, 0);
        $data['issueid'] = $id;
        $id = alphaid($id, 1);

        //根据id获取任务信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $data['level'] = $this->config->item('level', 'extension');
        $data['env'] = $this->config->item('env', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务不存在。任务id:['.$id.']');
                show_error('任务不存在', 500, '错误');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }
        $data['issue_profile'] = $output['content'];

        $data['PAGE_TITLE'] = $data['issue_profile']['issue_name'].' - 任务详情';

        $is_read = $this->input->get('is_read', TRUE);
        if ($is_read == 'yes') {
            $Post_data['id'] = $id;
            $api = $this->curl->post($system['api_host'].'/notify/change_read', $Post_data);
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if (!$output['status']) {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':标记提醒已读异常-'.$output['error']);
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':标记提醒已读API异常.HTTP_CODE['.$api['httpcode'].']');
            }
        }

        //读取相关提测记录
        $data['commit'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/commit/get_rows_by_issue?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['commit'] = $output['content'];
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取提测列表API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //计算提测成功率
        $data['rate'] = '无提测数据用于计算';
        $testIdArr = array();
        if ($data['commit']['total']) {
            foreach ($data['commit']['data'] as $key => $value) {
                if (isset($testIdArr[$value['repos_id']])) {
                    $testIdArr[$value['repos_id']] += 1;
                } else {
                    $testIdArr[$value['repos_id']] = 1;
                }
            }
            $maxTest = max($testIdArr);
            $data['rate'] = sprintf("%.2f", 1/$maxTest);
        }

        //获取相关BUG记录
        $data['bug'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/bug/get_rows_by_issue?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['bug'] = $output['content'];
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取bug列表API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //验证BUG是否都已经处理
        $data['fixedFlag'] = 1;
        if ($data['commit']['total']) {
            foreach ($data['commit']['data'] as $key => $value) {
                if ($value['state'] == '0' || $value['state'] == '1') {
                    $data['fixedFlag'] = 0;
                    break;
                }
            }
        }

        //读取所属计划
        $data['plan_profile'] = array();
        $api = $this->curl->get($system['api_host'].'/plan/profile?id='.$data['issue_profile']['plan_id']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['plan_profile'] = $output['content'];
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取计划详情API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //读取受理信息
        $data['accept_user'] = array();
        $api = $this->curl->get($system['api_host'].'/accept/get_rows_by_issue?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                foreach ($output['content']['data'] as $key => $value) {
                    $value['flow'] > 0 && $data['accept_user'][$value['flow']] = $value;
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取受理人员信息API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }
        
        //读取任务相关的评论
        $data['comment'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/comment/get_rows_by_id?id='.$id.'&type=issue');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['comment'] = $output['content'];
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取受理人员信息API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('issue_view', $data);

    }

    /**
     * 添加评论
     */
    public function coment_add_ajax() {

        $this->__init();

        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('content', '计划全称', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('issue_id', '任务id', 'trim|required',
            array('required' => '%s 不能为空')
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }
        $this->load->helper('alphaid');
        $id = $this->input->post('issue_id');
        $id = alphaid($id, 1);

        //根据id获取任务信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务不存在。任务id:['.$id.']');
                exit(json_encode(array('status' => false, 'error' => '任务不存在')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //写入评论
        $Post_data['id'] = $id;
        $Post_data['content'] = $this->input->post('content');
        $Post_data['type'] = 'issue';
        $Post_data['add_user'] = UID;
        $api = $this->curl->post($system['api_host'].'/comment/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output_comment = json_decode($api['output'], true);
            if (!$output_comment['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':评论发表失败');
                exit(json_encode(array('status' => false, 'error' => '评论发表失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入评论API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '写入评论API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        if (UID == $output['content']['accept_user']) {
            $usertype = '当前受理人';
        } else {
            $usertype = '路人甲';
        }
        //载入用户缓存文件
        $users = array();
        if (file_exists(APPPATH.'cache/user.cache.php')) {
          $users = file_get_contents(APPPATH.'cache/user.cache.php');
          $users = unserialize($users);
        }
        $callBack = array(
            'status' => true,
            'message' => array(
                'content'=>html_entity_decode($this->input->post('content')),
                'realname'=>$users[UID]['realname'],
                'avatar'=> AVATAR_HOST.'/'.$users[UID]['username'].'.jpg',
                'addtime' => '1秒',
                'usertype' => $usertype
            )
        );
        
        //写操作日志
        $Post_data_handle['sender'] = UID;
        $Post_data_handle['action'] = '评论';
        $Post_data_handle['target'] = $id;
        $Post_data_handle['target_type'] = 3;
        $Post_data_handle['type'] = 1;
        $Post_data_handle['subject'] = $output['content']['issue_name'];
        $Post_data_handle['content'] = $output_comment['content'];
        $api = $this->curl->post($system['api_host'].'/handle/write', $Post_data_handle);
        if ($api['httpcode'] == 200) {
            $output_handle = json_decode($api['output'], true);
            if ($output_handle['status']) {
                //发送通知提醒
                $api = $this->curl->get($system['queue_host'].'/?name=notify&opt=put&data='.$output_handle['content'].'|'.$id.'|3|'.UID.'&auth=mypass123'); //格式:log_id|target_id|target_type|sender_id
                if ($api['httpcode'] == 200) {
                    if ($api['output'] != 'HTTPSQS_PUT_OK') {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入通知异常-'.$api['output']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':打队列API异常.HTTP_CODE['.$api['httpcode'].']');
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志异常-'.$output_handle['error']);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
        }
        echo json_encode($callBack);
    }

    /**
     * 更改工作流
     */
    public function change_flow()
    {
        //获取传入参数
        $id = $this->uri->segment(3, 0);
        $flow = $this->uri->segment(4, 0);
        $this->load->helper('alphaid');
        $id = alphaid($id, 1);

        //验证工作流参数合法性
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $workflowfilter = $this->config->item('workflowfilter', 'extension');
        if (!isset($workflowfilter[$flow])) {
            exit(json_encode(array('status' => false, 'message' => '参数不合法')));
        }

        //验证ID合法性
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务不存在。任务id:['.$id.']');
                exit(json_encode(array('status' => false, 'error' => '任务不存在')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //更改工作流
        $Post_data['uid'] = UID;
        $Post_data['id'] = $id;
        $Post_data['flow'] = $workflowfilter[$flow]['id'];
        $api = $this->curl->post($system['api_host'].'/issue/change_flow', $Post_data);
        if ($api['httpcode'] == 200) {
            $output_change = json_decode($api['output'], true);
            if (!$output_change['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':更改工作流失败');
                exit(json_encode(array('status' => false, 'error' => '更改工作流失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':更改工作流API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '更改工作流API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //写操作日志
        $Post_data_handle['sender'] = UID;
        $Post_data_handle['action'] = '变更';
        $Post_data_handle['target'] = $id;
        $Post_data_handle['target_type'] = 3;
        $Post_data_handle['type'] = 1;
        $Post_data_handle['subject'] = $output['content']['issue_name'];
        $Post_data_handle['content'] = $workflowfilter[$flow]['name'];
        $api = $this->curl->post($system['api_host'].'/handle/write', $Post_data_handle);
        if ($api['httpcode'] == 200) {
            $output_handle = json_decode($api['output'], true);
            if ($output_handle['status']) {
                //发送通知提醒
                $api = $this->curl->get($system['queue_host'].'/?name=notify&opt=put&data='.$output_handle['content'].'|'.$id.'|3|'.UID.'&auth=mypass123'); //格式:log_id|target_id|target_type|sender_id
                if ($api['httpcode'] == 200) {
                    if ($api['output'] != 'HTTPSQS_PUT_OK') {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入通知异常-'.$api['output']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':打队列API异常.HTTP_CODE['.$api['httpcode'].']');
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志异常-'.$output_handle['error']);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
        }
        exit(json_encode(array('status' => true, 'message' => '更改成功')));
    }

    /**
     * 读取任务相关的操作日志
     */
    public function log_list()
    {
        //获取传入参数
        $id = $this->uri->segment(3, 0);
        $this->load->helper(array('alphaid', 'timediff'));
        $id = alphaid($id, 1);

        //验证工作流参数合法性
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');

        //验证ID合法性
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务不存在。任务id:['.$id.']');
                exit(json_encode(array('status' => false, 'error' => '任务不存在')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        $users = array();
        if (file_exists(APPPATH.'cache/user.cache.php')) {
          $users = file_get_contents(APPPATH.'cache/user.cache.php');
          $users = unserialize($users);
        }

        //读取操作日志
        $api = $this->curl->get($system['api_host'].'/handle/get_rows?id='.$id.'&type=issue');
        if ($api['httpcode'] == 200) {
            $output_log = json_decode($api['output'], true);
            if (!$output_log['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':操作日志不存在');
                exit(json_encode(array('status' => false, 'error' => '操作日志不存在')));
            } else {
                $list['total'] = $output_log['content']['total'];
                foreach ($output_log['content']['data'] as $key => $value) {
                    $list['comment'][$key]['realname'] = $users[$value['sender']]['realname'];
                    $list['comment'][$key]['avatar'] = AVATAR_HOST.'/'.$users[$value['sender']]['username'].'.jpg';
                    $list['comment'][$key]['friendtime'] = timediff($value['add_time'], time());
                    $list['comment'][$key]['content'] = $value['subject'];
                    $subject = '给你';
                    $subject = $value['action'].'了';
                    if ($value['target_type'] == '3') {
                        $subject .= '任务';
                        $url = '/issue/view/'.alphaid($value['target']);
                    }
                    if ($value['action'] == '评论') {
                        $url .= '#comment-'.alphaid($value['content']);
                    }
                    $end = '';
                    if ($value['action'] == '变更') {
                        $end = ' 的工作流状态为 '.$value['content'];
                    }
                    $list['comment'][$key]['content'] = $subject.' #<a href="'.$url.'">'.$value['subject'].'</a>'.$end;
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '读取操作日志API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        exit(json_encode($list));
    }

    public function change_accept()
    {
        //获取参数
        $id = $this->uri->segment(3, 0);
        $uid = $this->input->get("value", TRUE);
        $role = $this->input->get("pk", TRUE);
        $this->load->helper(array('alphaid', 'timediff'));
        $id = alphaid($id, 1);
        $uid = alphaid($uid, 1);

        //验证ID合法性
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务不存在。任务id:['.$id.']');
                exit(json_encode(array('status' => false, 'error' => '任务不存在')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '读取任务详情API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //载入用户缓存文件
        $users = array();
        if (file_exists(APPPATH.'cache/user.cache.php')) {
          $users = file_get_contents(APPPATH.'cache/user.cache.php');
          $users = unserialize($users);
        }

        //写入受理表
        $Post_data_accept['accept_user'] = $uid;
        $Post_data_accept['issue_id'] = $id;
        $Post_data_accept['flow'] = $role;
        $api = $this->curl->post($system['api_host'].'/accept/write', $Post_data_accept);
        if ($api['httpcode'] == 200) {
            $output_accept = json_decode($api['output'], true);
            if (!$output_accept['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入受理人异常-'.$output_accept['error']);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入受理人API异常.HTTP_CODE['.$api['httpcode'].']');
        }

        //写入订阅表
        $Post_data_subscription['target'] = $id;
        $Post_data_subscription['target_type'] = 3;
        $Post_data_subscription['user'] = $uid;
        $api = $this->curl->post($system['api_host'].'/subscription/write', $Post_data_subscription);
        if ($api['httpcode'] == 200) {
            $output_subscription = json_decode($api['output'], true);
            if (!$output_subscription['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入订阅异常-'.$output_subscription['error']);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入订阅API异常.HTTP_CODE['.$api['httpcode'].']');
        }

        //写操作日志
        $Post_data_handle['sender'] = UID;
        $Post_data_handle['action'] = '指派';
        $Post_data_handle['target'] = $id;
        $Post_data_handle['target_type'] = 3;
        $Post_data_handle['type'] = 1;
        $Post_data_handle['subject'] = $output['content']['issue_name'];
        $Post_data_handle['content'] = $users[$uid]['realname'];
        $api = $this->curl->post($system['api_host'].'/handle/write', $Post_data_handle);
        if ($api['httpcode'] == 200) {
            $output_handle = json_decode($api['output'], true);
            if ($output_handle['status']) {
                
                //发送提醒
                if ($uid != UID) {
                    $Post_data_notify['user'] = $uid;
                    $Post_data_notify['log_id'] = $output_handle['content'];
                    $api_notify = $this->curl->post($system['api_host'].'/notify/write', $Post_data_notify);
                    if ($api_notify['httpcode'] == 200) {
                        $output_notify = json_decode($api_notify['output'], true);
                        if (!$output_notify['status']) {
                            echo '写入通知异常-'.$output_notify['error'].PHP_EOL;
                        }
                    } else {
                        echo '写入提醒API异常.HTTP_CODE['.$api_notify['httpcode'].']'.PHP_EOL;
                    }
                }

            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志异常-'.$output_handle['error']);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
        }

        echo 1;
    }

    /**
     * 关注BUG
     */
    public function star_add()
    {
        $this->__init();

        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $id = alphaid($id, 1);

        //写入数据
        $Post_data['add_user'] = UID;
        $Post_data['star_id'] = $id;
        $Post_data['star_type'] = 1;

        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->post($system['api_host'].'/star/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                exit(json_encode(array('status' => true, 'message' => '关注成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':关注失败');
                exit(json_encode(array('status' => false, 'error' => '关注失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 取消关注bug
     */
    public function star_del()
    {
        $this->__init();

        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $id = alphaid($id, 1);

        //写入数据
        $Post_data['add_user'] = UID;
        $Post_data['star_id'] = $id;
        $Post_data['star_type'] = 1;

        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->post($system['api_host'].'/star/del', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                exit(json_encode(array('status' => true, 'message' => '取消关注成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':取消关注失败');
                exit(json_encode(array('status' => false, 'error' => '取消关注失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 关注bug列表
     */
    public function star()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '关注的bug列表';
        $data['folder'] = 'all';
        $offset = $data['offset'] = $this->uri->segment(3, 0);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['level'] = $this->config->item('level', 'extension');
        $data['workflow'] = $this->config->item('workflow', 'extension');
        $data['issuestatus'] = $this->config->item('issuestatus', 'extension');
        $data['tasktype'] = $this->config->item('tasktype', 'extension');
        $config = $this->config->item('pages', 'extension');
        
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/issue/star?uid='.UID.'&offset='.$offset.'&limit='.$config['per_page']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['rows'] = $output['content'];
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //分页
        $this->load->library('pagination');
        $config['total_rows'] = $data['rows']['total'];
        $config['cur_page'] = $offset;
        $config['base_url'] = '/issue/star/';
        $this->pagination->initialize($config);
        $data['pages'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        $data['per_page'] = $config['per_page'];

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->helper('alphaid');

        $this->load->view('issue', $data);
    }
}