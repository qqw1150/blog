<?php
use Phalcon\Di\FactoryDefault;

ini_set("display_errors",true);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('ASSETS_PATH', BASE_PATH . '/assets');

//require __DIR__.'/vendor/autoload.php';
include APP_PATH.'/config/constant.php';

try {

    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();

    /**
     * Handle routes
     */
    include APP_PATH . '/config/router.php';

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';

    /**
     * @var \Phalcon\Config $config
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    echo str_replace(["\n","\r","\t"], '', $application->handle()->getContent());

}catch (\Exception $e) {
    if($e instanceof \app\exceptions\NotLoginException){
        header("location:/user/loginHtml");exit;
    }

    if($config->application->debug){
        throw $e;
    }
//    echo $e->getMessage() . '<br>';
//    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
