<?php
declare(strict_types = 1);

namespace App\Service;

use App\Exception\BusinessException;
use App\Model\Post;
use App\Utils\Common;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class PostService extends BaseService
{
    /**
     * @Inject()
     * @var Post
     */
    public $model;

    /**
     * @Inject()
     * @var TagPostRelationService
     */
    public $tagPostRelationService;

    /**
     * @Inject()
     * @var UserLikeService
     */
    public $userLikeService;

    /**
     * @Inject()
     * @var UserFavoriteService
     */
    public $userFavoriteService;

    /**
     * @Inject()
     * @var UserInfoService
     */
    public $userInfoService;

    /**
     * 帖子列表分页
     * @param RequestInterface $request
     * @return mixed
     */
    public function index(RequestInterface $request)
    {
        // 类型： recommend: 推荐 default: 默认
        $type = trim($request->input('type', 'default'));

        $this->condition = [
            ['status', '=', 1],
            ['is_publish', '=', 1],
        ];
        if ($type === 'recommend') {
            $this->condition[] = ['is_recommend', '=', 1];
        }
        $list = parent::index($request);

        foreach ($list['data'] as $key => &$value) {
            $value['attach_urls'] = $value['attach_urls'] ? json_decode($value['attach_urls'], true) : [];
            $value['relation_tags_list'] = explode(',', $value['relation_tags']);
        }
        $list['data'] = Common::calculateList($request, $list['data']);
        return $list;
    }

    /**
     * 新增
     * @param RequestInterface $request
     * @return mixed
     */
    public function store(RequestInterface $request)
    {
        $relationTagIds = explode(',', $request->input('relation_tag_ids', ''));
        $uid = $request->getAttribute('uid', 0);
        $isPublish = trim($request->input('is_publish', 1));
        $this->data = [
            'user_id'       => $uid,
            'post_content'  => trim($request->input('post_content')),
            'link_url'      => trim($request->input('link_url', '')),
            'label_type'    => trim($request->input('label_type', 1)),
            'is_good'       => $request->has('link_url') ? 1 : 0,
            'relation_tags' => trim($request->input('relation_tags', '')),
            'address'       => trim($request->input('address', '')),
            'addr_lat'      => trim($request->input('addr_lat', '')),
            'addr_lng'      => trim($request->input('addr_lng', '')),
            'attach_urls'   => trim($request->input('attach_urls', '')),
            'attach_ids'    => trim($request->input('attach_ids', '')),
            'is_publish'    => $isPublish,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        Db::beginTransaction();
        try {
            $lastInsertId = parent::store($request);

            // 存储tag
            if ($request->has('relation_tag_ids') && $request->has('relation_tags')) {
                foreach ($relationTagIds as $value) {
                    // todo
                    $this->tagPostRelationService->data = [
                        'user_id' => $uid,
                        'tag_id' => $value,
                        'post_id' => $lastInsertId,
                        'created_at' => time(),
                        'updated_at' => time()
                    ];
                }
                $this->tagPostRelationService->insert($request);
            }
            //更新我的发帖数
            $this->userInfoService->condition = ['user_id' => $uid];
            $this->userInfoService->multiTableJoinQueryBuilder()->increment('post_num');

            Db::commit();
            return $lastInsertId;

        } catch (\Exception $e) {
            Db::rollBack();
            $message = $isPublish ? '发布失败' : '保存失败';
            throw new BusinessException((int)$e->getCode(), $message);
        }
    }

    /**
     * 点赞帖子
     * @param RequestInterface $request
     * @return mixed
     */
    public function like(RequestInterface $request)
    {
        try {
            $uid = $request->getAttribute('uid');
            $id = $request->input('id');
            $this->userLikeService->data = [
                'user_id' => $uid,
                'post_id' => $id,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            $this->condition = ['id' => $id];

            // 获取userId
            $userId = $this->multiTableJoinQueryBuilder()->value('user_id');

            Db::beginTransaction();

            // 插入
            $this->userLikeService->insert($request);

            //更新帖子点赞数 +1
            $this->multiTableJoinQueryBuilder()->increment('like_total');

            //更新帖子用户获赞数
            $this->userInfoService->multiTableJoinQueryBuilder()->increment('like_num', 1, ['user_id' => $userId]);

            //更新我点赞数
            $this->userInfoService->multiTableJoinQueryBuilder()->increment('my_like_num', 1, ['user_id' => $userId]);

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollBack();
            throw new BusinessException((int)$e->getCode(), '操作失败');
        }
    }

    /**
     * 取消点赞帖子
     * @param RequestInterface $request
     * @return mixed
     */
    public function cancelLike(RequestInterface $request)
    {
        try {
            $uid = $request->getAttribute('uid');
            $id = $request->input('id');

            // 获取userId
            $userId = $this->multiTableJoinQueryBuilder()->value('user_id');

            Db::beginTransaction();
            $this->userLikeService->condition = [
                ['user_id' => $uid],
                ['post_id' => $id],
            ];
            // 删除帖子点赞
            $this->userLikeService->delete($request);

            //更新帖子点赞数 -1
            $this->condition = ['id' => $id];
            $this->multiTableJoinQueryBuilder()->decrement('like_total');

            //更新帖子用户获赞数
            $this->userInfoService->multiTableJoinQueryBuilder()->decrement('like_num', 1, ['user_id' => $userId]);

            //更新我点赞数
            $this->userInfoService->multiTableJoinQueryBuilder()->decrement('my_like_num', 1, ['user_id' => $userId]);

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollBack();
            throw new BusinessException((int)$e->getCode(), '操作失败');
        }
    }

    /**
     * 收藏帖子
     * @param RequestInterface $request
     * @return mixed
     */
    public function favorite(RequestInterface $request)
    {
        try {
            $uid = $request->getAttribute('uid');
            $id = $request->input('id');
            $this->userFavoriteService->condition = [
                'user_id' => $uid,
                'post_id' => $id,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            Db::beginTransaction();

            $this->userFavoriteService->insert($request);
            //更新帖子收藏数 +1
            $this->condition = ['id' => $id];
            Db::table($this->model->getTable())->where($this->condition)->increment('favorite_total');

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollBack();
            throw new BusinessException((int)$e->getCode(), '操作失败');
        }
    }

    /**
     * 取消收藏帖子
     * @param RequestInterface $request
     * @return mixed
     */
    public function cancelFavorite(RequestInterface $request)
    {
        try {
            $uid = $request->getAttribute('uid');
            $id = $request->input('id');
            $this->userFavoriteService->data = [
                ['user_id' => $uid],
                ['post_id' => $id],
            ];
            Db::beginTransaction();

            $this->userFavoriteService->delete($request);
            //更新帖子收藏数 -1
            $this->condition = ['id' => $id];
            Db::table($this->model->getTable())->where($this->condition)->decrement('favorite_total');

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollBack();
            throw new BusinessException((int)$e->getCode(), '操作失败');
        }
    }

}