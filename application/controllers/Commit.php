<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 提测模块
 */
class Commit extends CI_Controller {

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
     * 提测列表
     *
     * 默认显示所有项目
     */
    public function index()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '提测列表';

        $folder = $this->uri->segment(3, 'all');
        if (in_array($folder, array('all', 'to_me', 'from_me'))) {
            $folder = $this->uri->segment(3, 'all');
        } else {
            $folder = 'all';
        }
        $data['folder'] = $folder;
        $state = $data['state'] = $this->uri->segment(4, 'all');
        $status = $data['status'] = $this->uri->segment(5, 'all');
        $offset = $data['offset'] = $this->uri->segment(6, 0);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['commitstatus'] = $this->config->item('commitstatus', 'extension');
        $data['commitstatusfilter'] = $this->config->item('commitstatusfilter', 'extension');
        $data['commitstatefilter'] = $this->config->item('commitstatefilter', 'extension');
        $data['commitstate'] = $this->config->item('commitstate', 'extension');
        $config = $this->config->item('pages', 'extension');

        //根据任务ID获取任务信息
        $filter = 'project_id,'.$this->_projectid;
        if ($state && $state != 'all') {
            $filter .= '|state,'.$data['commitstatefilter'][$state]['id'];
        }
        if ($status && $status != 'all') {
            $filter .= '|status,'.$data['commitstatusfilter'][$status]['id'];
        }
        if ($folder == 'to_me') {
            $filter .= '|accept_user,'.UID;
        }
        if ($folder == 'from_me') {
            $filter .= '|add_user,'.UID;
        }
        
        $ids = array();
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/commit/rows?offset='.$offset.'&limit='.$config['per_page'].'&filter='.$filter);
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
        $config['base_url'] = '/commit/index/'.$folder.'/'.$state.'/'.$status;
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

