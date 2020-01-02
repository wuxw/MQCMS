<?php
declare(strict_types=1);

/**
 * 基类
 */
namespace App\Controller\Api\Plugins;

use App\Controller\AbstractController;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;

/**
 * @Controller()
 * @Middleware(AuthMiddleware::class)
 * Class BaseController
 * @package App\Controller\Api\Plugins
 */
class BaseController extends AbstractController
{
    /**
     * @Inject()
     * @var BaseService
     */
    public $service;

    /**
     * @RequestMapping(path="index", methods="get, post")
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        return $this->service->index($request);
    }

    /**
     * @RequestMapping(path="store", methods="post")
     * @param RequestInterface $request
     * @return int
     */
    public function store(RequestInterface $request)
    {
        return $this->service->store($request);
    }

    /**
     * @RequestMapping(path="update", methods="post")
     * @param RequestInterface $request
     * @return int
     */
    public function update(RequestInterface $request)
    {
        return $this->service->update($request);
    }

    /**
     * @RequestMapping(path="delete", methods="post")
     * @param RequestInterface $request
     * @return int
     */
    public function delete(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer',
        ]);
        return $this->service->delete($request);
    }

    /**
     * @RequestMapping(path="show", methods="get")
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function show(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer'
        ]);
        return $this->service->show($request);
    }
}