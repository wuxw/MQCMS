<?php
declare(strict_types=1);

namespace App\Controller\admin\v1;

use App\Service\Admin\AdminService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class AdminController extends BaseController
{
    /**
     * @Inject()
     * @var AdminService
     */
    public $service;

    public function index(RequestInterface $request)
    {
        return $this->service->index($request);
    }
}