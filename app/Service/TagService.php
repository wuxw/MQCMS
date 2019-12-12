<?php
declare(strict_types=1);

namespace App\Service;

use Hyperf\HttpServer\Contract\RequestInterface;

class TagService extends BaseService
{
    protected $table = 'tag';

    /**
     * 标签列表
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(RequestInterface $request)
    {
        $this->condition[] = ['status', '=', 1];
        $this->orderBy = 'is_hot DESC, id DESC';
        return parent::index($request);
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function show(RequestInterface $request)
    {
        $id = $request->input('id');
        $this->condition = ['id' => $id];
        return parent::show($request);
    }

    /**
     * 新增
     * @param RequestInterface $request
     * @param array $data
     * @return mixed
     */
    public function store(RequestInterface $request)
    {
        $data = [
            'tag_name' => $request->input('tag_name'),
            'is_hot' => 0,
            'status' => 1,
            'first_create_user_id' => $request->getAttribute('uid'),
            'created_at' => time(),
            'updated_at' => time(),
        ];
        $this->data = $data;
        return parent::store($request);
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function delete(RequestInterface $request)
    {
        $this->condition = ['id' => $request->input('id')];
        return parent::delete($request);
    }
}