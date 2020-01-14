<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\UserTag;
use Hyperf\Di\Annotation\Inject;

class UserTagService extends BaseService
{
    /**
     * @Inject()
     * @var UserTag
     */
    public $model;
}