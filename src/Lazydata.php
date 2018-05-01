<?php

/**
 * Created by PhpStorm.
 * User: xuyanping
 * Date: 18/5/1
 * Time: 下午4:52
 */

namespace LazyData;

use LazyData\Helper\SqlHelper;
use LazyData\Helper\BuilderHelper;

class Lazydata
{
    protected $classname;  //class name
    protected $dir;        //the dir of the xml file
    protected $namespace;  //namspace default value :class name


    public function setClassName( $className ){
        $this->classname = $className;
        return $this;
    }

    public function setDir( $dir ){
        $this->dir = $dir;
        return $this;
    }

    public function setNamespace( $namespace ){
        $this->namespace = $namespace;
        return $this;
    }

    public function run(){
        $xmlFile = $this->dir.'/'.$this->classname.'.xml';

        if( ! file_exists($xmlFile)){
            echo '文件:'.$xmlFile.' 不存在！';
            return;
        }

        $xmlContent = file_get_contents($xmlFile);

        $xmlObject  = simplexml_load_string($xmlContent);
        $root       = $xmlObject->attributes()['root'];
        $tables     = $xmlObject->Table;

        $sqlHelper = new SqlHelper();
        $sqlHelper->dir(__DIR__.'/App/'.$root.'/sql/');

        foreach($tables as $table){
            $name   = $table->attributes()['name'];
            $index  = $table->attributes()['index'];
            $model  = $table->attributes()['model'];
            $modelPath = str_replace('/','\\',str_replace('/'.$name,"",$model));
            $model     = str_replace('/','\\',$model);

            $sqlHelper->model($model)->append();

            (new BuilderHelper($this->namespace,$name))
                ->index($index)
                ->model($modelPath.'\\',$name)
                ->target(__DIR__.'/App/'.$root.'/Builder/')
                ->generate();
        }

        $sqlHelper->execute();
    }
}