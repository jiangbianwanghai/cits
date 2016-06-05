<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 在线用户数据库模型
 *
 * @package application
 * @subpackage  models
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Model_online extends MY_Model {

    /**
     * @var string dbgroup 数据库
     */
    public $dbgroup = 'default';

    /**
     * @var string table 数据表
     */
    public $table   = 'user_online';

    /**
     * @var string primary 主键
     */
    public $primary = 'id';

    /**
     * 根据用户UID删除记录
     *
     * @param integer $uid 用户UID
     * @return string
     */
    public function del_by_uid($uid)
    {
    	return $this->db->delete($this->table, array('uid' => $uid));
    }

    /**
     * 获取在线用户信息
     *
     * @param integer $time 活跃时间范围。默认15分钟内的用户，建议活跃时间范围要比刷新在线状态的时间长。
     * @return array
     */
    public function users($time = 900)
    {
    	$onlineTime = time() - 900;
    	$this->db->where('act_time >', $onlineTime); //15分钟的活跃用户
    	$query = $this->db->get($this->table);
        $rows = $query->result_array();
        return $rows;
    }

    /**
     * 刷新在线状态
     *
     * @param integer $uid 用户UID
     * @param integer $time 更新数据库中在线状态的间隔时间.默认10分钟更新一次
     */
    public function refresh($uid, $time = 600) 
    {
    	if ((time() - $this->input->cookie('cits_user_online')) > $time) {
    		$this->update_by_unique(array('uid' => $uid, 'act_time' => time()));
    		$this->input->set_cookie('cits_user_online', time(), 86400);
    	}
    }
}