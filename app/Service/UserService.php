<?php
declare(strict_types = 1);

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Entity\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class UserService extends BaseService
{
    /**
     * @Inject()
     * @var User
     */
    public $model;

    /**
     * @Inject()
     * @var UserInfoService
     */
    public $userInfoService;

    /**
     * @Inject()
     * @var PostService
     */
    public $postService;

    /**
     * @Inject()
     * @var UserFollowService
     */
    public $userFollowService;

    /**
     * @Inject()
     * @var UserLikeService
     */
    public $userLikeService;

    /**
     * @Inject()
     * @var UserTagService
     */
    public $userTagService;

    /**
     * @Inject()
     * @var UserFavoriteService
     */
    public $userFavoriteService;

    /**
     * @Inject()
     * @var TagService
     */
    public $tagService;


    /**
     * 推荐用户列表
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        try {
            $model = $this->model->getTable();
            $userInfoTable = $this->userInfoService->model->getTable();

            $this->select = [
                $model => ['id', 'created_at', 'updated_at', 'user_name', 'nick_name', 'real_name', 'phone', 'avatar'],
                $userInfoTable => ['intro', 'like_num', 'follow_num', 'fans_num', 'post_num', 'my_like_num'],
            ];
            $this->condition = [
                [$model . '.status', '=', 1]
            ];
            $this->joinTables = [
                $userInfoTable => [
                    $model . '.id', '=', $userInfoTable . '.user_id'
                ]
            ];
            return parent::index($request);

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 用户信息（查看别人）
     * @param RequestInterface $request
     * @return mixed
     */
    public function show(RequestInterface $request)
    {
        try {
            $uid = $request->getAttribute('uid', 0);
            $id = $request->input('id');
            $model = $this->model->getTable();
            $userInfoTable = $this->userInfoService->model->getTable();

            $this->select = [
                $model => ['id', 'user_name', 'nick_name', 'real_name', 'phone', 'avatar'],
                $userInfoTable => ['intro', 'like_num', 'follow_num', 'fans_num', 'post_num', 'my_like_num'],
            ];
            $this->condition = [
                [$model . '.status', '=', 1],
                [$model . '.id', '=', $id],
            ];
            $this->joinTables = [
                $userInfoTable => [$model . '.id', '=', $userInfoTable . '.user_id']
            ];
            $data = parent::show($request);

            $data['is_follow'] = 0;
            if ($uid) {
                // 查询是否关注
                $this->userFollowService->condition = [
                    ['user_id', '=', $uid],
                    ['be_user_id', '=', $id]
                ];
                $exist = $this->userFollowService->multiTableJoinQueryBuilder()->exists();
                if ($exist) {
                    $data['is_follow'] = 1;
                }
            }
            return $data ?? [];

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 用户信息（查看自己）
     * @param RequestInterface $request
     * @return mixed
     */
    public function showSelf(RequestInterface $request)
    {
        try {
            $uid = $request->getAttribute('uid', 0);
            $model = $this->model->getTable();
            $userInfoTable = $this->userInfoService->model->getTable();

            $this->select = [
                $model => ['id', 'user_name', 'real_name', 'phone', 'avatar', 'intro'],
                $userInfoTable => ['like_num', 'follow_num', 'fans_num', 'post_num', 'my_like_num'],
            ];
            $this->condition = [
                [$model . '.status', '=', 1],
                [$model . '.id', '=', $uid],
            ];
            $this->joinTables = [
                $userInfoTable => [$model . '.id', '=', $userInfoTable . '.user_id']
            ];

            $data = parent::show($request);
            return $data ?? [];

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }


    /**
     * 用户帖子列表
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
            $page < 1 && $page = 1;
            $limit > 100 && $limit = 100;

            $this->postService->condition = [
                ['status', '=', 1],
                ['is_publish', '=', 1],
            ];
            $postIds = [];
            switch ($type) {
                //用户发布的帖子列表
                case 1:
                    $this->postService->condition[] = ['user_id', '=', $id];
                    break;

                //用户点赞的帖子列表
                case 2:
                    $this->userLikeService->condition = ['user_id' => $id];
                    $postIds = $this->userLikeService->multiTableJoinQueryBuilder()->pluck('post_id')->toArray();
                    break;

                //用户收藏的帖子列表
                case 3:
                    $this->userFavoriteService->condition = ['user_id' => $id];
                    $postIds = $this->userFavoriteService->multiTableJoinQueryBuilder()->pluck('post_id')->toArray();
                    break;

                //用户发布且含有商品的帖子列表
                case 4:
                    $this->postService->condition[] = ['user_id', '=', $id];
                    $this->postService->condition[] = ['is_good', '=', 1];
                    break;

                default:
                    throw new BusinessException(ErrorCode::BAD_REQUEST, '参数错误');
                    break;
            }
            $query = $this->postService->multiTableJoinQueryBuilder();
            if (in_array($type, [2, 3])) {
                $query = $query->whereIn('id', $postIds);
            }
            $count = $query->count();
            $pagination = $query->paginate((int)$limit, $this->select, 'page', (int)$page)->toArray();

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

    /**
     * 我的关注用户列表
     * @param RequestInterface $request
     * @return mixed
     */
    public function myFollowedUserList(RequestInterface $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $page < 1 && $page = 1;
            $limit > 100 && $limit = 100;
            $uid = $request->getAttribute('uid');

            $this->userFollowService->condition = ['user_id' => $uid];
            $ids = $this->userFollowService->multiTableJoinQueryBuilder()->pluck('be_user_id')->toArray();
            $this->select = ['id', 'user_name', 'nick_name', 'avatar'];
            $this->condition = [
                ['status', '=', 1],
            ];
            $query = $this->multiTableJoinQueryBuilder()->whereIn('id', $ids);
            $count = $query->count();
            $pagination = $query->paginate((int)$limit, $this->select, 'page', (int)$page)->toArray();
            $pagination['total'] = $count;
            return $pagination;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 我的关注标签列表
     * @param RequestInterface $request
     * @return mixed
     */
    public function myFollowedTagList(RequestInterface $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $page < 1 && $page = 1;
            $limit > 100 && $limit = 100;
            $uid = $request->getAttribute('uid');

            $this->userTagService->condition = ['user_id' => $uid];
            $ids = $this->userTagService->multiTableJoinQueryBuilder()->pluck('tag_id')->toArray();
            $this->select = ['id', 'tag_name'];
            $this->condition = [
                ['status', '=', 1],
            ];
            $query = $this->tagService->multiTableJoinQueryBuilder()->whereIn('id', $ids);
            $count = $query->count();
            $pagination = $query->paginate((int)$limit, $this->select, 'page', (int)$page)->toArray();
            $pagination['total'] = $count;
            return $pagination;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

}