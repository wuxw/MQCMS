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
    /**
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function show(RequestInterface $request)
    {
        $this->condition = ['id' => $request->input('id')];
        return parent::show($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function update(RequestInterface $request)
    {
        $id = $request->input('id');
        $this->data = [
            'account' => $request->input('account'),
            'real_name' => $request->input('real_name'),
            'phone' => $request->input('phone'),
            'status' => $request->input('status', 0),
            'updated_at' => time(),
        ];
        $this->condition = ['id' => $id];
        return parent::update($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function delete(RequestInterface $request)
    {
        $this->condition = ['id' => $request->input('id')];
        return parent::delete($request);
    }
}