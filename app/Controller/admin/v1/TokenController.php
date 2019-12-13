<?php
declare(strict_types=1);

namespace App\Controller\admin\v1;

use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * Class TokenController
 * @package App\Controller\api\v1
 */
class TokenController extends BaseController
{
    /**
     * 获取token信息
     * @return array|bool|object|string
     */
    public function index(RequestInterface $request)
    {
        return [
            'info' => $this->getTokenInfo(),
            'token' => $this->getAuthToken(),
            'user_id' => $this->getUserId()
        ];
    }

    /**
     * 创建token
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function store(RequestInterface $request)
    {
        return [
            'token' => $this->createAuthToken([
                'id' => 1,
                'name' => 'mqcms',
                'url' => 'http://www.mqcms.net',
                'from' => 'admin'
            ]),
            'jwt_config' => $this->getJwtConfig()
        ];
    }

}