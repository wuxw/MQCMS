<?php
declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Service\PostService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class PostController extends BaseController
{
    /**
     * @Inject()
     * @var PostService
     */
    public $service;

    /**
     * 帖子列表分页
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(RequestInterface $request)
    {
        $this->validateParam($request, [
            'type' => 'nullable|string',
        ]);

        return $this->service->index($request);
    }

    /**
     * 新增
     * @param RequestInterface $request
     * @return mixed
     */
    public function store(RequestInterface $request)
    {
        $this->validateParam($request, [
            'post_content' => 'required',
            'label_type' => 'required',
            'address' => 'required',
            'addr_lat' => 'required',
            'addr_lng' => 'required',
            'attach_urls' => 'required',
            'attach_ids' => 'required',
            'is_publish' => 'required|integer',
        ]);
        return $this->service->store($request);
    }

    /**
     * 帖子详情
     * @param RequestInterface $request
     * @return mixed
     */
    public function show(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer'
        ]);

        return $this->service->show($request);
    }

    /**
     * 点赞帖子
     * @param RequestInterface $request
     * @return mixed
     */
    public function like(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer'
        ]);

        return $this->service->like($request);
    }

    /**
     * 取消点赞帖子
     * @param RequestInterface $request
     * @return mixed
     */
    public function cancelLike(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer'
        ]);

        return $this->service->cancelLike($request);
    }

    /**
     * 收藏帖子
     * @param RequestInterface $request
     * @return mixed
     */
    public function favorite(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer'
        ]);

        return $this->service->favorite($request);
    }

    /**
     * 取消收藏帖子
     * @param RequestInterface $request
     * @return mixed
     */
    public function cancelFavorite(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer'
        ]);

        return $this->service->cancelFavorite($request);
    }
}