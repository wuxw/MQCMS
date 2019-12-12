<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Service\BaseService;
use Hyperf\HttpServer\Contract\RequestInterface;

class TagService extends BaseService
{
    protected $table = 'tag';

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        $type = $request->input('type', 'default');
        if ($type === 'hot') {
            $this->condition[] = ['is_hot', '=', 1];
        } else {
            $this->condition = [['status', '=', 1]];
        }
        $data = parent::index($request);

        foreach ($data['data'] as $key => &$value) {
            $value['created_at'] = $value['created_at'] ? date('Y-m-d H:i:s', $value['created_at']) : '';
            $value['updated_at'] = $value['updated_at'] ? date('Y-m-d H:i:s', $value['updated_at']) : '';
        }
        return $data;
    }

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function show(RequestInterface $request)
    {
        $id = $request->input('id');
        $this->condition = ['id' => $id];
        return parent::show($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function store(RequestInterface $request)
    {
        $data = [
            'tag_name' => $request->input('tag_name'),
            'is_hot' => $request->input('is_hot', 0),
            'status' => $request->input('status', 0),
            'first_create_user_id' => $request->getAttribute('uid'),
            'tag_type' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $this->select = ['id'];
        $this->condition = [['tag_name', '=', $data['tag_name']]];
        $tagInfo = parent::show($request);
        if ($tagInfo) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '标签名已经存在');
        }
        $this->data = $data;
        return parent::store($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function update(RequestInterface $request)
    {
        $id = $request->input('id');
        $data = [
            'tag_name' => $request->input('tag_name'),
            'is_hot' => $request->input('is_hot', 0),
            'status' => $request->input('status', 0),
            'tag_type' => 1,
            'updated_at' => time(),
        ];

        $this->condition = ['id' => $id];
        $this->data = $data;
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