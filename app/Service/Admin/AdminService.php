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
        $data = parent::index($request);

        foreach ($data['data'] as $key => &$value) {
            $value['created_at'] = $value['created_at'] ? date('Y-m-d H:i:s', $value['created_at']) : '';
            $value['updated_at'] = $value['updated_at'] ? date('Y-m-d H:i:s', $value['updated_at']) : '';
            $value['register_time'] = $value['register_time'] ? date('Y-m-d H:i:s', (int)$value['register_time']) : '';
            $value['login_time'] = $value['login_time'] ? date('Y-m-d H:i:s', (int)$value['login_time']) : '';
        }
        return $data;
    }
}