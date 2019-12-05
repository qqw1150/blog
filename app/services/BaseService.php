<?php

/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/10
 * Time: 16:27
 */

namespace app\services;


use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Profiler;
use Phalcon\Di;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Logger\Adapter\File;
use Phalcon\Mvc\Model;

/**
 * Class BaseService
 * @property Di $di
 * @package app\services
 * @property Mysql $db
 */
class BaseService implements InjectionAwareInterface
{
    protected $di;
    protected $db;


    /**
     * Sets the dependency injector
     *
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function setDI(\Phalcon\DiInterface $dependencyInjector)
    {
        $this->di = $dependencyInjector;
        $this->db = $this->di->get('db');
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

    /**
     * @param Model $model
     */
    public function recordErr($model)
    {
        /**
         * @var File $logger
         */
        $logger = $this->di->get('logger');
        $messages = $model->getMessages();
        foreach ($messages as $message) {
            $logger->error($message->getMessage() . '-' . $message->getType() . '-' . $message->getField());
        }
    }

    public static function printSql()
    {
        $profiler = new Profiler();
        $profiles = $profiler->getProfiles();
        foreach ($profiles as $profile) {
            var_dump($profile->getSqlStatement());
        }
    }
}
