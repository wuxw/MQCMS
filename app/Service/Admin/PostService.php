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
            $userTableName => ['uuid', 'user_name']
        ];

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
            'user_id'           => $request->getAttribute('uid'),
            'post_content'      => trim($request->input('post_content')),
            'link_url'          => trim($request->input('link_url')),
            'label_type'        => $request->input('label_type', 0),
            'is_good'           => $request->input('is_good', 0),
            'relation_tags'     => $request->input('relation_tags', ''),
            'address'           => $request->input('address', ''),
            'addr_lat'          => $request->input('addr_lat', ''),
            'addr_lng'          => $request->input('addr_lng', ''),
            'attach_urls'       => $request->input('attach_urls', ''),
            'is_publish'        => $request->input('is_publish', 0),
            'status'            => $request->input('status', 0),
            'is_recommand'      => $request->input('is_recommand', 0),
            'like_total'        => $request->input('like_total', 0),
            'favorite_total'    => $request->input('favorite_total', 0),
            'comment_total'     => $request->input('comment_total', 0),
            'created_at'        => time(),
            'updated_at'        => time(),
        ];
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
            'user_id'           => $request->getAttribute('uid'),
            'post_content'      => trim($request->input('post_content')),
            'link_url'          => trim($request->input('link_url')),
            'label_type'        => $request->input('label_type', 0),
            'is_good'           => $request->input('is_good', 0),
            'relation_tags'     => $request->input('relation_tags', ''),
            'address'           => $request->input('address', ''),
            'addr_lat'          => $request->input('addr_lat', ''),
            'addr_lng'          => $request->input('addr_lng', ''),
            'attach_urls'       => $request->input('attach_urls', ''),
            'is_publish'        => $request->input('is_publish', 0),
            'status'            => $request->input('status', 0),
            'is_recommand'      => $request->input('is_recommand', 0),
            'like_total'        => $request->input('like_total', 0),
            'favorite_total'    => $request->input('favorite_total', 0),
            'comment_total'     => $request->input('comment_total', 0),
            'updated_at'        => time(),
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