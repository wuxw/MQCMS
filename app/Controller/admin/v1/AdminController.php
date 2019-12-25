<?php
declare(strict_types=1);

namespace App\Controller\admin\v1;

use App\Service\Admin\AdminService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;

/**
 * @Controller()
 * Class AdminController
 * @package App\Controller\admin\v1
 */
class AdminController extends BaseController
{
    /**
     * @Inject()
     * @var AdminService
     */
    public $service;
}