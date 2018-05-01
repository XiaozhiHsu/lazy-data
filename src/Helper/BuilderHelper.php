<?php

/**
 * Created by PhpStorm.
 * User: xuyanping
 * Date: 18/5/1
 * Time: 下午4:43
 */

namespace LazyData\Helper;

use LazyData\Tool\FormatTool;
use LazyData\Tool\MysqlTool;

class BuilderHelper
{
    protected $domain         = '';
    protected $className      = '';
    protected $modelClassName = '';
    protected $namespace      = '';
    protected $targetDir      = '';
    protected $index          = '';

    public function __construct($namespace,$domain)
    {
        $this->domain         = $domain;
        $this->className      = $domain.'Builder';
        $this->namespace      = $namespace;
    }

    public function model( $modelNameSpace,$model ){
        $this->modelSpaceClassName = $modelNameSpace.$model;
        $this->modelClassName = $model;
        return $this;
    }

    public function target( $target ){
        $this->targetDir = $target;
        return $this;
    }

    public function index($index){
        if(is_numeric($index)){
            $index = intval($index);
        }
        $this->index = $index;
        return $this;
    }

    protected function fields(){
        $className    = $this->modelSpaceClassName;
        $modelService = new $className();

        $mysqlTool = new MysqlTool($modelService);
        $record    = $mysqlTool->fetchById($this->index);
        $columns   = $mysqlTool->describeColumns();

        $this->fields = [];
        foreach($columns as $column){
            $columnName = $column->getName();
            $this->fields[$columnName] = $this->columnValue($column,$record);
        }

        return $this;
    }

    protected function columnValue($column,$record){
        $columnName = $column->getName();

        if($column->isNumeric() && isset($record[$columnName])){
            return intval($record[$columnName]);
        }

        if($column->isNumeric() && !isset($record[$columnName])){
            return 0;
        }

        if(!$column->isNumeric() && isset($record[$columnName])){
            return $record[$columnName];
        }

        return '';
    }

    protected function fieldValue($key,$value){
        $content = '';

        if(is_string($key) && is_string($value)){
            $value  = "'{$value}'";
        }elseif (is_string($key) && is_numeric($value)){
            $value  = intval($value);
        }

        if(is_string($key)){
            $content .= Tool::space(4)."protected ".'$'."{$key} = {$value};".PHP_EOL;
        }else{
            $content .= Tool::space(4)."protected ".'$'."{value} = {$value};".PHP_EOL;
        }
        return $content;
    }


