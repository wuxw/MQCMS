<?php
declare(strict_types=1);
/**
 * 用户控制器
 */
namespace App\Controller\Api\V1;

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
     * 用户信息（查看别人）
     * @param RequestInterface $request
     * @return mixed
     */
    public function showSelf(RequestInterface $request)
    {
        return $this->service->showSelf($request);
    }

    /**
     * 用户帖子列表
     * @param RequestInterface $request
     * @return mixed
     */
    public function postList(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer',
            'type' => 'integer'
        ]);

        return $this->service->postList($request);
    }

    /**
     * 我的关注用户列表
     * @param RequestInterface $request
     * @return mixed
     */
    public function myFollowedUserList(RequestInterface $request)
    {
        return $this->service->myFollowedUserList($request);
    }

    /**
     * 我的关注标签列表
     * @param RequestInterface $request
     * @return mixed
     */
    public function myFollowedTagList(RequestInterface $request)
    {
        return $this->service->myFollowedTagList($request);
    }

    /**
     * 关注用户
     * @param RequestInterface $request
     * @return mixed
     */
    public function follow(RequestInterface $request)
    {
        $this->validateParam($request, [
            'uid' => 'required|integer',
        ]);

        return $this->service->follow($request);
    }
}