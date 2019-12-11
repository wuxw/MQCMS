<?php
declare(strict_types=1);

/**
 * auth控制器
 */
namespace App\Controller\admin\v1;

use App\Service\Admin\AuthService;
use App\Utils\JWT;
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
        $this->validateParam($request, [
            'account' => 'required',
            'phone' => 'required',
            'password' => 'required|max_len,100|min_len,6'
        ], 400, '参数错误');

        $lastInsertId = $this->service->register($request);
        $token = $this->createAuthToken(['id' => $lastInsertId]);
        return $this->response->json([
            'token' => $token,
            'expire_time' => JWT::$leeway,
            'uuid' => $lastInsertId,
            'info' => [
                'name' => $request->input('account'),
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
        $this->validateParam($request, [
            'account' => 'required',
            'password' => 'required|max_len,100|min_len,6'
        ], 400, '参数错误');

        $adminInfo = $this->service->login($request);
        $token = $this->createAuthToken(['id' => $adminInfo['id']]);
        return $this->response->json([
            'token' => $token,
            'expire_time' => JWT::$leeway,
            'uuid' => $adminInfo['id'],
            'info' => [
                'name' => $request->input('account'),
                'avatar' => $adminInfo['avatar'],
                'access' => []
            ]
        ]);
    }

}