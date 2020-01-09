<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\UserApplication;
use App\Model\UserFavorite;
use App\Model\UserInfo;

class User extends \App\Model\User
{
    /**
     * @return \Hyperf\Database\Model\Relations\HasOne
     */
    public function userInfo()
    {
        return $this->hasOne(UserInfo::class, 'user_id', 'id');
    }

    /**
     * @return \Hyperf\Database\Model\Relations\HasMany
     */
    public function userApplication()
    {
        return $this->hasMany(UserApplication::class, 'user_id', 'id');
    }

    /**
     * @return \Hyperf\Database\Model\Relations\HasMany
     */
    public function userFavorite()
    {
        return $this->hasMany(UserFavorite::class, 'user_id', 'id');
    }
}