<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $uuid
 * @property string $account
 * @property string $password
 * @property string $phone 
 * @property string $avatar 
 * @property int $status 
 * @property string $salt 
 * @property string $real_name 
 * @property string $register_time 
 * @property string $register_ip 
 * @property string $login_time 
 * @property string $login_ip 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Admin extends Model
{
    /**
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uuid', 'account', 'password', 'phone', 'avatar', 'status', 'salt', 'real_name', 'register_time', 'register_ip', 'login_time', 'login_ip', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}