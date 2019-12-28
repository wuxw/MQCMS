<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property string $app_desc 
 * @property string $app_hash 
 * @property string $app_intro 
 * @property string $app_link 
 * @property string $app_logo 
 * @property string $app_name 
 * @property string $app_package_name 
 * @property string $app_package_path 
 * @property string $app_package_table 
 * @property string $app_version 
 * @property \Carbon\Carbon $created_at 
 * @property int $id 
 * @property int $status 
 * @property \Carbon\Carbon $updated_at 
 */
class UserApplication extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_application';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['app_desc', 'app_hash', 'app_intro', 'app_link', 'app_logo', 'app_name', 'app_package_name', 'app_package_path', 'app_package_table', 'app_version', 'created_at', 'id', 'status', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['created_at' => 'datetime', 'id' => 'integer', 'status' => 'integer', 'updated_at' => 'datetime'];
}