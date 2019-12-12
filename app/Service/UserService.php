<?php
declare(strict_types=1);

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Utils\Common;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;

class UserService extends BaseService
{
    protected $table = 'user';

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
            $select = [$this->table.'.id', 'user_name', 'nick_name', 'real_name', 'phone', 'avatar', 'intro', 'like_num', 'follow_num', 'fans_num', 'post_num', 'my_like_num'];
            $query = Db::table($this->table)->where('status', 1)->Join('user_info', 'user.id', '=', 'user_info.user_id')->select($select);
            $count = $query->count();
            $pagination = $query->paginate((int)$limit, $select, 'page', (int)$page)->toArray();
            $pagination['total'] = $count;
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
            //todo!!!!
            $uid = $request->getAttribute('uid');
            var_dump($uid);
            $id = $request->input('id');
            $select = [$this->table.'.id', 'user_name', 'nick_name', 'real_name', 'phone', 'avatar', 'intro', 'like_num', 'follow_num', 'fans_num', 'post_num', 'my_like_num'];
            $this->condition = [
                ['status', '=', 1],
                [$this->table.'.id', '=', $id],
            ];
            $data = Db::table($this->table)->where('status', 1)->Join('user_info', 'user.id', '=', 'user_info.user_id')->select($select)->first();
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