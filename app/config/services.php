<?php

use app\models\domains\RegisterForm;
use app\models\domains\LoginForm;
use Phalcon\Crypt;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Factory;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * 控制器调度器
 */
$di->setShared(
    'dispatcher',
    function () {
        $dispatcher = new \Phalcon\Mvc\Dispatcher();
        $dispatcher->setDefaultNamespace("app\controllers");
        $dispatcher->setDefaultController('index');

        $eventManager = new \Phalcon\Events\Manager();
        $eventManager->attach('dispatch:beforeDispatchLoop', function (\Phalcon\Events\Event $event, \Phalcon\Mvc\Dispatcher $dispatcher) {
            $controllerName = $dispatcher->getControllerClass();
            $actionName = $dispatcher->getActionName();

            try {
                /**
                 * @var \Phalcon\Http\Request $request
                 */
                $reflection = new ReflectionClass($controllerName);
                $request = $reflection->newInstance()->request;

                $formClass='app\models\domains\\'.ucfirst($actionName).'Form';
                $reflection2 = new ReflectionClass($formClass);
                $formInstance=$reflection2->newInstance();
                $formInstance->fillData($request->get());
                $dispatcher->setParams([$formInstance]);

//                if ($actionName === 'login') {
//                    $loginForm = new LoginForm();
//                    $loginForm->setAccount($request->get('account', 'trim', ''));
//                    $loginForm->setPassword($request->get('password', 'trim', ''));
//                    $rememberMe = $request->get('rememberMe', 'int!', 0);
//                    $loginForm->setRememberMe($rememberMe);
//                    $dispatcher->setParams([$loginForm]);
//                } else if ($actionName === 'register') {
//                    $registerForm = new RegisterForm();
//                    $registerForm->setAccount($request->get('account', 'trim', ''));
//                    $registerForm->setPassword($request->get('password', 'trim', ''));
//                    $registerForm->setCaptcha($request->get('captcha', 'trim', ''));
//                    $dispatcher->setParams([$registerForm]);
//                }

            } catch (Exception $e) {
                //控制器不存在什么都不做
            }
        });

        $dispatcher->setEventsManager($eventManager);

        return $dispatcher;
    }
);

/**
 * Setting up the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.volt' => function ($view) {
            $config = $this->getConfig();

            $volt = new VoltEngine($view, $this);

            $volt->setOptions([
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ]);

            return $volt;
        },
        '.phtml' => PhpEngine::class

    ]);

    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname,
        'charset' => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});


/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set('flash', function () {
    return new Flash([
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice' => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ]);
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    ini_set("session.save_path", "/workspace/sessions/");
    $options = [
        'lifetime' => 0,
        'adapter' => 'files',
    ];
    $session = Factory::load($options);
    $session->start();

    return $session;
});

/**
 * 文件日志器
 */
$di->set('logger', function () {
    $logger = new \Phalcon\Logger\Adapter\File(APP_PATH . '/logs/' . date('Y-m-d') . '.log');
    return $logger;
});

$di->set(
    'crypt',
    function () {
        $crypt = new Crypt();
        $crypt->setCipher('aes-256-ctr');
//        $crypt->useSigning(true);

        // Set a global encryption key
        $crypt->setKey(
            'ROOroot123!@#'
        );

        return $crypt;
    },
    true
);


$di->set(
    'cookies',
    function () {
        $cookies = new Cookies();
        $cookies->useEncryption(true);
        return $cookies;
    }
);

$di->set('redis', function () use ($di) {
    /**
     * @var \Phalcon\Config $config
     */
    $config = $di->get('config');
    $conf = $config->redis;
    $redis = new \Redis();
    $redis->connect($conf['host'],$conf['port'],$conf['timeout']);
    $redis->auth($conf['password']);
    return $redis;
});


#########################  用户自定义服务  ###################################
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