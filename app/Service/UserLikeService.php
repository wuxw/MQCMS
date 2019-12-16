<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\UserLike;
use Hyperf\Di\Annotation\Inject;

class UserLikeService extends BaseService
{
    /**
     * @Inject()
     * @var UserLike
     */
    public $table;
}