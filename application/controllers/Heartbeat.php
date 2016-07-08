<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 实时大盘
 */
class Heartbeat extends CI_Controller {

    public function index()
    {
        $this->load->helper('url');
        redirect('/heartbeat/plan', 'location');
    }

    public function plan()
    {
        $data['PAGE_TITLE'] = '计划大盘';

        $offset = $data['offset'] = $this->uri->segment(3, 0);
        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $config = $this->config->item('pages', 'extension');
        $data['planflow'] = $this->config->item('planflow', 'extension');

        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/plan/rows?offset='.$offset.'&limit='.$config['per_page']);
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
        $config['base_url'] = '/heartbeat/plan';
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

        $this->load->view('hb_plan', $data);
    }

    public function issue()
    {
        $data['PAGE_TITLE'] = '任务大盘';

        $offset = $data['offset'] = $this->uri->segment(3, 0);
        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $config = $this->config->item('pages', 'extension');
        $data['level'] = $this->config->item('level', 'extension');
        $data['workflow'] = $this->config->item('workflow', 'extension');
        $data['workflowfilter'] = $this->config->item('workflowfilter', 'extension');
        $data['issuestatusfilter'] = $this->config->item('issuestatusfilter', 'extension');
        $data['issuestatus'] = $this->config->item('issuestatus', 'extension');
        $data['tasktype'] = $this->config->item('tasktype', 'extension');
        $data['tasktypefilter'] = $this->config->item('tasktypefilter', 'extension');

        $ids = array();
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/issue/rows?offset='.$offset.'&limit='.$config['per_page']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['rows'] = $output['content'];
                foreach ($data['rows']['data'] as $key => $value) {
                    $ids[] = $value['plan_id'];
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        if ($ids) {
            $api = $this->curl->get($system['api_host'].'/plan/rows?ids='.implode(',', array_unique($ids)));
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    foreach ($output['content'] as $key => $value) {
                        $data['planarr'][$value['id']] = $value;
                    }
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取计划API异常.HTTP_CODE['.$api['httpcode'].']');
                show_error('读取计划API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
            }
        }

        //分页
        $this->load->library('pagination');
        $config['total_rows'] = $data['rows']['total'];
        $config['cur_page'] = $offset;
        $config['base_url'] = '/heartbeat/issue';
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

        $this->load->view('hb_issue', $data);
    }

    public function bug()
    {
        $data['PAGE_TITLE'] = 'BUG大盘';

        $offset = $data['offset'] = $this->uri->segment(3, 0);
        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $config = $this->config->item('pages', 'extension');
        $data['level'] = $this->config->item('level', 'extension');
        $data['bugflow'] = $this->config->item('bugflow', 'extension');
        $data['bugstatus'] = $this->config->item('bugstatus', 'extension');

        $ids = array();
        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/bug/rows?offset='.$offset.'&limit='.$config['per_page']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['rows'] = $output['content'];
                foreach ($data['rows']['data'] as $key => $value) {
                    $ids[] = $value['issue_id'];
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        if ($ids) {
            $api = $this->curl->get($system['api_host'].'/issue/rows?ids='.implode(',', array_unique($ids)));
            if ($api['httpcode'] == 200) {
                $output = json_decode($api['output'], true);
                if ($output['status']) {
                    foreach ($output['content'] as $key => $value) {
                        $data['issuearr'][$value['id']] = $value;
                    }
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取计划API异常.HTTP_CODE['.$api['httpcode'].']');
                show_error('读取计划API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
            }
        }

        //分页
        $this->load->library('pagination');
        $config['total_rows'] = $data['rows']['total'];
        $config['cur_page'] = $offset;
        $config['base_url'] = '/heartbeat/bug';
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

        $this->load->view('hb_bug', $data);
    }

    public function env()
    {
        $data['PAGE_TITLE'] = '测试环境占用大盘';
        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        $data['rows'] = array('total' => 0, 'data' => array());
        $api = $this->curl->get($system['api_host'].'/env/rows');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['rows'] = $output['content'];

                $server = $repo = array();
                foreach($data['rows']['data'] as $r){
                    $server[] = $r['env'];
                    if(!empty($r['br']) && ($r['rev'] > 0) && ($r['createtime'] > (strtotime(date('Y-m-d',time())) + 3600*5))){
                        $repo[$r['repo']][$r['env']] = '<span class="label label-info">' . $r['br'] . '@' . $r['rev'] . '</span><br>(' . strftime('%Y-%m-%d %H:%M:%S', $r['createtime']) . ')';
                    }else{
                        $repo[$r['repo']][$r['env']] = '<span class="label label-default">N/A</span>';
                    }
                }
                sort($server);
                ksort($repo);
                    $data['PAGE_TITLE'] = '代码查询- 数据分析';
                $data['repo'] = $repo;
                $data['server'] = array_unique($server);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->helper('alphaid');

        $this->load->view('hb_env', $data);
    }
}