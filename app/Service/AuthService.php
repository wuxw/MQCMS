<?php
declare(strict_types=1);

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;
use App\Pool\HttpClient;
use App\Utils\Common;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class AuthService extends BaseService
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
     * @var HttpClient
     */
    public $httpClient;

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
        $uuid = Common::generateSnowId();
        $this->data = [
            'uuid' => $uuid,
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
            return [$lastInsertId, $uuid];

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

        $this->select = ['id', 'uuid', 'salt', 'password'];
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

    public function miniProgram(RequestInterface $request)
    {
        $code = $request->input('code');

        return $this->httpClient->getClient()->get('http://www.baidu.com')->getStatusCode();
    }
}