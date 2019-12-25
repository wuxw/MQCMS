<?php
declare(strict_types=1);

namespace App\Controller\admin\v1;

use App\Service\Admin\PostService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;

/**
 * @Controller()
 * Class PostController
 * @package App\Controller\admin\v1
 */
class PostController extends BaseController
{
    /**
     * @Inject()
     * @var PostService
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
            'tag_name' => 'required',
            'is_hot' => 'required|integer',
            'status' => 'required',
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
            'id' => 'required',
            'content' => 'required',
            'status' => 'required',
        ]);
        return $this->service->update($request);
    }
}