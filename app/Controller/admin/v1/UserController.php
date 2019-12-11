<?php


namespace App\Controller\admin\v1;


use App\Service\Admin\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class UserController extends BaseController
{
    /**
     * @Inject()
     * @var UserService
     */
    public $service;

    public function index(RequestInterface $request)
    {
        return $this->service->index($request);
    }

    /**
     * 创建用户
     * @param RequestInterface $request
     * @param array $data
     * @return mixed
     */
    public function store(RequestInterface $request)
    {
        $this->validateParam($request, [
            'user_name' => 'required',
            'real_name' => 'required',
            'phone' => 'required',
        ], 400, '参数错误');

        return $this->service->store($request);
    }

    /**
     * 更新用户
     * @param RequestInterface $request
     * @return mixed
     */
    public function update(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required',
            'user_name' => 'required',
            'real_name' => 'required',
            'phone' => 'required'
        ], 400, '参数错误');

        return $this->service->update($request);
    }
}