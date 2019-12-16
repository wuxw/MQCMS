<?php
declare(strict_types = 1);

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;
use App\Utils\Common;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class UserService extends BaseService
{
    /**
     * @Inject()
     * @var User
     */
    public $table;

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
     * @var UserFavoriteService
     */
    public $userFavoriteService;

    /**
     * 注册
     * @param RequestInterface $request
     * @return int
     * @throws \Exception
     */
    public function register(RequestInterface $request)
    {
        $userName = $request->input('user_name');
        $password = $request->input('password');
        $ip = $request->getServerParams()['remote_addr'];

        $this->select = ['id', 'status', 'avatar'];
        $this->condition = ['user_name' => $userName];
        $userInfo = parent::show($request);

        if ($userInfo) {
            if ($userInfo['status'] == 0) {
                throw new BusinessException(ErrorCode::BAD_REQUEST, '账号已被封禁');
            } else {
                throw new BusinessException(ErrorCode::BAD_REQUEST, '账号已存在，请直接登录');
            }
        }
        $salt = Common::generateSalt();
        $this->data = [
            'user_no' => Common::generateSnowId(),
            'user_name' => $userName,
            'real_name' => '',
            'nick_name' => $userName . generateRandomString(6),
            'phone' => '',
            'avatar' => '',
            'password' => Common::generatePasswordHash($password, $salt),
            'salt' => $salt,
            'status' => 1,
            'register_time' => time(),
            'register_ip' => $ip,
            'login_time' => time(),
            'login_ip' => $ip,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        Db::beginTransaction();
        try {
            $lastInsertId = parent::store($request);
            $this->userInfoService->data = [
                'user_id' => $lastInsertId,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            $this->userInfoService->store($request);
            Db::commit();
            return $lastInsertId;

        } catch (\Exception $e) {
            Db::rollBack();
            throw new BusinessException((int)$e->getCode(), '注册失败');
        }
    }

    /**
     * 登录
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function login(RequestInterface $request)
    {
        $userName = $request->input('user_name');
        $password = $request->input('password');

        $this->select = ['id', 'salt', 'password'];
        $this->condition = ['status' => 1, 'user_name' => $userName];
        $userInfo = parent::show($request);

        if (!$userInfo) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '用户不存在');
        }

        if ($userInfo['password'] != Common::generatePasswordHash($password, $userInfo['salt'])) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '密码不正确');
        }
        $ip = $request->getServerParams()['remote_addr'];
        $this->data = [
            'login_ip' => $ip,
            'login_time' => time()
        ];
        $this->condition = ['id' => $userInfo['id']];
        if (!parent::update($request)) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '登录失败');
        }
        return $userInfo;
    }

    /**
     * 推荐用户列表
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        try {
            $this->select = [
                $this->table . '.id',
                'user_name',
                'nick_name',
                'real_name',
                'phone',
                'avatar',
                'intro',
                'like_num',
                'follow_num',
                'fans_num',
                'post_num',
                'my_like_num',
                $this->table . '.created_at',
                $this->table . '.updated_at',
            ];
            $this->condition = [
                [$this->table . '.status', '=', 1]
            ];
            $this->joinTables = [
                $this->userInfoService->table => [$this->table . '.id', '=', $this->userInfoService->table . '.user_id']
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

            $this->select = [
                $this->table . '.id',
                'user_name',
                'nick_name',
                'real_name',
                'phone',
                'avatar',
                'intro',
                'like_num',
                'follow_num',
                'fans_num',
                'post_num',
                'my_like_num'
            ];
            $this->condition = [
                [$this->table . '.status', '=', 1],
                [$this->table . '.id', '=', $id],
            ];
            $this->joinTables = [
                $this->userInfoService->table => [$this->table . '.id', '=', $this->userInfoService->table . '.user_id']
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

            $this->select = [
                $this->table . '.id',
                'user_name',
                'nick_name',
                'real_name',
                'phone',
                'avatar',
                'intro',
                'like_num',
                'follow_num',
                'fans_num',
                'post_num',
                'my_like_num'
            ];
            $this->condition = [
                [$this->table . '.status', '=', 1],
                [$this->table . '.id', '=', $uid],
            ];
            $this->joinTables = [
                $this->userInfoService->table => [$this->table . '.id', '=', $this->userInfoService->table . '.user_id']
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
            $page = $page < 1 ? 1 : $page;
            $limit = $limit > 100 ? 100 : $limit;

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

}