        $this->load->view('commit', $data);
    }

    /**
     * 创建任务面板
     *
     * 创建任务面板，需要先解析计划ID
     */
    public function add()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '创建提测记录';

        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $data['issueid'] = $id;
        $id = alphaid($id, 1);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $data['level'] = $this->config->item('level', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        //根据项目ID获取计划列表
        $api = $this->curl->get($system['api_host'].'/issue/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['profile'] = $output['content'];
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':编辑的任务不存在.id[ '.$id.' ]');
                show_error('编辑的任务不存在', 500, '错误');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务信息异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        $this->load->view('commit_add', $data);
    }

    /**
     * 写入提测信息
     *
     * 操作：写入提测信息，写入操作日志，通知受理人
     */
    public function add_ajax()
    {
        $this->__init();

        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('repos_id', '代码库id', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '代码库id[ '.$this->input->post('repos_id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('br', '分支', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('commit', 'commitid', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('test_summary', '提测说明', 'trim');
        $this->form_validation->set_rules('issue_id', '任务id', 'trim|required',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '任务id[ '.$this->input->post('issue_id').' ]不符合规则'
            )
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

        //写入数据
        $Post_data['project_id'] = $output['content']['project_id'];
        $Post_data['plan_id'] = $output['content']['plan_id'];
        $Post_data['issue_id'] = $id;
        $Post_data['repos_id'] = $this->input->post('repos_id');
        $Post_data['br'] = $this->input->post('br');
        $Post_data['test_flag'] = $this->input->post('commit');
        $Post_data['add_user'] = UID;
        $Post_data['test_summary'] = $this->input->post('test_summary');

        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->post($system['api_host'].'/commit/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output_commit = json_decode($api['output'], true);
            if ($output_commit['status']) {
                $repos = array();
                if (file_exists(APPPATH.'cache/repos.cache.php')) {
                  $repos = file_get_contents(APPPATH.'cache/repos.cache.php');
                  $repos = unserialize($repos);
                }
                //写入操作日志
                $Post_data_handle['sender'] = UID;
                $Post_data_handle['action'] = '提测';
                $Post_data_handle['target'] = $output['content']['id'];
                $Post_data_handle['target_type'] = 3;
                $Post_data_handle['type'] = 1;
                $Post_data_handle['subject'] = $output['content']['issue_name'];
                $Post_data_handle['content'] = $repos[$this->input->post('repos_id')]['repos_name'].'@'.$this->input->post('br').'#'.$this->input->post('commit');
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
                $api = $this->curl->get($system['queue_host'].'/?name=notify&opt=put&data='.$output_handle['content'].'|'.$output['content']['id'].'|3|'.UID.'&auth=mypass123'); //格式:log_id|target_id|target_type|senderi_id
                if ($api['httpcode'] == 200) {
                    if ($api['output'] != 'HTTPSQS_PUT_OK') {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入提醒异常-'.$api['output']);
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':打队列API异常.HTTP_CODE['.$api['httpcode'].']');
                }
                
                exit(json_encode(array('status' => true, 'message' => '提交成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':提交失败：'.$output['error']);
                exit(json_encode(array('status' => false, 'error' => '提交失败：'.$output['error'])));
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

        $data['PAGE_TITLE'] = '编辑提测';

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
     * 获取代码库的分支
     */
    public function getbr() 
    {
        set_time_limit(0);
        //获取输入的参数
        $id = $this->uri->segment(3, 0);

        //载入代码库缓存文件
        $repos = array();
        if (file_exists(APPPATH.'cache/repos.cache.php')) {
          $repos = file_get_contents(APPPATH.'cache/repos.cache.php');
          $repos = unserialize($repos);
        }

        //验证输入的参数合法性
        if (!isset($repos[$id]))
            exit(json_encode(array('status' => false, 'error' => '输入的参数有误')));

        //组合队列url
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $sqsUrl = $system['queue_host']."/?name=tree&opt=put&data=";
        $sqsUrl .= $id."&auth=mypass123";

        //发送消息给后端worker
        $this->load->library('curl');
        $res = $this->curl->get($sqsUrl);
        if ($res['httpcode'] != 200)
            exit(json_encode(array('status' => false, 'error' => '消息队列出现异常')));

        $cacheFile = APPPATH.'cache/repos_'.$id.'_tree.log';
        //循环验证缓存文件是否生成
        while(1) {
            usleep(500);
            if (file_exists($cacheFile)) {
                break;
            }
        }

        $con = file_get_contents($cacheFile);
        if ($con) {
            $conArr = unserialize($con);
        }

        if (!$conArr)
            exit(json_encode(array('status' => false, 'error' => '格式异常')));

        $str = '';
        foreach ($conArr as $key => $value) {
            $str .='<option value="'.$value.'">'.$value.'</option>';
        }
        $callBack = array('status' => true, 'output' => $str);
        echo json_encode($callBack);
    }

    /**
     * 获取代码库的提交记录
     */
    public function getcommit() 
    {
        set_time_limit(0);
        //获取输入的参数
        $id = $this->input->post('id');
        $branch = $this->input->post('branch');
        //载入代码库缓存文件
        $repos = array();
        if (file_exists(APPPATH.'cache/repos.cache.php')) {
          $repos = file_get_contents(APPPATH.'cache/repos.cache.php');
          $repos = unserialize($repos);
        }

        //验证输入的参数合法性
        if (!isset($repos[$id]))
            exit(json_encode(array('status' => false, 'error' => '输入的参数有误')));

        //组合队列url
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $sqsUrl = $system['queue_host']."/?name=commit&opt=put&data=";
        $sqsUrl .= $id."|".$branch."&auth=mypass123";
        //echo $sqsUrl;

        //发送消息给后端worker
        $this->load->library('curl');
        $res = $this->curl->get($sqsUrl);
        if ($res['httpcode'] != 200)
            exit(json_encode(array('status' => false, 'error' => '消息队列出现异常')));

        $cacheFile = APPPATH."cache/repos_".$id."_".str_replace('/', '_', $branch)."_commit.xml";
        //循环验证缓存文件是否生成
        while(1) {
            usleep(500);
            if (file_exists($cacheFile)) {
                break;
            }
            usleep(1000);
        }
        header("content-type:text/html;charset=utf-8");
        $dom = new DOMDocument(); 
        $dom->load($cacheFile);
        $messages = $dom->getElementsByTagName('logentry');
        $arrInfos = array(); 
        //兼容gc.style没有填写author的例外。
        if ($id == 43) {
            foreach ($messages as $book) 
            {
                $revision = $book->getAttribute('revision'); 
                $date = $book->getElementsByTagName('date'); 
                $date = $date->item(0)->nodeValue;       
                $arrInfo['revision'] = $revision;
                $arrInfo['date'] = date("Y-m-d H:i:s", strtotime($date));
                $arrInfos[] = $arrInfo; 
            }
            $str = '';
            foreach ($arrInfos as $key => $value) {
                $str .='<option value="'.$value['revision'].'">'.$value['revision'].':::'.$value['date'].'</option>';
            }
        } else {
            foreach ($messages as $book) 
            {
                $revision = $book->getAttribute('revision'); 
                $author = $book->getElementsByTagName('author'); 
                $author = $author->item(0)->nodeValue; 
                $date = $book->getElementsByTagName('date'); 
                $date = $date->item(0)->nodeValue;       
                $arrInfo['revision'] = $revision;
                $arrInfo['author'] = $author;
                $arrInfo['date'] = date("Y-m-d H:i:s", strtotime($date));
                $arrInfos[] = $arrInfo; 
            }
            $str = '';
            foreach ($arrInfos as $key => $value) {
                $str .='<option value="'.$value['revision'].'">'.$value['revision'].':::'.$value['author'].':::'.$value['date'].'</option>';
            }
        }
        
        $callBack = array('status' => true, 'output' => $str);
        echo json_encode($callBack);
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
        $Post_data['star_type'] = 2;

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
        $Post_data['star_type'] = 2;

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
     * 关注的提测列表
     */
    public function star()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '关注的提测列表';
        $data['folder'] = 'all';
        $offset = $data['offset'] = $this->uri->segment(3, 0);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['commitstatus'] = $this->config->item('commitstatus', 'extension');
        $data['commitstatusfilter'] = $this->config->item('commitstatusfilter', 'extension');
        $data['commitstatefilter'] = $this->config->item('commitstatefilter', 'extension');
        $data['commitstate'] = $this->config->item('commitstate', 'extension');
        $config = $this->config->item('pages', 'extension');
        
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/commit/star?uid='.UID.'&offset='.$offset.'&limit='.$config['per_page']);
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
        $config['base_url'] = '/commit/star/';
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

        $this->load->view('commit', $data);
    }

    /**
     * 部署提测脚本
     */
    public function env()
    {
        $test_id = $this->input->get('id');
        $env_id = $this->input->get('env');

        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $env = $this->config->item('env', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $env_ip = $env[$env_id]['ip'];

        //获取提测信息
        $api = $this->curl->get($system['api_host'].'/commit/profile?id='.$test_id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if (!$output['status']) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':提测记录不存在.id[ '.$id.' ]');
                exit(json_encode(array('status' => false, 'error' => '提测记录不存在')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取提测记录异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //打队列
        $repos = array();
        if (file_exists(APPPATH.'cache/repos.cache.php')) {
          $repos = file_get_contents(APPPATH.'cache/repos.cache.php');
          $repos = unserialize($repos);
        }
        $log_filename = 'deploy_staging_'.$repos[$output['content']['repos_id']]['repos_name'].'_'.str_replace('/', '_', $output['content']['br']).'_'.$output['content']['test_flag'];
        $api = $this->curl->get($system['queue_host'].'/?name=deploy_staging&opt=put&data='.$env_ip.'|'.$output['content']['repos_id'].'|'.str_replace('/', ':::', $output['content']['br']).'|'.$output['content']['test_flag'].'&auth=mypass123');
        if ($api['httpcode'] == 200) {
            if ($api['output'] != 'HTTPSQS_PUT_OK') {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入提醒异常-'.$api['output']);
                exit(json_encode(array('status' => false, 'error' => '写入提醒异常-'.$api['output'])));
            } else {
                exit(json_encode(array('status' => true, 'content' => $log_filename)));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':打队列API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 获得部署进度
     */
    public function get_process()
    {
        $test_id = $this->input->get('testid');
        $env_id = $this->input->get('env');
        $file = $this->input->get('file');
        $flag_file = APPPATH.'cache/'.$file.'_flag.log';
        if (file_exists($flag_file)) {
            $flag = file_get_contents($flag_file);
            $flag = intval($flag);
            if ($flag) {
                //锁定任务状态
                $this->config->load('extension', TRUE);
                $system = $this->config->item('system', 'extension');
                $this->load->library('curl', array('token'=>$system['access_token']));

                //获取提测信息
                $api = $this->curl->get($system['api_host'].'/commit/zhanyong?id='.$test_id.'&user='.UID.'&env='.$env_id);
                if ($api['httpcode'] == 200) {
                    $output = json_decode($api['output'], true);
                    if (!$output['status']) {
                        log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':占用失败.id[ '.$id.' ]');
                        exit(json_encode(array('status' => false, 'error' => '占用失败')));
                    }
                } else {
                    log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取提测记录异常.HTTP_CODE['.$api['httpcode'].']');
                    exit(json_encode(array('status' => false, 'error' => '占用API接口异常.HTTP_CODE['.$api['httpcode'].']')));
                }
                exit(json_encode(array('status' => true, 'content' => '部署成功', 'process' => '100')));
            } else {
                exit(json_encode(array('status' => false, 'error' => '部署失败')));
            }
        } else {
            if (!$this->input->cookie('commit_'.$test_id)) {
                $this->input->set_cookie(array('name' => 'commit_'.$test_id, 'value' => 1, 'expire' => 600));
            } else {
                $num = $this->input->cookie('commit_'.$test_id);
                if ($num == 99) {
                    $num = 99;
                } else {
                    $num += 2;
                }
                $this->input->set_cookie(array('name' => 'commit_'.$test_id, 'value' => $num, 'expire' => 600));
            }
            exit(json_encode(array('status' => true, 'content' => '部署中', 'process' => $num)));
        }
    }

    /**
     * 输出某个代码库元的提测记录
     */
    public function repos()
    {
        $this->__init();

        $this->load->helper('alphaid');
        $repos_id = $data['id'] = $this->uri->segment(3, 0);
        $data['repos_id'] = alphaid($repos_id, 1);

        $data['PAGE_TITLE'] = '提测列表';

        $data['folder'] = 'all';
        $state = $data['state'] = $this->uri->segment(4, 'all');
        $status = $data['status'] = $this->uri->segment(5, 'all');
        $offset = $data['offset'] = $this->uri->segment(6, 0);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['commitstatus'] = $this->config->item('commitstatus', 'extension');
        $data['commitstatusfilter'] = $this->config->item('commitstatusfilter', 'extension');
        $data['commitstatefilter'] = $this->config->item('commitstatefilter', 'extension');
        $data['commitstate'] = $this->config->item('commitstate', 'extension');
        $config = $this->config->item('pages', 'extension');

        //根据任务ID获取任务信息
        $filter = 'repos_id,'.$data['repos_id'];
        if ($state && $state != 'all') {
            $filter .= '|state,'.$data['commitstatefilter'][$state]['id'];
        }
        if ($status && $status != 'all') {
            $filter .= '|status,'.$data['commitstatusfilter'][$status]['id'];
        }
        
        $ids = array();
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/commit/rows?offset='.$offset.'&limit='.$config['per_page'].'&filter='.$filter);
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
        $config['base_url'] = '/commit/repos/'.$repos_id.'/'.$state.'/'.$status;
        $this->pagination->initialize($config);
        $data['pages'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        $data['per_page'] = $config['per_page'];

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('commit_repos', $data);
    }
}