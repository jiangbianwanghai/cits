<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 我的面板
 */
class Dashboard extends CI_Controller {

    public function index() {

        $data['PAGE_TITLE'] = '我的面板';

        //

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;
        
        $this->load->view('home', $data);
    }

    /**
     * 退出
     */
    public function logout() {
        $this->load->helper(array('cookie', 'url'));
        delete_cookie('cits_auth'); //删除用户信息
        delete_cookie('cits_user_online'); //删除在线时间戳
        delete_cookie('cits_star_project'); //删除关注项目

        //删除在线状态
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->del_by_uid(UID);
        redirect('/', 'location');
    }

    /**
     * 图片上传
     */
    public function upload()
    {
        if($_FILES['upload_file']) {
            $dir_name = date("Ymd", time());
            $this->config->load('extension', TRUE);
            $system = $this->config->item('system', 'extension');
            $dir = $system['file_dir'].'/'.$dir_name;
            if (!is_dir($dir)) mkdir($dir, 0777);
            $config['upload_path'] = $dir; 
            $config['file_name'] = 'IMG_'.time();
            $config['overwrite'] = TRUE;
            $config["allowed_types"] = 'jpg|jpeg|png|gif';
            $config["max_size"] = 2048;
            $this->load->library('upload', $config);

            if(!$this->upload->do_upload('upload_file')) {               
                $error = $this->upload->display_errors();
                echo '{"success": false,"msg": "'.$error.'"}';
            } else {
                $data['upload_data']=$this->upload->data();
                $img=$data['upload_data']['file_name'];
                echo '{"success": true,"file_path": "'.$system['file_host'].'/'.$dir_name.'/'.$img.'"}';                              
            }  
        }
    }