    public function template(){
        $content  = '<?php'.PHP_EOL.PHP_EOL;
        $content .= 'namespace '.$this->namespace.'\\Test\\Builder;'.PHP_EOL.PHP_EOL;
        $content .= 'use '.$this->modelSpaceClassName.';'.PHP_EOL;
        $content .= "class {$this->className}\n{".PHP_EOL;
        $fields  = $this->fields;

        foreach ($fields as $key => $field ) {
            $content .= $this->fieldValue($key,$field);
        }

        $constructParam = lcfirst($this->modelClassName);

        $content .= PHP_EOL;

        //构造函数
        $content .= FormatTool::space(4).'public function __construct($'.$constructParam.' = null )'.PHP_EOL;
        $content .= FormatTool::space(4).'{'.PHP_EOL;
        $content .= FormatTool::space(8).'if(is_null($'.$constructParam.')){'.PHP_EOL;
        $content .= FormatTool::space(12).'$'.$constructParam.' = new '.$this->modelClassName.'();'.PHP_EOL;
        $content .= FormatTool::space(8).'}'.PHP_EOL;
        $content .= FormatTool::space(8).'$this->model = $'.$constructParam.';'.PHP_EOL;
        $content .= FormatTool::space(4).'}'.PHP_EOL.PHP_EOL;

        //属性赋值
        $content .= FormatTool::space(4).'public function __call($name,$arguments)'.PHP_EOL;
        $content .= FormatTool::space(4).'{'.PHP_EOL;
        $content .= FormatTool::space(8).'$pattern = \'/[A-Z][a-z]{0,}/\';'.PHP_EOL;
        $content .= FormatTool::space(8).'preg_match_all($pattern,$name,$matches);'.PHP_EOL;
        $content .= FormatTool::space(8).'$field   = \'\';'.PHP_EOL;
        $content .= FormatTool::space(8).'foreach ($matches[0] as $key => $match){;'.PHP_EOL;
        $content .= FormatTool::space(12).'$matches[$key] = strtolower($match);'.PHP_EOL;
        $content .= FormatTool::space(8).'}'.PHP_EOL.PHP_EOL;
        $content .= FormatTool::space(8).'$field   = implode(\'_\',$matches);'.PHP_EOL;
        $content .= FormatTool::space(8).'if(!empty($arguments)){'.PHP_EOL;
        $content .= FormatTool::space(12).'$this->$field = $arguments[0];'.PHP_EOL;
        $content .= FormatTool::space(8).'}'.PHP_EOL;
        $content .= FormatTool::space(8).'return $this;'.PHP_EOL;
        $content .= FormatTool::space(4).'}'.PHP_EOL.PHP_EOL;

        //获取属性值
        $content .= FormatTool::space(4).'public function __call($name,$arguments)'.PHP_EOL;
        $content .= FormatTool::space(4).'{'.PHP_EOL;
        $content .= FormatTool::space(8).'$pattern = \'/[A-Z][a-z]{0,}/\';'.PHP_EOL;
        $content .= FormatTool::space(8).'preg_match_all($pattern,$name,$matches);'.PHP_EOL;
        $content .= FormatTool::space(8).'$field   = \'\';'.PHP_EOL;
        $content .= FormatTool::space(8).'foreach ($matches[0] as $key => $match){;'.PHP_EOL;
        $content .= FormatTool::space(12).'$matches[$key] = strtolower($match);'.PHP_EOL;
        $content .= FormatTool::space(8).'}'.PHP_EOL.PHP_EOL;
        $content .= FormatTool::space(8).'$field   = implode(\'_\',$matches);'.PHP_EOL;

        $content .= FormatTool::space(8).' if(strpos($name,"get")!==false && empty($arguments))'.PHP_EOL;
        $content .= FormatTool::space(8).' {'.PHP_EOL;
        $content .= FormatTool::space(12).' return $this->$field;'.PHP_EOL;
        $content .= FormatTool::space(8).' }'.PHP_EOL.PHP_EOL;

        $content .= FormatTool::space(8).'if(strpos($name,"set")!==false &&!empty($arguments)){'.PHP_EOL;
        $content .= FormatTool::space(12).'$this->$field = $arguments[0];'.PHP_EOL;
        $content .= FormatTool::space(8).'}'.PHP_EOL;
        $content .= FormatTool::space(8).'return $this;'.PHP_EOL;
        $content .= FormatTool::space(4).'}'.PHP_EOL.PHP_EOL;

        //build属性
        $content .= FormatTool::space(4).'public function build()'.PHP_EOL;
        $content .= FormatTool::space(4).'{'.PHP_EOL;

        foreach($fields as $key => $field){
            if(is_string($key)){
                $content .= FormatTool::space(8).'$this->model->'.$key.' = $this->'.$key.';'.PHP_EOL;
            }else{
                $content .= FormatTool::space(8).'$this->model->'.$field.' = $this->'.$field.';'.PHP_EOL;
            }

        }
        $content .= FormatTool::space(8).'$this->model->save();'.PHP_EOL;
        $content .= FormatTool::space(8).'return $this;'.PHP_EOL;
        $content .= FormatTool::space(4).'}'.PHP_EOL.PHP_EOL;

        $content .= "}".PHP_EOL;
        return $content;
    }

    public function generate(){
        $this->fields();
        $fileName = $this->targetDir.$this->className.'.php';
        if(file_exists($fileName)){
            echo "{$fileName} 已存在～".PHP_EOL;
            return ;
        }

        try{
            $content = $this->template();
            file_put_contents($fileName,$content);

            if(file_exists($fileName)){
                echo "{$fileName}  文件生成完成！".PHP_EOL;
                return ;
            }

        }catch (\Exception $e){
            echo "未知错误{$fileName}  文件生成失败！".PHP_EOL;
        }

    }
}