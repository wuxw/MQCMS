<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\TagPostRelation;

class Tag extends \App\Model\Tag
{
    public function postIds()
    {
        return $this->hasMany(TagPostRelation::class, 'tag_id', 'id');
    }
}