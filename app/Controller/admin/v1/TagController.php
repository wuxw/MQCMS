<?php
declare(strict_types=1);

namespace App\Controller\admin\v1;

use App\Service\Admin\TagService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;

/**
 * @Controller()
 * Class TagController
 * @package App\Controller\admin\v1
 */
class TagController extends BaseController
{
    /**
     * @Inject()
     * @var TagService
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
            'tag_name' => 'required'
        ]);
        return $this->service->update($request);
    }
}