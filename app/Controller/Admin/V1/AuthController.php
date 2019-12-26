<?php
declare(strict_types=1);

/**
 * auth控制器
 */
namespace App\Controller\Admin\V1;

use App\Service\Admin\AuthService;
use App\Utils\JWT;
use App\Utils\Redis;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;

/**
 * @Controller()
 * Class AuthController
 * @package App\Controller\admin\v1
 */
class AuthController extends BaseController
{
    /**
     * @Inject()
     * @var AuthService
     */
    public $service;

    /**
     * @RequestMapping(path="register", methods="post")
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function register(RequestInterface $request)
    {
        $post = $this->validateParam($request, [
            'account' => 'required',
            'phone' => 'required',
            'password' => 'required|max:100|min:6'
        ]);
        list($lastInsertId, $uuid) = $this->service->register($request);
        $token = $this->createAuthToken(['id' => $lastInsertId, 'uuid' => $uuid], $request);
        return $this->response->json([
            'token' => $token,
            'expire_time' => JWT::$leeway,
            'uuid' => $uuid,
            'info' => [
                'name' => $post['account'],
                'avatar' => '',
                'access' => []
            ]
        ]);
    }

    /**
     * @RequestMapping(path="login", methods="post")
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function login(RequestInterface $request)
    {
        $post = $this->validateParam($request, [
            'account' => 'required',
            'password' => 'required|max:100|min:6'
        ]);

        $adminInfo = $this->service->login($request);
        $token = $this->createAuthToken(['id' => $adminInfo['id'], 'uuid' => $adminInfo['uuid']], $request);
        Redis::getContainer()->set('admin:token:' . $adminInfo['uuid'], $token);

        return $this->response->json([
            'token' => $token,
            'expire_time' => JWT::$leeway,
            'uuid' => $adminInfo['uuid'],
            'info' => [
                'name' => $post['account'],
                'avatar' => $adminInfo['avatar'],
                'access' => []
            ]
        ]);
    }

    /**
     * @RequestMapping(path="logout", methods="post")
     * @Middleware(AuthMiddleware::class)
     * @param RequestInterface $request
     * @return mixed
     */
    public function logout(RequestInterface $request)
    {
        return Redis::getContainer()->del('admin:token:' . $request->getAttribute('uuid'));
    }
}