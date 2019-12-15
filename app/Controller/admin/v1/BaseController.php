<?php
declare(strict_types=1);

/**
 * 基类
 */
namespace App\Controller\admin\v1;

use App\Controller\AbstractController;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class BaseController extends AbstractController
{
    /**
     * @Inject()
     * @var BaseService
     */
    public $service;

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        return $this->service->index($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function store(RequestInterface $request)
    {
        return $this->service->store($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function update(RequestInterface $request)
    {
        return $this->service->update($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function delete(RequestInterface $request)
    {
        return $this->service->delete($request);
    }

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function show(RequestInterface $request)
    {
        return $this->service->show($request);
    }

}