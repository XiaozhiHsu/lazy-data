<?php
/**
 * Created by PhpStorm.
 * User: xuyanping
 * Date: 18/5/1
 * Time: 下午4:47
 */

namespace LazyData\Helper;

use LazyData\Tool\MysqlTool;

class SqlHelper
{
    protected $model;
    protected $sql;
    protected $dir;
    protected $file;
    protected $table;

    public function dir( $dir){
        $this->dir  = $dir;
        return $this;
    }

    public function model($model)
    {
        $this->model    = new $model();
        return $this;
    }

    public function append(){
        $modelTool = new MysqlTool($this->model);
        $sql = $modelTool->sqlCreation();

        $createTableSql = $sql['Create Table'];
        $tableName      = $sql['Table'];
        $this->table[]  = $tableName;
        $this->sql[$tableName]  = $createTableSql.';'.PHP_EOL.PHP_EOL;
        return $this;
    }

    protected function _execute( $table,$createTableSql ){
        if(!is_dir($this->dir)){
            mkdir($this->dir,0755);
        }
        $file = $this->dir.'/'.$table.'.sql';
        if(!file_exists($file)){
            file_put_contents($file,$createTableSql);
        }
    }

    public function execute(){
        foreach($this->sql as $table => $createTableSql){
            $this->_execute($table,$createTableSql);
        }
    }
}