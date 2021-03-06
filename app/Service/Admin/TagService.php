<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Tag;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class TagService extends BaseService
{
    /**
     * @Inject()
     * @var Tag
     */
    public $model;

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        // 搜索
        if ($request->has('search')) {
            $searchForm = $request->input('search');
            $this->multiSingleTableSearchCondition($searchForm);
        }
        return parent::index($request);
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
    public function store(RequestInterface $request)
    {
        $data = [
            'tag_name' => $request->input('tag_name'),
            'tag_title' => $request->input('tag_title'),
            'tag_desc' => $request->input('tag_desc'),
            'tag_keyword' => $request->input('tag_keyword'),
            'is_hot' => $request->input('is_hot', 0),
            'status' => $request->input('status', 0),
            'first_create_user_id' => $request->getAttribute('uid'),
            'tag_type' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $this->select = ['id'];
        $this->condition = ['tag_name' => $data['tag_name']];
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
        $this->data = [
            'tag_name' => $request->input('tag_name'),
            'tag_title' => $request->input('tag_title'),
            'tag_desc' => $request->input('tag_desc'),
            'tag_keyword' => $request->input('tag_keyword'),
            'is_hot' => $request->input('is_hot', 0),
            'status' => $request->input('status', 0),
            'tag_type' => $request->input('tag_type', 1),
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