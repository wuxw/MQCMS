<?php
declare(strict_types=1);

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
        ]);
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
            'id' => 'required|integer',
            'user_name' => 'required',
            'real_name' => 'required',
            'phone' => 'required'
        ]);
        return $this->service->update($request);
    }
}