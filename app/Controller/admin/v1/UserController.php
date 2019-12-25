<?php
declare(strict_types=1);

namespace App\Controller\admin\v1;

use App\Service\Admin\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;

/**
 * @Controller()
 * Class UserController
 * @package App\Controller\admin\v1
 */
class UserController extends BaseController
{
    /**
     * @Inject()
     * @var UserService
     */
    public $service;

    /**
     * @RequestMapping(path="store", methods="post")
     * @Middleware(AuthMiddleware::class)
     * @param RequestInterface $request
     * @return int
     */
    public function store(RequestInterface $request)
    {
        $this->validateParam($request, [
            'user_name' => 'required',
            'real_name' => 'required',
            'phone' => 'required',
        ]);
        return $this->service->store($request);
    }

    /**
     * @RequestMapping(path="update", methods="post")
     * @Middleware(AuthMiddleware::class)
     * @param RequestInterface $request
     * @return int
     */
    public function update(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer',
            'user_name' => 'required',
            'real_name' => 'required',
            'phone' => 'required'
        ]);
        return $this->service->update($request);
    }
}