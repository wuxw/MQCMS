<?php
declare(strict_types=1);

namespace App\Controller\Admin\V1;

use App\Service\Admin\AttachmentService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Middleware\AuthMiddleware;

/**
 * @Controller()
 * @Middleware(AuthMiddleware::class)
 * Class AttachmentController
 * @package App\Controller\Admin\V1
 */
class AttachmentController extends BaseController
{
    /**
     * @Inject()
     * @var AttachmentService
     */
    public $service;

    /**
     * @RequestMapping(path="update", methods="post")
     * @param RequestInterface $request
     * @return int
     */
    public function update(RequestInterface $request)
    {
        $this->validateParam($request, [
            'id' => 'required',
            'name' => 'required',
            'attach_url' => 'required',
            'status' => 'required',
        ]);
        return $this->service->update($request);
    }
}