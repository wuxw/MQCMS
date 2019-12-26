<?php
declare(strict_types=1);

namespace App\Controller\Admin\V1;

use App\Utils\Common;
use App\Utils\Redis;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @AutoController()
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
            'uid' => $request->getAttribute('uid'),
            'uuid' => $request->getAttribute('uuid')
        ];
    }

    /**
     * 创建token
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function store(RequestInterface $request)
    {
        $token = $this->createAuthToken([
            'id' => 1,
            'uuid' => 123,
            'name' => 'mqcms',
            'url' => 'http://www.mqcms.net',
            'from' => Common::getCurrentPath($request),
            'action' => Common::getCurrentActionName($request, get_class_methods(get_class($this)))
        ], $request);

        Redis::getContainer()->set('admin:token:123', $token);

        return [
            'token' => $token,
            'jwt_config' => $this->getJwtConfig($request),
            'uid' => $request->getAttribute('uid'),
            'uuid' => $request->getAttribute('uuid')
        ];
    }

}