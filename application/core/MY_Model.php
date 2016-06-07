<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 自定义的模型
 */
class MY_Model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取最小起始id/最大结束id
     * @param  string $order 倒序还是正序，只接受DESC/ASC
     * @param  string $where 查询条件
     * @return int | false
     */
    public function getId($order = 'DESC', $where = '')
    {
        $maxId = false;
        if (in_array($order, array('ASC', 'DESC'))) {
            if ($where)
                $where = " WHERE ".$where;
            $customDB = $this->load->database($this->dbgroup, TRUE);
            $sql = "SELECT `".$this->primary."` FROM `".$this->table."`".$where." ORDER BY `".$this->primary."` ".$order." LIMIT 1";
            echo $sql;exit;
            $query = $customDB->query("SELECT `".$this->primary."` FROM `".$this->table."`".$where." ORDER BY `".$this->primary."` ".$order." LIMIT 1");
            $customDB->close();
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                return $row[$this->primary];
            }
        }
        return $maxId;
    }

    /**
     * 返回多行记录
     * @param  array   $field 要查询的字段
     * @param  string  $where 查询条件
     * @param  string  $order 排序方式
     * @param  integer $limit 步长
     * @return array | false
     */
    public function get_rows($field = array(), $where = array(), $order = array(), $limit = 20, $offset = 0)
    {
        $customDB = $this->load->database($this->dbgroup, TRUE);
        if ($field)
            $fieldStr = "`".implode("`,`", $field)."`";
        else
            $fieldStr = "*";
        $customDB->select($fieldStr);
        if ($where) {
            $customDB->where($where);
        }
        if ($order) {
            foreach ($order as $key => $value) {
                $customDB->order_by($key, $value);
            }
        }
        $this->db->limit($limit, $offset);
        $query = $customDB->get($this->table);
        $customDB->close();
        return $query->result_array();
    }

    /**
     * 返回单条记录
     * @param  array  $field 要查询的字段
     * @param  string $where 查询条件
     * @param  string $order 排序方式
     * @return array | false
     */
    public function fetchOne($field = array(), $where = array())
    {
        $customDB = $this->load->database($this->dbgroup, TRUE);
        if ($field)
            $fieldStr = "`".implode("`,`", $field)."`";
        else
            $fieldStr = "*";
        $customDB->select($fieldStr);
        if ($where) {
            $customDB->where($where);
        }
        $query = $customDB->get($this->table);
        $customDB->close();
        return $query->row_array();
    }

    /**
     * 返回记录数量
     * @param  array   $field 要查询的字段
     * @param  string  $where 查询条件
     * @param  string  $order 排序方式
     * @return array | false
     */
    public function getCount($where = '')
    {
        if ($where)
            $whereStr = " WHERE ".$where;
        else
            $whereStr = null;
        $sql = "SELECT count(`".$this->primary."`) as total FROM `".$this->table."`".$whereStr." LIMIT 1";
        //echo $sql."\n";
        $customDB = $this->load->database($this->dbgroup, TRUE);
        $query = $customDB->query($sql);
        $customDB->close();
        if ($query->num_rows) {
            $row = $query->row_array();
            return $row['total'];
        }
    }

    /**
     * 根据主键id更新记录
     * @param  array $data 更新的信息
     * @param  int $id   主键id
     * @return true|false
     */
    public function update_by_where($data, $where)
    {
        $result = false;
        $customDB = $this->load->database($this->dbgroup, TRUE);
        $result = $customDB->update($this->table, $data, $where);
        $customDB->close();
        return $result;
    }

    /**
     * 插入记录
     * 插入成功返回记录id，失败返回false
     * @param array $data 要添加的记录数组[一维数组]
     * @return int | false
     */
    public function add($data)
    {
        $id = false;
        $customDB = $this->load->database($this->dbgroup, TRUE);
        if ($customDB->insert($this->table, $data)) {
            $id = $customDB->insert_id();
            $customDB->close();
            return $id;
        } else {
            return false;
        }
    }
    
    /**
     * 根据唯一索引更新数据
     *
     * @param array $data 更新字段需要包含唯一索引
     * @return boolean 布尔
     */
    public function update_by_unique($data) {
        $customDB = $this->load->database($this->dbgroup, TRUE);
        return $customDB->replace($this->table, $data);
    }
}