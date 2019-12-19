<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\UserAuth;
use Hyperf\Di\Annotation\Inject;

class UserAuthService extends BaseService
{
    /**
     * @Inject()
     * @var UserAuth
     */
    public $table;
}