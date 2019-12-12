<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Utils\Common;
use App\Utils\JWT;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BackendAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */

    protected $response;

    /**
     * @var string
     */
    protected $header = 'Authorization';

    /**
     * @var string
     */
    protected $pattern = '/^Bearer\s+(.*?)$/';

    /**
     * @var string
     */
    protected $realm = 'api';

    /**
     * @var string
     */
    public static $authToken = '';

    /**
     * @var string
     */
    public static $jwtKeyName = 'JWT_ADMIN_KEY';

    /**
     * @var string
     */
    public static $jwtKeyExp = 'JWT_ADMIN_EXP';

    /**
     * @var string
     */
    public static $jwtKeyAud = 'JWT_ADMIN_AUD';

    /**
     * @var string
     */
    public static $jwtKeyId = 'JWT_ADMIN_ID';

    /**
     * @var string
     */
    public static $jwtKeyIss = 'JWT_ADMIN_ISS';

    public static $tokenInfo = [];

    /**
     * @return array
     */
    public static function getJwtConfig()
    {
        return [
            'key' => env(self::$jwtKeyName),
            'exp' => env(self::$jwtKeyExp),
            'aud' => env(self::$jwtKeyAud),
            'iss' => env(self::$jwtKeyIss)
        ];
    }

    /**
     * AuthMiddleware constructor.
     * @param ContainerInterface $container
     * @param HttpResponse $response
     * @param RequestInterface $request
     */
    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
        $this->challenge();
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeader($this->header);
        $isValidToken = $this->authenticate($header);
        if (!$isValidToken) {
            throw new BusinessException(ErrorCode::UNAUTHORIZED, 'token验证失败');
        }
        $tokenInfo = $this->getAuthTokenInfo($this->request);
        $request = $request->withAttribute('uid', $tokenInfo['id']);
        return $handler->handle($request);
    }

    /**
     * setHeader
     */
    public function challenge()
    {
        $this->response->withHeader('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
    }

    /**
     * 验证token
     * @param $header
     * @return bool|null
     */
    public function authenticate($header)
    {
        if (!empty($header) && $header[0] !== null) {
            if ($this->pattern !== null) {
                if (preg_match($this->pattern, $header[0], $matches)) {
                    self::$authToken = $matches[1];
                } else {
                    return null;
                }
            }
            return true;
        }
        return null;
    }

    /**
     * 验证token有效性并获取token值
     * @param RequestInterface $request
     * @return array|bool|object|string
     */
    public function getAuthTokenInfo(RequestInterface $request)
    {
        $currentPath = Common::getCurrentPath($request);
        if ($currentPath !== env(self::$jwtKeyId)) {
            throw new BusinessException(ErrorCode::UNAUTHORIZED, 'token验证失败');
        }
        self::$tokenInfo = JWT::getTokenInfo(self::$authToken, self::getJwtConfig());
        return self::$tokenInfo;
    }


    /**
     * 创建token
     * @param $info
     * @return string
     */
    public static function createAuthToken($info)
    {
        return JWT::createToken($info, self::getJwtConfig());
    }
}