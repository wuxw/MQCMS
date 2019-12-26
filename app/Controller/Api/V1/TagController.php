<?php
declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Service\TagService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * Class TagController
 * @package App\Controller\Api\V1
 */
class TagController extends BaseController
{
    /**
     * @Inject()
     * @var TagService
     */
    public $service;

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
        ]);
        return $this->service->store($request);
    }

    /**
     * 标签下帖子列表
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

}