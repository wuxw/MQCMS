<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $user_id 
 * @property string $post_content 
 * @property string $link_url 
 * @property int $label_type 
 * @property int $is_good 
 * @property string $relation_tags 
 * @property string $address 
 * @property string $addr_lat 
 * @property string $addr_lng 
 * @property string $attach_urls 
 * @property string $attach_ids 
 * @property int $is_publish 
 * @property int $status 
 * @property int $is_recommend 
 * @property int $like_total 
 * @property int $favorite_total 
 * @property int $comment_total 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Post extends Model
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
    protected $table = 'post';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'post_content', 'link_url', 'label_type', 'is_good', 'relation_tags', 'address', 'addr_lat', 'addr_lng', 'attach_urls', 'attach_ids', 'is_publish', 'status', 'is_recommend', 'like_total', 'favorite_total', 'comment_total', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'label_type' => 'integer', 'is_good' => 'integer', 'is_publish' => 'integer', 'status' => 'integer', 'is_recommend' => 'integer', 'like_total' => 'integer', 'favorite_total' => 'integer', 'comment_total' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}