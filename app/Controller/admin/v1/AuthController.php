<?php
declare(strict_types=1);

/**
 * auth控制器
 */
namespace App\Controller\admin\v1;

use App\Service\Admin\AuthService;
use App\Utils\JWT;
use App\Utils\Redis;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
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
     * 注册
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
        $lastInsertId = $this->service->register($request);
        $token = $this->createAuthToken(['id' => $lastInsertId], $request);
        return $this->response->json([
            'token' => $token,
            'expire_time' => JWT::$leeway,
            'uuid' => $lastInsertId,
            'info' => [
                'name' => $post['account'],
                'avatar' => '',
                'access' => []
            ]
        ]);
    }

    /**
     * 账号密码登录
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
        $token = $this->createAuthToken(['id' => $adminInfo['id']], $request);
        Redis::getRedis()->set('admin_token_' . $adminInfo['id'], $token);

        return $this->response->json([
            'token' => $token,
            'expire_time' => JWT::$leeway,
            'uuid' => $adminInfo['id'],
            'info' => [
                'name' => $post['account'],
                'avatar' => $adminInfo['avatar'],
                'access' => []
            ]
        ]);
    }

    /**
     * 退出登录
     * @param RequestInterface $request
     */
    public function logout(RequestInterface $request)
    {
        return Redis::getRedis()->del('admin_token_' . $request->getAttributes('uid'));
    }
}