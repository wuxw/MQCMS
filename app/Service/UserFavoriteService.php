<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\UserFavorite;
use Hyperf\Di\Annotation\Inject;

class UserFavoriteService extends BaseService
{
    /**
     * @Inject()
     * @var UserFavorite
     */
    public $table;
}