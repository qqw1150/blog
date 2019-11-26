<?php

/**
 * 加载业务服务
 */
if ($dp = opendir(APP_PATH . '/services/')) {
    while (false !== ($file = readdir($dp))) {
        if ($file !== '.' || $file !== '..' || strtolower(substr($file, 0, 4)) == 'base') {
            $className   = substr($file, 0, strpos($file, '.php'));
            $serviceName = strtolower(substr($className, 0, 1)) . substr($className, 1);
            $fullClass   = "\app\services\\" . $className;
            $di->set($serviceName, function () use ($fullClass) {
                $service = new $fullClass();
                return $service;
            });
        }
    }
    closedir($dp);
}
