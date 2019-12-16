<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\UserInfo;
use Hyperf\Di\Annotation\Inject;

class UserFollowService extends BaseService
{
    /**
     * @Inject()
     * @var UserInfo
     */
    public $table;
}