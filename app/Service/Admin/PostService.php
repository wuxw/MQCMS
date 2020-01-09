<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Post;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class PostService extends BaseService
{
    /**
     * @Inject()
     * @var Post
     */
    public $table;

    /**
     * @Inject()
     * @var UserService
     */
    public $userService;

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        $tableName = $this->table->getTable();
        $userTableName = $this->userService->table->getTable();
        $this->condition = [
            [$tableName . '.status', '=', 1]
        ];
        $this->joinTables = [
            $userTableName => [$tableName . '.user_id', '=', $userTableName . '.id']
        ];
        $this->orderBy = [
            $tableName => ['id' => 'DESC']
        ];
        $this->select = [
            $tableName => ['*'],
            $userTableName => ['id', 'uuid', 'user_name']
        ];

        // 搜索
        if ($request->has('search')) {
            $searchForm = $request->input('search');
            $this->condition[] = $this->multiSingleTableSearchCondition($searchForm);
        }
        return parent::index($request);
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
        if (!$request->getAttribute('uid')) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '用户不存在');
        }
        $data = [
            'tag_name' => trim($request->input('tag_name')),
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
        $data = [
            'tag_name' => trim($request->input('tag_name')),
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