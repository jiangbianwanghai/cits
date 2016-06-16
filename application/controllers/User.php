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
        echo '用户列表';
    }

    /**
     * 刷新用户缓存文件
     */
    public function refresh()
    {
        $this->load->library(array('curl', 'encryption'));
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
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

}