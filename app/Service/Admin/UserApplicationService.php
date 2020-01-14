<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Model\UserApplication;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;

class UserApplicationService extends BaseService
{
    /**
     * @Inject()
     * @var UserApplication
     */
    public $model;
}
