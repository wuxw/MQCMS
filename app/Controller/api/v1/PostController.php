<?php
declare(strict_types=1);

namespace App\Controller\api\v1;

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
            'link_url' => 'url',
            'label_type' => 'required',
            'relation_tags' => 'required',
            'relation_tag_ids' => 'required',
            'address' => 'required',
            'addr_lat' => 'required',
            'addr_lng' => 'required',
            'attach_urls' => 'required',
            'attach_ids' => 'required',
            'is_publish' => 'required|integer',
        ]);
        return $this->service->store($request);
    }
}