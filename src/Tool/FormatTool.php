<?php
/**
 * Created by PhpStorm.
 * User: xuyanping
 * Date: 18/5/1
 * Time: 下午4:40
 */

namespace LazyData\Tool;

class FormatTool
{
    static public function space($number){
        $tags = "";
        while($number>0){
            $tags.= " ";
            $number--;
        }
        return $tags;
    }
}