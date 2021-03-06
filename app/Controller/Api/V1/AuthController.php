<?php
declare(strict_types=1);

/**
 * auth控制器
 */
namespace App\Controller\Api\V1;

use App\Service\AuthService;
use App\Utils\JWT;
use App\Utils\Redis;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller
 * Class AuthController
 * @package App\Controller\Api\V1
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
            'user_name' => 'required',
            'password' => 'required|max:100|min:6'
        ]);

        list($lastInsertId, $uuid) = $this->service->register($request);
        $token = $this->createAuthToken(['id' => $lastInsertId, 'uuid' => $uuid], $request);

        return $this->response->json([
            'token' => $token,
            'expire_time' => JWT::$leeway,
            'uuid' => $lastInsertId,
            'info' => [
                'name' => $post['user_name'],
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
            'user_name' => 'required',
            'password' => 'required|max:100|min:6'
        ]);

        $userInfo = $this->service->login($request);
        $token = $this->createAuthToken(['id' => $userInfo['id'], 'uuid' => $userInfo['uuid']], $request);
        Redis::getContainer()->set('api:token:' . $userInfo['uuid'], $token);

        return $this->response->json([
            'token' => $token,
            'expire_time' => JWT::$leeway
        ]);
    }

    /**
     * 小程序登录
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function miniProgram(RequestInterface $request)
    {
        $this->validateParam($request, [
            'code' => 'required'
        ]);
        return $this->service->miniProgram($request);
    }
}