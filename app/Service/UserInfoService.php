<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\UserInfo;
use Hyperf\Di\Annotation\Inject;

class UserInfoService extends BaseService
{
    /**
     * @Inject()
     * @var UserInfo
     */
    public $model;
}