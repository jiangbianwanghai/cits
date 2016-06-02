<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Model_online extends MY_Model {
    public $dbgroup = 'default';
    public $table   = 'user_online';
    public $primary = 'id';

    public function delByUid($uid) {
    	return $this->db->delete($this->table, array('uid' => $uid));
    }

    /**
     * 获取在线用户信息
     */
    public function users() {
    	$onlineTime = time() - 900;
    	$this->db->where('act_time >', $onlineTime); //15分钟的活跃用户
    	$query = $this->db->get($this->table);
        $rows = $query->result_array();
        return $rows;
    }

    /**
     * 刷新在线状态
     */
    public function refreshOnline($uid) {
    	if ((time() - $this->input->cookie('cits_user_online')) > 6) {
    		$this->updateByUnique(array('uid' => $uid, 'act_time' => time()));
    		$this->input->set_cookie('cits_user_online', time(), 86400);
    	}
    }
}