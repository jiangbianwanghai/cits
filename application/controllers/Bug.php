<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Bug模块
 */
class Bug extends CI_Controller {

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
     * Bug列表
     *
     * 默认显示所有项目
     */
    public function index()
    {
        $this->__init();

        $data['PAGE_TITLE'] = 'bug列表';

        $folder = $data['folder'] = $this->uri->segment(3, 'all');
        $state = $data['state'] = $this->uri->segment(4, 'all');
        $status = $data['status'] = $this->uri->segment(5, 'all');
        $offset = $data['offset'] = $this->uri->segment(6, 0);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['level'] = $this->config->item('level', 'extension');
        $data['bugflow'] = $this->config->item('bugflow', 'extension');
        $data['bugflowfilter'] = $this->config->item('bugflowfilter', 'extension');
        $data['bugstatusfilter'] = $this->config->item('bugstatusfilter', 'extension');
        $data['bugstatus'] = $this->config->item('bugstatus', 'extension');
        $config = $this->config->item('pages', 'extension');

        //根据任务ID获取任务信息
        $filter = 'project_id,'.$this->_projectid;
        if ($state && $state != 'all') {
            $filter .= '|state,'.$data['bugflowfilter'][$state]['id'];
        }
        if ($status && $status != 'all') {
            $filter .= '|status,'.$data['bugstatusfilter'][$status]['id'];
        }
        if ($folder == 'to_me') {
            $filter .= '|accept_user,'.UID;
        }
        if ($folder == 'from_me') {
            $filter .= '|add_user,'.UID;
        }
        
        $ids = array();
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/bug/rows?offset='.$offset.'&limit='.$config['per_page'].'&filter='.$filter);
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
        $api = $this->curl->get($system['api_host'].'/star/get_rows_by_type?uid='.UID.'&type=3');
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
        $config['base_url'] = '/bug/index/'.$folder.'/'.$state.'/'.$status;
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

        $this->load->view('bug', $data);
    }

    /**
     * 创建bug面板
     *
     * 创建bug面板，需要先解析任务id
     */
    public function add()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '创建BUG';

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

        //根据任务ID获取任务信息
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['profile'] = $output['content'];
                $data['profile']['url'] && $data['profile']['url'] = unserialize($data['profile']['url']);
                $data['PAGE_TITLE'] = $data['profile']['issue_name'].' - '.$data['PAGE_TITLE'];
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务不存在.id[ '.$id.' ]');
                show_error('任务不存在', 500, '错误');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务信息API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //根据任务id获取受理人
        $data['dev_user'] = 0;
        $api = $this->curl->get($system['api_host'].'/accept/get_rows_by_issue?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                if ($output['content']['data']) {
                    foreach ($output['content']['data'] as $key => $value) {
                        if ($value['flow'] == 2) {
                            $data['dev_user'] = $value['accept_user'];
                            break;
                        }
                    }

                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务还未进入执行阶段.id[ '.$id.' ]');
                show_error('任务还未进入执行阶段', 500, '错误');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取受理人API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        $this->load->view('bug_add', $data);
    }

