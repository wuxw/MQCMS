<?php
declare(strict_types=1);

namespace App\Controller\Admin\V1;

use App\Service\Admin\AdminService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\AuthMiddleware;

/**
 * @Controller()
 * @Middleware(AuthMiddleware::class)
 * Class AdminController
 * @package App\Controller\Admin\V1
 */
class AdminController extends BaseController
{
    /**
     * @Inject()
     * @var AdminService
     */
    public $service;
}