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
}