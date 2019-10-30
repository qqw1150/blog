<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/10
 * Time: 16:27
 */

namespace app\services;


use Phalcon\Di;
use Phalcon\Di\InjectionAwareInterface;

/**
 * Class BaseService
 * @property Di $di
 * @package app\services
 */
class BaseService implements InjectionAwareInterface
{
    protected $di;


    /**
     * Sets the dependency injector
     *
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function setDI(\Phalcon\DiInterface $dependencyInjector)
    {
       $this->di=$dependencyInjector;
    }

    /**
     * Returns the internal dependency injector
     *
     * @return \Phalcon\DiInterface
     */
    public function getDI()
    {
        return $this->di;
    }
}