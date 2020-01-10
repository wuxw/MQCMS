<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $created_at 
 * @property int $first_create_user_id 
 * @property int $id 
 * @property int $is_hot 
 * @property int $status 
 * @property string $tag_desc 
 * @property string $tag_keyword 
 * @property string $tag_name 
 * @property string $tag_title 
 * @property int $tag_type 
 * @property int $updated_at 
 * @property int $used_count 
 */
class Tag extends Model
{
    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tag';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['created_at', 'first_create_user_id', 'id', 'is_hot', 'status', 'tag_desc', 'tag_keyword', 'tag_name', 'tag_title', 'tag_type', 'updated_at', 'used_count'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['created_at' => 'integer', 'first_create_user_id' => 'integer', 'id' => 'integer', 'is_hot' => 'integer', 'status' => 'integer', 'tag_type' => 'integer', 'updated_at' => 'integer', 'used_count' => 'integer'];
}