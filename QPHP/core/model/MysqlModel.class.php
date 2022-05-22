<?php


class MysqlModel extends BaseModel
{
    /**
     * 查询一条
     * @return array
     */
    public function findOne(){
        $join = '';
        if(!empty($this->join)){
            foreach ($this->join as $v){
                $join .= " {$v} ";
            }
        }
        $this->sql = "select {$this->field} from {$this->table}  {$this->asTable} {$join} where {$this->where}";
        return $this->db->getRow($this->sql);
    }

    /**
     * 查询多条
     * @return array
     */
    public function select(){
        $join = '';
        if(!empty($this->join)){
            foreach ($this->join as $v){
                $join .= " {$v} ";
            }
        }
        $this->sql = "select {$this->field} from {$this->table} {$this->asTable} {$join} where {$this->where}";

        return $this->db->getRows($this->sql);
    }



    //======================以下不完整============

    public function find($id){
        $this->sql  = "select * from {$this->table} where {$this->key}={$id}";
        return $this->db->getRow($this->sql );
    }

    public function findAll(){
        $this->sql  = "select * from {$this->table}";
        return $this->db->getRows($this->sql );
    }


    public function key($key){
        $this->key=$key;
        return $this;
    }

    public function add($arr){
        $add = $this->db->insert($this->table,$arr);
        if($add){
            return $this->db->getLastInsertId();//添加的id
        }
        return 0;
    }

    public function edit($arr,$where){
        return $this->db->update($this->table,$arr,$where);
    }


    public function del($where){
        return $this->db->delete($this->table,$where);
    }
}