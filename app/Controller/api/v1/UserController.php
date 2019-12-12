<?php
declare(strict_types=1);
/**
 * 用户控制器
 */
namespace App\Controller\api\v1;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class UserController extends BaseController
{
    /**
     * @Inject()
     * @var UserService
     */
    public $service;


    /**
     * 推荐用户列表
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(RequestInterface $request)
    {
        return parent::index($request);
    }

    /**
     * 用户信息
     * @param RequestInterface $request
     * @return mixed
     */
    public function show(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer'
        ]);

        return parent::show($request);
    }
}