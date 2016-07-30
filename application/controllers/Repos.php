<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 代码库模块
 */
class Repos extends CI_Controller {

    /**
     * 列表
     *
     * 默认显示所有项目
     */
    public function index()
    {
        $data['PAGE_TITLE'] = 'bug列表';

        //读取代码库信息

        $offset = $data['offset'] = $this->uri->segment(3, 0);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $config = $this->config->item('pages', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        $data['rows'] = array('total' => 0, 'data' => array());
        $filter = 'status,1';
        $api = $this->curl->get($system['api_host'].'/repos/rows?offset='.$offset.'&limit='.$config['per_page'].'&filter='.$filter);
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
        $config['base_url'] = '/repos/index';
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

        $this->load->view('repos', $data);
    }

    /**
     * 添加代码源
     */
    public function add()
    {
        $data['PAGE_TITLE'] = '添加代码源';

        $this->load->view('repos_add', $data);
    }

    public function add_ajax()
    {
        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('repos_name', '名称', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_name_other', '别名', 'trim');
        $this->form_validation->set_rules('repos_url', '地址', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_summary', '描述', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('merge', '是否需要合并', 'trim|required|is_natural|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '是否需要合并[ '.$this->input->post('merge').' ]不符合规则',
                'max_length' => '是否需要合并[ '.$this->input->post('merge').' ]太长了'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        //写入数据
        $Post_data['repos_name'] = $this->input->post('repos_name');
        $Post_data['repos_name_other'] = $this->input->post('repos_name_other');
        $Post_data['repos_url'] = $this->input->post('repos_url');
        $Post_data['repos_summary'] = $this->input->post('repos_summary');
        $Post_data['merge'] = $this->input->post('merge');
        $Post_data['type'] = $this->input->post('type');
        $Post_data['add_user'] = UID;
        $api = $this->curl->post($system['api_host'].'/repos/write', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->refresh(0);
                exit(json_encode(array('status' => true, 'message' => '添加成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':添加失败：'.$output['error']);
                exit(json_encode(array('status' => false, 'error' => '添加失败：'.$output['error'])));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入代码库API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    public function edit()
    {

        $data['PAGE_TITLE'] = '编辑代码库';

        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $data['id'] = $id;
        $id = alphaid($id, 1);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        //根据项目ID获取计划列表
        $api = $this->curl->get($system['api_host'].'/repos/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $data['profile'] = $output['content'];
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':代码库不存在.id[ '.$id.' ]');
                show_error('代码库不存在', 500, '错误');
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取代码库信息API异常.HTTP_CODE['.$api['httpcode'].']');
            show_error('API异常.HTTP_CODE['.$api['httpcode'].']', 500, '错误');
        }

        $this->load->view('repos_edit', $data);
    }

    public function edit_ajax()
    {
        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('id', 'id', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_name', '名称', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_name_other', '别名', 'trim');
        $this->form_validation->set_rules('repos_url', '地址', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_summary', '描述', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('merge', '是否需要合并', 'trim|required|is_natural|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '是否需要合并[ '.$this->input->post('merge').' ]不符合规则',
                'max_length' => '是否需要合并[ '.$this->input->post('merge').' ]太长了'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        $this->load->helper('alphaid');
        $id = alphaid($this->input->post('id'), 1);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));

        //写入数据
        $Post_data['id'] = $id;
        $Post_data['repos_name'] = $this->input->post('repos_name');
        $Post_data['repos_name_other'] = $this->input->post('repos_name_other');
        $Post_data['repos_url'] = $this->input->post('repos_url');
        $Post_data['repos_summary'] = $this->input->post('repos_summary');
        $Post_data['merge'] = $this->input->post('merge');
        $Post_data['last_user'] = UID;
        $api = $this->curl->post($system['api_host'].'/repos/update', $Post_data);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->refresh(0);
                exit(json_encode(array('status' => true, 'message' => '编辑成功')));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':编辑失败：'.$output['error']);
                exit(json_encode(array('status' => false, 'error' => '编辑失败：'.$output['error'])));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入代码库API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * bug 删除
     */
    public function del()
    {
        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $id = alphaid($id, 1);

        //根据id获取任务信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/repos/del?id='.$id.'&user='.UID);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->refresh(0);
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
     * 详情
     */
    public function view()
    {
        //解析url传值
        $this->load->helper('alphaid');
        $id = $this->uri->segment(3, 0);
        $id = alphaid($id, 1);

        //读取系统配置信息
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $data['level'] = $this->config->item('level', 'extension');

        //载入用户缓存文件
        $users = array();
        if (file_exists(APPPATH.'cache/user.cache.php')) {
          $users = file_get_contents(APPPATH.'cache/user.cache.php');
          $users = unserialize($users);
        }

        //根据bug ID获取bug信息
        $api = $this->curl->get($system['api_host'].'/repos/profile?id='.$id);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $merge = array(0 => '不需要', 1 => '需要');
                $output['content']['merge'] = $merge[$output['content']['merge']];
                $output['content']['add_user'] = $users[$output['content']['add_user']]['realname'];
                $output['content']['add_time'] = $output['content']['add_time'] ? date('Y/m/d H:i:s', $output['content']['add_time']) : 'N/A';
                $output['content']['last_user'] = $output['content']['last_user'] ? $users[$output['content']['last_user']]['realname'] : 'N/A';
                $output['content']['last_time'] = $output['content']['last_time'] ? date('Y/m/d H:i:s', $output['content']['last_time']) : 'N/A';
                exit(json_encode(array('status' => true, 'message' => $output['content'])));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':bug不存在.id[ '.$id.' ]');
                exit(json_encode(array('status' => false, 'error' => '查询失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':读取bug信息API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }

    /**
     * 刷新代码库缓存文件
     */
    public function refresh($output = 1)
    {
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $this->load->library('curl', array('token'=>$system['access_token']));
        $api = $this->curl->get($system['api_host'].'/repos/cache');
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->load->helper('file');
                foreach ($output['content']['data'] as $key => $value) {
                    $rows[$value['id']] = $value;
                }
                $bool = write_file(APPPATH.'/cache/repos.cache.php', serialize($rows));
                if ($bool) {
                    if ($output) {
                        exit(json_encode(array('status' => true, 'message' => '刷新成功')));
                    } else {
                        return true;
                    }
                } else {
                    if ($output) {
                        exit(json_encode(array('status' => false, 'error' => '刷新失败')));
                    } else {
                        return false;
                    }
                }
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            if ($output) {
                exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
            } else {
                return false;
            }
        }
    }
}