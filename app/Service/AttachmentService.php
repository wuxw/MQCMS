<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Attachment;
use Hyperf\Di\Annotation\Inject;

class AttachmentService extends BaseService
{
    /**
     * @Inject()
     * @var Attachment
     */
    public $model;
}