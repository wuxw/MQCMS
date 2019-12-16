<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Model\Admin;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class AdminService extends BaseService
{
    /**
     * @Inject()
     * @var Admin
     */
    public $table;

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        $data = parent::index($request);

        foreach ($data['data'] as $key => &$value) {
            $value['register_time'] = $value['register_time'] ? date('Y-m-d H:i:s', (int)$value['register_time']) : '';
            $value['login_time'] = $value['login_time'] ? date('Y-m-d H:i:s', (int)$value['login_time']) : '';
        }
        return $data;
    }
}