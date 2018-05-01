<?php
/**
 * Created by PhpStorm.
 * User: xuyanping
 * Date: 18/5/1
 * Time: 下午4:39
 */

namespace LazyData\Tool;

class MysqlTool
{
    protected $modelService = null;

    public function __construct( $modelService ){
        $this->modelService = $modelService;
    }

    public function fetchById( $id ){
        $sql = "select * from ".$this->modelService->getSource()." where id=? ";
        $record = new Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $this->modelService,
            $this->modelService->getReadConnection()->query($sql,[$id])
        );

        return isset($record) ?  $record->toArray()[0]:[];
    }

    public function describeColumns(){
        return $this->modelService->describeColumns();
    }

    public function sqlCreation(){
        $sql = "show create table ".$this->modelService->getSource();
        $record = new Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $this->modelService,
            $this->modelService->getReadConnection()->query($sql)
        );

        return isset($record) ?  $record->toArray()[0]:[];
    }
}