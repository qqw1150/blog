<?php

/**
 * 业务服务类
 */
$di->set('userService',function (){
   $service=new \app\services\UserService();
   return $service;
});
