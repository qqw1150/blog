<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/12
 * Time: 18:26
 */

namespace app\models\domains;


abstract class Base
{
    /**
     * @param  string $class 当前类类名
     * @return array|false
     */
    protected function _toArray($class)
    {
        try {
            $reflection = new \ReflectionClass($class);
            $properties = $reflection->getProperties();
            $arr = [];
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                $propertyValue = $this->$propertyName;
                $arr[$propertyName] = $propertyValue;
            }

            if (count($arr) === 0) {
                return false;
            }

            return $arr;
        } catch (\ReflectionException $e) {
            return false;
        }

    }

    public abstract function toArray();

}