<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\TagPostRelation;
use Hyperf\Di\Annotation\Inject;

class TagPostRelationService extends BaseService
{
    /**
     * @Inject()
     * @var TagPostRelation
     */
    public $table;
}