    /**
     * 获取最新的buglist
     */
    public function get_bug_to_me()
    {
        //读取系统配置信息
        $this->load->helper('alphaid');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $bugflow = $this->config->item('bugflow', 'extension');
        $bugstatus = $this->config->item('bugstatus', 'extension');

        //读取bug
        $ids = array();
        $rows = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/bug/rows?limit=5&filter=accept_user,'.UID);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $rows = $output['content'];
                foreach ($rows['data'] as $key => $value) {
                    $rows['data'][$key]['id'] = alphaid($value['id']);
                    $rows['data'][$key]['bugstatus_color'] = $bugstatus[$value['status']]['span_color'];
                    $rows['data'][$key]['bugstatus_name'] = $bugstatus[$value['status']]['name'];
                    $rows['data'][$key]['bugstate_color'] = $bugflow[$value['state']]['span_color'];
                    $rows['data'][$key]['bugstate_name'] = $bugflow[$value['state']]['name'];
                    $rows['data'][$key]['add_time'] = date("Y/m/d H:i:s", $value['add_time']);
                    $ids[] = $value['issue_id'];
                }
            } else{
                exit(json_encode(array('status' => false, 'message' => '无记录')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'message' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //读取相关联的任务
        if ($ids) {
            $api = $this->curl->get($system['api_host'].'/issue/rows?ids='.implode(',', array_unique($ids)));
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    foreach ($output['content'] as $key => $value) {
                        $issuearr[$value['id']] = $value;
                    }
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务API异常.HTTP_CODE['.$api['httpcode'].']');
                exit(json_encode(array('status' => false, 'message' => '读取任务API异常.HTTP_CODE['.$api['httpcode'].']')));
            }

            foreach ($rows['data'] as $key => $value) {
                $rows['data'][$key]['issue_name'] = isset($issuearr[$value['issue_id']]) ? $issuearr[$value['issue_id']]['issue_name'] : 'N/A';
            }
        }

        exit(json_encode(array('status' => true, 'output' => $rows)));
    }

    /**
     * 获取最新的提测记录
     */
    public function get_commit_from_me()
    {
        //读取系统配置信息
        $this->load->helper('alphaid');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $commitstatus = $this->config->item('commitstatus', 'extension');
        $commitstate = $this->config->item('commitstate', 'extension');

        $repos = array();
        if (file_exists(APPPATH.'cache/repos.cache.php')) {
          $repos = unserialize(file_get_contents(APPPATH.'cache/repos.cache.php'));
        }

        //读取bug
        $ids = array();
        $rows = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/commit/rows?limit=5&filter=add_user,'.UID);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $rows = $output['content'];
                foreach ($rows['data'] as $key => $value) {
                    $rows['data'][$key]['subject'] = isset($repos[$value['repos_id']]) ? $repos[$value['repos_id']]['repos_name'].'@'.$value['br'].'#'.$value['test_flag'] : 'N/A';
                    $rows['data'][$key]['commitstatus_color'] = $commitstatus[$value['status']]['span_color'];
                    $rows['data'][$key]['commitstatus_name'] = $commitstatus[$value['status']]['name'];
                    $rows['data'][$key]['commitstate_color'] = $commitstate[$value['state']]['span_color'];
                    $rows['data'][$key]['commitstate_name'] = $commitstate[$value['state']]['name'];
                    $rows['data'][$key]['add_time'] = date("Y/m/d H:i:s", $value['add_time']);
                    $ids[] = $value['issue_id'];
                }
            } else{
                exit(json_encode(array('status' => false, 'message' => '无记录')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'message' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //读取相关联的任务
        if ($ids) {
            $api = $this->curl->get($system['api_host'].'/issue/rows?ids='.implode(',', array_unique($ids)));
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    foreach ($output['content'] as $key => $value) {
                        $issuearr[$value['id']] = $value;
                    }
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取任务API异常.HTTP_CODE['.$api['httpcode'].']');
                exit(json_encode(array('status' => false, 'message' => '读取任务API异常.HTTP_CODE['.$api['httpcode'].']')));
            }

            foreach ($rows['data'] as $key => $value) {
                $rows['data'][$key]['issue_id'] = isset($issuearr[$value['issue_id']]) ? alphaid($value['issue_id']) : '0';
                $rows['data'][$key]['issue_name'] = isset($issuearr[$value['issue_id']]) ? $issuearr[$value['issue_id']]['issue_name'] : 'N/A';
            }
        }

        exit(json_encode(array('status' => true, 'output' => $rows)));
    }

    /**
     * 获取最新的提测记录
     */
    public function get_issue_to_me()
    {
        //读取系统配置信息
        $this->load->helper('alphaid');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $workflow = $this->config->item('workflow', 'extension');
        $issuestatus = $this->config->item('issuestatus', 'extension');
        $tasktype = $this->config->item('tasktype', 'extension');

        //读取bug
        $ids = array();
        $rows = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/issue/rows?limit=5&filter=accept_user,'.UID);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $rows = $output['content'];
                foreach ($rows['data'] as $key => $value) {
                    $rows['data'][$key]['id'] = alphaid($value['id']);
                    $rows['data'][$key]['workflow_color'] = $workflow[$value['workflow']]['span_color'];
                    $rows['data'][$key]['workflow_name'] = $workflow[$value['workflow']]['name'];
                    $rows['data'][$key]['issuestatus_color'] = $issuestatus[$value['status']]['span_color'];
                    $rows['data'][$key]['issuestatus_name'] = $issuestatus[$value['status']]['name'];
                     $rows['data'][$key]['tasktype'] = $tasktype[$value['type']];
                    $rows['data'][$key]['add_time'] = date("Y/m/d H:i:s", $value['add_time']);
                    $ids[] = $value['plan_id'];
                }
            } else{
                exit(json_encode(array('status' => false, 'message' => '无记录')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'message' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //读取相关联的任务
        if ($ids) {
            $api = $this->curl->get($system['api_host'].'/plan/rows?ids='.implode(',', array_unique($ids)));
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    foreach ($output['content'] as $key => $value) {
                        $planarr[$value['id']] = $value;
                    }
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取计划API异常.HTTP_CODE['.$api['httpcode'].']');
                exit(json_encode(array('status' => false, 'message' => '读取计划API异常.HTTP_CODE['.$api['httpcode'].']')));
            }

            foreach ($rows['data'] as $key => $value) {
                $rows['data'][$key]['plan_name'] = isset($planarr[$value['plan_id']]) ? $planarr[$value['plan_id']]['plan_name'] : 'N/A';
            }
        }

        exit(json_encode(array('status' => true, 'output' => $rows)));
    }

    /**
     * 读取提醒信息
     */
    public function get_notify()
    {
        //获得消息记录
        $this->load->helper('alphaid');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/notify/get_rows?uid='.UID.'&is_read=n&offset=0&limit=5');
        if ($api['httpcode'] == 200) {
            $users = array();
            if (file_exists(APPPATH.'/cache/user.cache.php')) {
              $users = file_get_contents(APPPATH.'/cache/user.cache.php');
              $users = unserialize($users);
            }
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                foreach ($output['content']['data'] as $key => $value) {
                    $output['content']['data'][$key]['user_realname'] = $users[$value['user']]['realname'];
                    $output['content']['data'][$key]['log_action'] = $value['log']['action'];
                    $output['content']['data'][$key]['log_subject'] = $value['log']['subject'];
                    
                    if ($value['log']['target_type'] == '3') {
                        $url = 'issue/view/'.alphaid($value['log']['target']);
                        $output['content']['data'][$key]['log_target_type'] = '任务';
                    }
                    $output['content']['data'][$key]['log_url'] = $url;
                    $output['content']['data'][$key]['log_target'] = $value['log']['target'];
                    $output['content']['data'][$key]['log_sender_username'] = $users[$value['log']['sender']]['username'];
                    $output['content']['data'][$key]['log_sender_realname'] = $users[$value['log']['sender']]['realname'];
                    $subject = '给你';
                    $subject = $value['log']['action'].'了';
                    if ($value['log']['target_type'] == '3') {
                        $subject .= '任务';
                        $url = '/issue/view/'.alphaid($value['log']['target']);
                    }
                    if ($value['log']['action'] == '评论') {
                        $url .= '#comment-'.alphaid($value['log']['content']);
                    }
                    $end = '';
                    if ($value['log']['action'] == '变更') {
                        $end = ' 的工作流状态为 '.$value['log']['content'];
                    }
                    $output['content']['data'][$key]['subject'] = $subject.' #<a href="/notify/read?id='.alphaid($value['id']).'&token='.urlencode($this->encryption->encrypt($url)).'">'.$value['log']['subject'].'</a>'.$end;

                }
                $output['content']['datas'] = $output['content']['data'];
                unset($output['content']['data']);
                exit(json_encode($output['content']));
            } else {
                exit(json_encode(array('total' => 0)));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取提醒API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
}