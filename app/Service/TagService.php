<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Tag;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class TagService extends BaseService
{
    /**
     * @Inject()
     * @var Tag
     */
    public $table;

    /**
     * 标签列表
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(RequestInterface $request)
    {
        $this->condition = ['status' => 1];
        $this->orderBy = 'is_hot DESC, id DESC';
        $data = parent::index($request);

        foreach ($data['data'] as $key => &$value) {
            $value['created_at'] = $value['created_at'] ? date('Y-m-d H:i:s', $value['created_at']) : '';
            $value['updated_at'] = $value['updated_at'] ? date('Y-m-d H:i:s', $value['updated_at']) : '';
        }
        return $data;
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function show(RequestInterface $request)
    {
        $this->condition = ['id' => $request->input('id')];
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
        $this->data = [
            'tag_name' => $request->input('tag_name'),
            'is_hot' => 0,
            'status' => 1,
            'first_create_user_id' => $request->getAttribute('uid'),
            'created_at' => time(),
            'updated_at' => time(),
        ];
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