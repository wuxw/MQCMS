<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Service\BaseService;
use Hyperf\HttpServer\Contract\RequestInterface;

class AdminService extends BaseService
{
    public $table = 'admin';

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        return parent::index($request);
    }
}