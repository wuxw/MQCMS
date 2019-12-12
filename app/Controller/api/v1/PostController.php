<?php
declare(strict_types=1);

namespace App\Controller\api\v1;

use App\Service\PostService;
use Hyperf\Di\Annotation\Inject;

class PostController extends BaseController
{
    /**
     * @Inject()
     * @var PostService
     */
    public $service;

}