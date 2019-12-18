<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\BusinessException;
use App\Model\Tag;
use App\Utils\Common;
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
     * @Inject()
     * @var UserTagService
     */
    public $userTagService;

    /**
     * @Inject()
     * @var TagPostRelationService
     */
    public $tagPostRelationService;

    /**
     * @Inject()
     * @var PostService
     */
    public $postService;


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
     * 标签详情
     * @param RequestInterface $request
     * @return mixed
     */
    public function show(RequestInterface $request)
    {
        try {
            $uid = $request->getAttribute('uid', 0);
            $id = $request->input('id');

            $this->select = ['id', 'tag_name', 'is_hot', 'tag_type', 'used_count'];
            $this->condition = [
                ['id', '=', $id],
                ['status', '=', 1],
            ];

            $data = parent::show($request);
            $data['is_follow'] = 0;
            if ($uid) {
                // 查询是否关注
                $this->userTagService->condition = [
                    ['user_id', '=', $uid],
                    ['tag_id', '=', $id]
                ];
                $exist = $this->userTagService->multiTableJoinQueryBuilder()->exists();
                if ($exist) {
                    $data['is_follow'] = 1;
                }
            }

            //标签下帖子数
            $this->tagPostRelationService->condition = ['tag_id' => $id];
            $postNum = $this->userTagService->multiTableJoinQueryBuilder()->count();
            $data['post_num'] = $postNum;

            //标签关注人数
            $this->userTagService->condition = ['tag_id' => $id];
            $followedNum = $this->userTagService->multiTableJoinQueryBuilder()->count();
            $data['followed_num'] = $followedNum;
            return $data;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
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

    /**
     * 标签下帖子列表
     * @param RequestInterface $request
     * @return mixed
     */
    public function postList(RequestInterface $request)
    {
        try {
            $id = $request->input('id');
            $type = $request->input('type', 1);
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $page = $page < 1 ? 1 : $page;
            $limit = $limit > 100 ? 100 : $limit;

            $this->tagPostRelationService->condition = ['tag_id' => $id];
            $postIds = $this->tagPostRelationService->multiTableJoinQueryBuilder()->pluck('post_id')->toArray();

            $this->postService->condition = [
                ['status', '=', 1],
                ['is_publish', '=', 1],
            ];
            //推荐的帖子
            if ($type == 2) {
                $this->postService->orderBy = 'is_recommend DESC, id DESC';
            }
            $query = $this->postService->multiTableJoinQueryBuilder();
            $query = $query->whereIn('id', $postIds);
            $count = $query->count();
            $pagination = $query->paginate((int)$limit, $this->select, 'page', (int)$page)->toArray();
            $pagination['data'] = Common::calculateList($request, $pagination['data']);
            foreach ($pagination['data'] as $key => &$value) {
                $value['attach_urls'] = $value['attach_urls'] ? json_decode($value['attach_urls'], true) : [];
                $value['relation_tags_list'] = explode(',', $value['relation_tags']);
            }
            $pagination['total'] = $count;
            return $pagination;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

}