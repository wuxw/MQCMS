<?php
declare(strict_types=1);

namespace App\Controller\admin\v1;

use App\Service\Admin\TagService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class TagController extends BaseController
{
    /**
     * @Inject()
     * @var TagService
     */
    public $service;

    /**
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(RequestInterface $request)
    {
        return $this->service->index($request);
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function show(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer|alpha_numeric'
        ], 400, '参数错误');

        return $this->service->show($request);
    }

    /**
     * 新增
     * @param RequestInterface $request
     * @param array $data
     * @return mixed
     */
    public function store(RequestInterface $request)
    {
        $this->validateParam($request, [
            'tag_name' => 'required',
            'is_hot' => 'required',
            'status' => 'required',
        ], 400, '参数错误');

    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function delete(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required',
        ], 400, '参数错误');
        return $this->service->delete($request);
    }

    /**
     * 编辑
     * @param RequestInterface $request
     * @param array $data
     * @return mixed
     */
    public function update(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required',
            'tag_name' => 'required',
            'is_hot' => 'required',
            'status' => 'required',
        ], 400, '参数错误');

        return $this->service->update($request);
    }
}