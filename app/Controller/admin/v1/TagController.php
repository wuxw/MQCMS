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
     * 新增
     * @param RequestInterface $request
     * @param array $data
     * @return mixed
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
     * @param RequestInterface $request
     * @return mixed
     */
    public function delete(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required|integer',
        ]);
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
            'tag_name' => 'required'
        ]);
        return $this->service->update($request);
    }
}