    /**
     * 写入BUG信息
     *
     * 操作：写入bug信息，写入操作日志，通知受理人
     */
    public function add_ajax()
    {
        $this->__init();

        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('issue_id', '所属任务ID', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('level', '优先级', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '优先级[ '.$this->input->post('level').' ]不符合规则',
                'max_length' => '优先级[ '.$this->input->post('level').' ]太长了'
            )
        );
        $this->form_validation->set_rules('subject', '标题', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('content', '描述', 'trim');
        $this->form_validation->set_rules('accept_user', '受理人ID', 'trim|required',
            array('required' => '%s 不能为空')
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        $this->load->helper('alphaid');
        $id = alphaid($this->input->post('issue_id'), 1);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        //根据任务ID获取任务信息
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

        //写入数据
        $issue_name = $output['content']['issue_name'];
        $Post_data['subject'] = $this->input->post('subject');
        $Post_data['content'] = $this->input->post('content');
        $Post_data['project_id'] = $output['content']['project_id'];
        $Post_data['plan_id'] = $output['content']['plan_id'];
        $Post_data['issue_id'] = $id;
        $Post_data['level'] = $this->input->post('level');
        $Post_data['add_user'] = UID;
        $Post_data['accept_user'] = alphaid($this->input->post('accept_user'), 1);
        $api = $this->curl->post($system['api_host'].'/bug/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {

                //写入操作日志
                $Post_data_handle['sender'] = UID;
                $Post_data_handle['action'] = '反馈';
                $Post_data_handle['target'] = $id;
                $Post_data_handle['target_type'] = 3;
                $Post_data_handle['type'] = 1;
                $Post_data_handle['subject'] = $issue_name;
                $Post_data_handle['content'] = $output['content'];
                $api = $this->curl->post($system['api_host'].'/handle/write', $Post_data_handle);
                if ($api['httpcode'] == 200) {
                    $output_handle = json_decode($api['output'], true);
                    if (!$output_handle['status']) {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志异常-'.$output_handle['error']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
                }

                //发送提醒
                $api = $this->curl->get($system['queue_host'].'/?name=notify&opt=put&data='.$output_handle['content'].'|'.$id.'|3|'.UID.'&auth=mypass123'); //格式:log_id|target_id|target_type|senderi_id
                if ($api['httpcode'] == 200) {
                    if ($api['output'] != 'HTTPSQS_PUT_OK') {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入提醒异常-'.$api['output']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':打队列API异常.HTTP_CODE['.$api['httpcode'].']');
                }
                exit(json_encode(array('status' => true, 'message' => '创建成功', 'content' => alphaid($id))));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':创建失败：'.$output['error']);
                exit(json_encode(array('status' => false, 'error' => '创建失败：'.$output['error'])));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入任务API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    public function view()
    {
        $this->__init();

        $data['PAGE_TITLE'] = 'BUG详情';

        //解析url传值
        $this->load->helper(array('alphaid', 'timediff'));
        $id = $this->uri->segment(3, 0);
        $data['bugid'] = $id;
        $id = alphaid($id, 1);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['level'] = $this->config->item('level', 'extension');

        //根据bug ID获取bug信息
        $api = $this->curl->get($system['api_host'].'/bug/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['profile'] = $output['content'];
                $data['PAGE_TITLE'] = $data['profile']['subject'].' - '.$data['PAGE_TITLE'];
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':bug不存在.id[ '.$id.' ]');
                show_error('bug不存在', 500, '错误');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取bug信息API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //根据任务 ID获取任务信息
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$data['profile']['issue_id']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['issue'] = $output['content'];
                $data['PAGE_TITLE'] = $data['profile']['subject'].' - '.$data['PAGE_TITLE'];
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务不存在.id[ '.$id.' ]');
                show_error('任务不存在', 500, '错误');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务信息API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //读取任务相关的评论
        $data['comment'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/comment/get_rows_by_id?id='.$id.'&type=bug');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['comment'] = $output['content'];
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取相关评论信息API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('bug_view', $data);
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
        $this->form_validation->set_rules('bug_id', '任务id', 'trim|required',
            array('required' => '%s 不能为空')
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }
        $this->load->helper('alphaid');
        $id = $this->input->post('bug_id');
        $id = alphaid($id, 1);

        //根据id获取任务信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/bug/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':bug不存在。任务id:['.$id.']');
                exit(json_encode(array('status' => false, 'error' => 'bug不存在')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取bug详情API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '读取bug详情API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //写入评论
        $Post_data['id'] = $id;
        $Post_data['content'] = $this->input->post('content');
        $Post_data['type'] = 'bug';
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
        $Post_data_handle['target_type'] = 4;
        $Post_data_handle['type'] = 1;
        $Post_data_handle['subject'] = $output['content']['subject'];
        $Post_data_handle['content'] = $output_comment['content'];
        $api = $this->curl->post($system['api_host'].'/handle/write', $Post_data_handle);
        if ($api['httpcode'] == 200) {
            $output_handle = json_decode($api['output'], true);
            if ($output_handle['status']) {
                //发送通知提醒
                $api = $this->curl->get($system['queue_host'].'/?name=notify&opt=put&data='.$output_handle['content'].'|'.$id.'|4|'.UID.'&auth=mypass123'); //格式:log_id|target_id|target_type|sender_id
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
     * bug 删除
     */
    public function del()
    {
        $this->__init();

        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $id = alphaid($id, 1);

        //根据id获取任务信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/bug/del?id='.$id.'&user='.UID);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                exit(json_encode(array('status' => true, 'message' => '删除成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':删除失败');
                exit(json_encode(array('status' => false, 'error' => '删除失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * bug 关闭
     */
    public function close()
    {
        $this->__init();

        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $id = alphaid($id, 1);

        //根据id获取任务信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/bug/close?id='.$id.'&user='.UID);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                exit(json_encode(array('status' => true, 'message' => '关闭成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':关闭失败');
                exit(json_encode(array('status' => false, 'error' => '关闭失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * bug 开启
     */
    public function open()
    {
        $this->__init();

        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $id = alphaid($id, 1);

        //根据id获取任务信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/bug/open?id='.$id.'&user='.UID);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                exit(json_encode(array('status' => true, 'message' => '开启成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':开启失败');
                exit(json_encode(array('status' => false, 'error' => '开启失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
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
        $Post_data['star_type'] = 3;

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
        $Post_data['star_type'] = 3;

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
        $data['bugflow'] = $this->config->item('bugflow', 'extension');
        $data['bugflowfilter'] = $this->config->item('bugflowfilter', 'extension');
        $data['bugstatusfilter'] = $this->config->item('bugstatusfilter', 'extension');
        $data['bugstatus'] = $this->config->item('bugstatus', 'extension');
        $config = $this->config->item('pages', 'extension');
        
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/bug/star?uid='.UID.'&offset='.$offset.'&limit='.$config['per_page']);
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
        $config['base_url'] = '/bug/star/';
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

        $this->load->view('bug', $data);
    }
}