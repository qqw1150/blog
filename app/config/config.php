<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

$secret = include_once __DIR__.'/.my.php';

return new \Phalcon\Config([
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => $secret['database']['host'],
        'username'    => $secret['database']['username'],
        'password'    => $secret['database']['password'],
        'dbname'      => $secret['database']['dbname'],
        'charset'     => $secret['database']['charset'],
    ],
    'redis'=>[
        'host'=>$secret['redis']['host'],
        'port'=>$secret['redis']['port'],
        'password'=>$secret['redis']['password'],
        'timeout'=>$secret['redis']['timeout'],
    ],
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'cacheDir'       => BASE_PATH . '/cache/',

        // This allows the baseUri to be understand project paths that are not in the root directory
        // of the webpspace.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        'baseUri'        => '/',
        'debug'=>true,
        'domain'=>'zhangyong.name',
        'domain2'=>'blog.zhangyong.name',
        'tag'=>array(
            '<label class="label label-default">{text}</label>',
            '<label class="label label-primary">{text}</label>',
            '<label class="label label-success">{text}</label>',
            '<label class="label label-info">{text}</label>',
            '<label class="label label-warning">{text}</label>',
            '<label class="label label-danger">{text}</label>',
        )
    ]
]);
