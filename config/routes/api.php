<?php
use Hyperf\HttpServer\Router\Router;
use App\Middleware\AuthMiddleware;

Router::addGroup('v1/', function () {

    // token
    Router::addGroup('token/', function () {
        // 创建token
        Router::post('store', 'App\Controller\Api\V1\tokencontroller@store');
        // 获取token信息
        Router::get('index', 'App\Controller\Api\V1\TokenController@index', ['middleware' => [AuthMiddleware::class]]);
    });

    // 标签
    Router::addGroup('tag/', function () {
        Router::get('index', 'App\Controller\Api\V1\TagController@index');
        Router::get('show', 'App\Controller\Api\V1\TagController@show');
        Router::post('store', 'App\Controller\Api\V1\TagController@store', ['middleware' => [AuthMiddleware::class]]);
        Router::delete('delete', 'App\Controller\Api\V1\TagController@delete', ['middleware' => [AuthMiddleware::class]]);
        Router::get('post-list', 'App\Controller\Api\V1\TagController@postList');
    });

    // 用户
    Router::addGroup('user/', function () {
        Router::get('index', 'App\Controller\Api\V1\UserController@index');
        Router::get('show', 'App\Controller\Api\V1\UserController@show');
        Router::get('show-self', 'App\Controller\Api\V1\UserController@showSelf', ['middleware' => [AuthMiddleware::class]]);
        Router::get('post-list', 'App\Controller\Api\V1\UserController@postList');
        Router::get('my-followed-user-list', 'App\Controller\Api\V1\UserController@myFollowedUserList', ['middleware' => [AuthMiddleware::class]]);
        Router::get('my-followed-tag-list', 'App\Controller\Api\V1\UserController@myFollowedTagList', ['middleware' => [AuthMiddleware::class]]);
        Router::post('store', 'App\Controller\Api\V1\UserController@store');
    });

    // auth
    Router::addGroup('auth/', function () {
        Router::post('login', 'App\Controller\Api\V1\AuthController@login');
        Router::post('register', 'App\Controller\Api\V1\AuthController@register');
        Router::post('mini-program', 'App\Controller\Api\V1\AuthController@miniProgram');
    });

    // 帖子
    Router::addGroup('post/', function () {
        Router::get('index', 'App\Controller\Api\V1\PostController@index');
        Router::post('store', 'App\Controller\Api\V1\PostController@store', ['middleware' => [AuthMiddleware::class]]);
        Router::delete('delete', 'App\Controller\Api\V1\PostController@delete');
        Router::post('update', 'App\Controller\Api\V1\PostController@update');
        Router::post('like', 'App\Controller\Api\V1\PostController@like', ['middleware' => [AuthMiddleware::class]]);
        Router::post('cancel-like', 'App\Controller\Api\V1\PostController@cancelLike', ['middleware' => [AuthMiddleware::class]]);
        Router::post('favorite', 'App\Controller\Api\V1\PostController@favorite', ['middleware' => [AuthMiddleware::class]]);
        Router::post('cancel-favorite', 'App\Controller\Api\V1\PostController@cancelFavorite', ['middleware' => [AuthMiddleware::class]]);
    });
});