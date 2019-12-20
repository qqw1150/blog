<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/12/17
 * Time: 12:28
 */

namespace app\models;


use Phalcon\Mvc\Model;

abstract class Base extends Model
{
    /**
     * @param $data
     * @return mixed
     * @throws \ReflectionException
     */
    protected static function _toObj($data,$self){
        if(empty($data)){
            return null;
        }
        $obj=new $self();

        $class=new \ReflectionClass($self);
        $properties=$class->getProperties();
        foreach ($properties as $property){
            $propertyName=$property->getName();
            if(in_array($propertyName,array_keys($data)) && !empty($data[$propertyName])){
                call_user_func_array([$obj,'set'.ucfirst($propertyName)],[$data[$propertyName]]);
            }
        }
        return $obj;
    }
}