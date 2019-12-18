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
use App\Middleware\AuthMiddleware;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\api\v1\IndexController@index');

// api接口
Router::addGroup('/api/', function () {
    Router::addGroup('v1/', function () {

        // token
        Router::addGroup('token/', function () {
            // 创建token
            Router::post('store', 'App\Controller\api\v1\tokencontroller@store');
            // 获取token信息
            Router::get('index', 'App\Controller\api\v1\TokenController@index', ['middleware' => [AuthMiddleware::class]]);
        });

        // 标签
        Router::addGroup('tag/', function () {
            Router::get('index', 'App\Controller\api\v1\TagController@index');
            Router::get('show', 'App\Controller\api\v1\TagController@show');
            Router::post('store', 'App\Controller\api\v1\TagController@store', ['middleware' => [AuthMiddleware::class]]);
            Router::delete('delete', 'App\Controller\api\v1\TagController@delete', ['middleware' => [AuthMiddleware::class]]);
            Router::get('post-list', 'App\Controller\api\v1\TagController@postList');
        });

        // 用户
        Router::addGroup('user/', function () {
            Router::get('index', 'App\Controller\api\v1\UserController@index');
            Router::get('show', 'App\Controller\api\v1\UserController@show');
            Router::get('show-self', 'App\Controller\api\v1\UserController@showSelf', ['middleware' => [AuthMiddleware::class]]);
            Router::get('post-list', 'App\Controller\api\v1\UserController@postList');
            Router::get('my-followed-user-list', 'App\Controller\api\v1\UserController@myFollowedUserList', ['middleware' => [AuthMiddleware::class]]);
            Router::get('my-followed-tag-list', 'App\Controller\api\v1\UserController@myFollowedTagList', ['middleware' => [AuthMiddleware::class]]);
            Router::post('store', 'App\Controller\api\v1\UserController@store');
        });

        // auth
        Router::addGroup('auth/', function () {
            Router::post('login', 'App\Controller\api\v1\AuthController@login');
            Router::post('register', 'App\Controller\api\v1\AuthController@register');
            Router::post('mini-program', 'App\Controller\api\v1\AuthController@miniProgram');
        });

        // 帖子
        Router::addGroup('post/', function () {
           Router::get('index', 'App\Controller\api\v1\PostController@index');
           Router::post('store', 'App\Controller\api\v1\PostController@store', ['middleware' => [AuthMiddleware::class]]);
           Router::delete('delete', 'App\Controller\api\v1\PostController@delete');
           Router::post('update', 'App\Controller\api\v1\PostController@update');
        });
    });
});

// 后台接口
Router::addGroup('/admin/', function () {
    Router::addGroup('v1/', function () {

        // token
        Router::addGroup('token/', function () {
            // 创建token
            Router::post('store', 'App\Controller\admin\v1\tokencontroller@store');
            // 获取token信息
            Router::get('index', 'App\Controller\admin\v1\TokenController@index', ['middleware' => [AuthMiddleware::class]]);
        });

        // 管理员
        Router::addGroup('admin/', function () {
            Router::get('index', 'App\Controller\admin\v1\AdminController@index');
            Router::post('store', 'App\Controller\admin\v1\AdminController@store');
            Router::post('update', 'App\Controller\admin\v1\AdminController@update');
            Router::post('delete', 'App\Controller\admin\v1\AdminController@delete');
        }, ['middleware' => [AuthMiddleware::class]]);

        // auth
        Router::addGroup('auth/', function () {
            Router::post('login', 'App\Controller\admin\v1\AuthController@login');
            Router::post('register', 'App\Controller\admin\v1\AuthController@register');
            Router::post('logout', 'App\Controller\admin\v1\AuthController@logout', ['middleware' => [AuthMiddleware::class]]);
        });

        // 用户
        Router::addGroup('user/', function () {
            Router::get('index', 'App\Controller\admin\v1\UserController@index');
            Router::post('store', 'App\Controller\admin\v1\UserController@store');
            Router::post('update', 'App\Controller\admin\v1\UserController@update');
        }, ['middleware' => [AuthMiddleware::class]]);

        // 标签
        Router::addGroup('tag/', function () {
            Router::get('index', 'App\Controller\admin\v1\TagController@index');
            Router::get('show', 'App\Controller\admin\v1\TagController@show');
            Router::post('store', 'App\Controller\admin\v1\TagController@store');
            Router::post('delete', 'App\Controller\admin\v1\TagController@delete');
            Router::post('update', 'App\Controller\admin\v1\TagController@update');
        }, ['middleware' => [AuthMiddleware::class]]);

        // 内容
        Router::addGroup('post/', function () {
            Router::get('index', 'App\Controller\admin\v1\PostController@index');
            Router::get('show', 'App\Controller\admin\v1\PostController@show');
            Router::post('store', 'App\Controller\admin\v1\PostController@store');
            Router::post('delete', 'App\Controller\admin\v1\PostController@delete');
            Router::post('update', 'App\Controller\admin\v1\PostController@update');
        }, ['middleware' => [AuthMiddleware::class]]);

        // 附件
        Router::addGroup('attachment/', function () {
            Router::get('index', 'App\Controller\admin\v1\AttachmentController@index');
            Router::get('show', 'App\Controller\admin\v1\AttachmentController@show');
            Router::post('store', 'App\Controller\admin\v1\AttachmentController@store');
            Router::post('delete', 'App\Controller\admin\v1\AttachmentController@delete');
            Router::post('update', 'App\Controller\admin\v1\AttachmentController@update');
        }, ['middleware' => [AuthMiddleware::class]]);

        // 评价
        Router::addGroup('comment/', function () {
            Router::get('index', 'App\Controller\admin\v1\CommentController@index');
            Router::get('show', 'App\Controller\admin\v1\CommentController@show');
            Router::post('store', 'App\Controller\admin\v1\CommentController@store');
            Router::post('delete', 'App\Controller\admin\v1\CommentController@delete');
            Router::post('update', 'App\Controller\admin\v1\CommentController@update');
        }, ['middleware' => [AuthMiddleware::class]]);
    });
});

