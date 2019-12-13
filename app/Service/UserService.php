<?php
declare(strict_types=1);

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Utils\Common;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class UserService extends BaseService
{
    public $table = 'user';

    /**
     * @Inject()
     * @var UserInfoService
     */
    public $userInfoService;

    /**
     * 推荐用户列表
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $page = $page < 1 ? 1 : $page;
            $limit = $limit > 100 ? 100 : $limit;

            $this->select = [
                $this->table.'.id',
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
                [$this->table.'.status', '=', 1]
            ];
            $this->joinTables = [
                $this->userInfoService->table => [$this->table . '.id', '=', $this->userInfoService->table . '.user_id']
            ];
            $query = $this->multiTableJoinQueryBuilder();

            $count = $query->count();
            $pagination = $query->paginate((int)$limit, $this->select, 'page', (int)$page)->toArray();
            $pagination['total'] = $count;
            foreach ($pagination['data'] as $key => &$value) {
                $value['created_at'] = $value['created_at'] ? date('Y-m-d H:i:s', $value['created_at']) : '';
                $value['updated_at'] = $value['updated_at'] ? date('Y-m-d H:i:s', $value['updated_at']) : '';
            }
            return $pagination;

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
                $this->table.'.id',
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
                [$this->table.'.status', '=', 1],
                [$this->table.'.id', '=', $id],
            ];
            $this->joinTables = [
                $this->userInfoService->table => [$this->table . '.id', '=', $this->userInfoService->table . '.user_id']
            ];
            $this->orderBy = [
                $this->table => ["id" => "DESC"],
                $this->userInfoService->table => ["id" => "ASC"],
            ];

            $query = $this->multiTableJoinQueryBuilder();
            $data = $query->first();
            return $data ?? [];

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

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
        $ip = $request->getHeader('Host')[0];

        $this->select = ['id', 'status', 'avatar'];
        $this->condition = [['user_name', '=', $userName]];
        $userInfo = parent::show($request);

        if ($userInfo) {
            if ($userInfo['status'] == 0) {
                throw new BusinessException(ErrorCode::BAD_REQUEST, '账号已被封禁');
            } else {
                throw new BusinessException(ErrorCode::BAD_REQUEST, '账号已存在，请直接登录');
            }
        }
        $salt = Common::generateSalt();
        $data = [
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
        try{
            $lastInsertId = Db::table('user')->insertGetId($data);
            $userInfo = [
                'user_id' => $lastInsertId,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            Db::table('user_info')->insert($userInfo);
            Db::commit();
            return $lastInsertId;
        } catch(\Throwable $ex){
            Db::rollBack();
            throw new BusinessException(ErrorCode::BAD_REQUEST, '注册失败');
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
        $this->condition = [['status', '=', 1], ['user_name', '=', $userName]];
        $userInfo = parent::show($request);

        if (!$userInfo) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '用户不存在');
        }

        if ($userInfo['password'] != Common::generatePasswordHash($password, $userInfo['salt'])) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '密码不正确');
        }
        return $userInfo;
    }
}