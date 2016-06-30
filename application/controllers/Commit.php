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
     * 计划列表
     *
     * 默认显示所有项目
     */
    public function index()
    {
        $this->__init();

        $data['PAGE_TITLE'] = '计划列表';

        

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('plan', $data);
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
     * 写入任务信息
     *
     * 操作：写入任务信息，写入操作日志，通知受理人
     */
    public function add_ajax()
    {
        $this->__init();
        exit();

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
        if ($planid) {
            $planid = $this->encryption->decrypt($planid);
            if (!($planid != 0 && ctype_digit($planid))) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':计划id错误');
                exit(json_encode(array('status' => false, 'error' => '计划id错误')));
            }
        }

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
            exit(json_encode(array('status' => false, 'error' => '消息队列出现异常', 'code' => 1001)));

        //等待2秒中，让worker把查询结果写入缓存
        sleep(2);

        //获取生成的缓存文件并解析它
        $cacheFile = APPPATH.'cache/repos_'.$id.'_tree.log';
        if (!file_exists($cacheFile))
            exit(json_encode(array('status' => false, 'error' => '文件不存在', 'code' => 1002)));

        $con = file_get_contents($cacheFile);
        if ($con)
        $conArr = unserialize($con);

        if (!$conArr)
            exit(json_encode(array('status' => false, 'error' => '格式异常', 'code' => 1003)));

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

        //发送消息给后端worker
        $this->load->library('curl');
        $res = $this->curl->get($sqsUrl);
        if ($res['httpcode'] != 200)
            exit(json_encode(array('status' => false, 'error' => '消息队列出现异常', 'code' => 1001)));

        //等待2秒中，让worker把查询结果写入缓存
        sleep(2);
        header("content-type:text/html;charset=utf-8");
        $dom = new DOMDocument(); 
        $dom->load(APPPATH."/cache/repos_1_commit.xml");
        $messages = $dom->getElementsByTagName('logentry');
        $arrInfos = array(); 
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
        $callBack = array('status' => true, 'output' => $str);
        echo json_encode($callBack);
    }
}