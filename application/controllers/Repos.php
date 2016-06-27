<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户模块
 */
class Repos extends CI_Controller {

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
     * 刷新代码库缓存文件
     */
    public function refresh()
    {
        $this->load->library(array('curl', 'encryption'));
        $this->config->load('extension', TRUE);
        $system = $this->config->item('system', 'extension');
        $api = $this->curl->get($system['api_host'].'/repos/cache?access_token='.$system['access_token']);
        if ($api['httpcode'] == 200) {
            $output = json_decode($api['output'], true);
            if ($output['status']) {
                $this->load->helper('file');
                foreach ($output['content']['data'] as $key => $value) {
                    $rows[$value['id']] = $value;
                    $rows[$value['id']]['sha'] = $this->encryption->encrypt($value['id']);
                }
                write_file(APPPATH.'/cache/repos.cache.php', serialize($rows));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':API异常.HTTP_CODE['.$api['httpcode'].']');
            exit(json_encode(array('status' => false, 'error' => 'API异常.HTTP_CODE['.$api['httpcode'].']')));
        }
    }
}