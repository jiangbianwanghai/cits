<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户模块
 */
class User extends CI_Controller {

    /**
     * 用户列表
     *
     * 默认显示所有项目
     */
    public function index()
    {
        $data['PAGE_TITLE'] = '用户面板';

        $uid = $this->uri->segment(3, 0);
        $this->load->helper('alphaid');
        if ($uid) {
            $uid = alphaid($uid, 1);
        } else {
            $uid = UID;
        }

        //读取个人信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        $api = $this->curl->get($system['api_host'].'/user/row?uid='.$uid);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['profile'] = $output['content'];
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.$output['error']);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':获取个人信息API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        $report = array();

        //输出受理统计
        $api = $this->curl->get($system['api_host'].'/report/accept?uid='.$uid);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                foreach ($output['content'] as $key => $value) {
                    $time = strtotime($value['perday']);
                    $report[$time]['perday'] = $value['perday'];
                    $report[$time]['issue'] = $value['total'];
                    $report[$time]['bug'] = 0;
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.$output['error']);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':输出受理统计API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '输出受理统计API异常.HTTP_CODE['.$api['httpcode'].']')));
        }

        //输出bug统计
        $api = $this->curl->get($system['api_host'].'/report/bug?uid='.$uid);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                foreach ($output['content'] as $key => $value) {
                    $time = strtotime($value['perday']);
                    $report[$time]['perday'] = $value['perday'];
                    $report[$time]['bug'] = $value['total'];
                    !isset($report[$time]['issue']) && $report[$time]['issue'] = 0;
                }
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.$output['error']);
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':输出bug统计API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => '输bug统计API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
        $report_tmp = array();
        if ($report) {
            foreach ($report as $key => $value) {
                $tmp[] = $key;
            }
            sort($tmp);
            foreach ($tmp as $key => $value) {
                $report_tmp[] = $report[$value];
            }
        }
        $data['report'] = $report_tmp;

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        $this->load->view('user', $data);
    }

    /**
     * 刷新用户缓存文件
     */
    public function refresh()
    {
        $this->load->library('encryption');
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/user/cache');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->load->helper('file');
                foreach ($output['content']['data'] as $key => $value) {
                    $rows[$value['uid']] = $value;
                    $rows[$value['uid']]['sha'] = $this->encryption->encrypt($value['uid']);
                }
                write_file(APPPATH.'/cache/user.cache.php', serialize($rows));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 操作记录
     */
    public function log()
    {
        $data['PAGE_TITLE'] = '操作记录';

        $uid = $this->uri->segment(3, 0);
        $offset = $this->uri->segment(4, 0);

        //读取系统配置信息
        $this->load->helper('alphaid');
        if ($uid) {
            $uid = alphaid($uid, 1);
        } else {
            $uid = UID;
        }
        $data['logs'] = array('total' => 0, 'data' => array());
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $config = $this->config->item('pages', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/handle/get_rows?uid='.$uid.'&offset='.$offset.'&limit='.$config['per_page']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['logs'] = $output['content'];
                $target_type_arr = array('1' => array('zh' => '项目', 'en' => 'project'), '2' => array('zh' => '计划', 'en' => 'plan'), '3' => array('zh' => '任务', 'en' => 'issue'), '4' => array('zh' => '提测', 'en' => 'commit'), '5' => array('zh' => 'Bug', 'en' => 'bug'));
                foreach ($data['logs']['data'] as $key => $value) {
                    $text = '';
                    if ($value['action'] == '指派' || $value['action'] == '变更') {
                        $text .= '将 ';
                    } elseif($value['action'] == '提测' || $value['action'] == '反馈') {
                        $text .= '向 ';
                    } else {
                        $text .= $value['action'].'了 ';
                    }
                    $text .= $target_type_arr[$value['target_type']]['zh'].' <a href="/'.$target_type_arr[$value['target_type']]['en'].'/view/'.alphaid($value['target']).'">'.$value['subject'].'</a>';
                    if ($value['action'] == '指派') {
                        $text .= ' '.$value['action'].'给了 ';
                    }
                    if ($value['action'] == '变更') {
                        $text .= ' 状态'.$value['action'].'为 ';
                    }
                    if ($value['action'] == '提测') {
                        $text .= ' '.$value['action'].'了代码 ';
                    }
                    if ($value['action'] != '编辑' && $value['action'] != '反馈' && $value['action'] != '评论') {
                        $text .= ' '.$value['content'];
                    }
                    if ($value['action'] == '反馈') {
                        $text .= ' '.$value['action'].'了一个BUG <a href="/bug/view/'.alphaid($value['content']).'">查看该BUG</a>';
                    }
                    if ($value['action'] == '评论') {
                        $text .= ' <a href="/issue/view/'.alphaid($value['target']).'#comment-'.alphaid($value['content']).'">查看评论</a>';
                    }
                    $data['logs']['data'][$key]['text'] = $text;
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取操作日志API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        //刷新在线用户列表（埋点）
        $this->load->model('Model_online', 'online', TRUE);
        $this->online->refresh(UID);
        $onlineUsers = $this->online->users();
        $data['online_users'] = $onlineUsers;

        //分页
        $this->load->library('pagination');
        $config['total_rows'] = $data['logs']['total'];
        $config['cur_page'] = $offset;
        $config['base_url'] = '/user/log/'.alphaid($uid);
        $this->pagination->initialize($config);
        $data['pages'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        $data['per_page'] = $config['per_page'];

        $this->load->view('user_log', $data);
    }


    public function avatar()
    {
        $data['PAGE_TITLE'] = '修改头像';
        $this->load->view('user_avatar', $data);
    }

    /**
     * 图片上传
     */
    public function avatar_upload()
    {
        if($_FILES['head_photo']) {
            $dir_name = date("Ymd", time());
            $this->config->load('extension', TRUE);
            $system = $this->config->item('system', 'extension');
            $dir = $system['avatar_dir'].'/'.$dir_name;
            if (!is_dir($dir)) mkdir($dir, 0777);
            $config['upload_path'] = $dir; 
            $config['file_name'] = 'IMG_'.time();
            $config['overwrite'] = TRUE;
            $config["allowed_types"] = 'jpg|jpeg|png|gif';
            $config["max_size"] = 2048;
            $this->load->library('upload', $config);

            if(!$this->upload->do_upload('head_photo')) {               
                $error = $this->upload->display_errors();
                echo '{"success": false,"msg": "'.$error.'"}';
            } else {
                $data['upload_data']=$this->upload->data();
                $img=$data['upload_data']['file_name'];
                chmod($dir.'/'.$img, 0777);
                echo '{"msg": 0000, "error":"", "imgurl": "'.AVATAR_HOST.'/'.$dir_name.'/'.$img.'"}';                              
            }  
        }
    }

    /**
     * 裁切头像
     */
    public function avatar_crop()
    {
        $data['PAGE_TITLE'] = '裁切头像';
        $data['img'] = $this->input->get('img');
        $this->load->view('user_avatar_crop', $data);
    }

    public function avatar_submit()
    {
        
        $x = $this->input->post('x');
        $y = $this->input->post('y');
        $w = $this->input->post('w');
        $h = $this->input->post('h');
        $targ_w = $targ_h = 100;
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $pic_name = str_replace(AVATAR_HOST, '', $this->input->post('pic_name'));
        $config = array(
            'filepath' => $system['avatar_dir'],
            'picname' => $pic_name,
            'x' => $x,
            'y' => $y,
            'w' => $w,
            'h' => $h,
            'tw' => $targ_w,
            'th' => $targ_h,
            'newfilename' => USER_NAME
        );
        $this->load->library('jcrop', $config);
        $file=$this->jcrop->crop();
        $file['file'] = str_replace($system['avatar_dir'], AVATAR_HOST, $file['file']).'?'.time();
        echo json_encode($file);
    }
}