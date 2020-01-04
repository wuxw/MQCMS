<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\Api\V1\IndexController@index');

// api接口
Router::addGroup('/api/', function () {
    require_once './config/routes/api.php';

    Router::addGroup('plugins/', function () {
        requireDirScript(dirname(__DIR__) . '/config/routes/plugins', 'api');
    });
});

/**
 * admin接口
 */
Router::addGroup('/admin/', function () {
    require_once './config/routes/admin.php';

    Router::addGroup('plugins/', function () {
        requireDirScript(dirname(__DIR__) . '/config/routes/plugins', 'admin');
    });
});
