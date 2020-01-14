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
    public $model;

    /**
     * @Inject()
     * @var UserInfoService
     */
    public $userInfoService;

    /**
     * @Inject()
     * @var UserAuthService
     */
    public $userAuthService;

    /**
     * 注册
     * @param RequestInterface $request
     * @return int
     * @throws \Exception
     */
    public function register(RequestInterface $request)
    {
        $userName = trim($request->input('user_name'));
        $password = trim($request->input('password'));
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
            'nick_name' => $userName . generate_random_string(6),
            'phone' => '',
            'avatar' => '',
            'password' => Common::generatePasswordHash($password, $salt),
            'salt' => $salt,
            'status' => 1,
            'register_time' => time(),
            'register_ip' => $ip,
            'login_time' => time(),
            'login_ip' => $ip,
        ];
        Db::beginTransaction();
        try {
            $lastInsertId = parent::store($request);
            $this->userInfoService->data = [
                'user_id' => $lastInsertId,
            ];
            $this->userInfoService->store($request);
            Db::commit();
            return [$lastInsertId, $uuid];

        } catch (\Exception $e) {
            Db::rollBack();
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 登录
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function login(RequestInterface $request)
    {
        $userName = trim($request->input('user_name'));
        $password = trim($request->input('password'));

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
        $nickName = $request->has('nick_name', '');
        $avatarUrl = $request->has('avatar_url', '');
        $gender = $request->has('gender', 1);
        $country = $request->has('country', '');
        $province = $request->has('province', '');
        $city = $request->has('city', '');
        $version = $request->has('version', '1.0.0');
        $ip = $request->getServerParams()['remote_addr'];

        $url = 'https://api.weixin.qq.com/sns/jscode2session?';
        $url .= 'appid=' . env('MINI_APP_ID') . '&secret=' . env('MINI_APP_SECRET') . '&js_code=' . $code . '&grant_type=authorization_code';

        $client = make(HttpClient::class, [
            'option' => []
        ]);
        $response = $client->getClient()->get($url);
        $body = json_decode($response->getBody(), true);
        if ($body['errcode'] !== 0) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '微信授权失败 code: ' . $body['errocode'] . ' msg: ' . $body['errmsg']);
        }

        $this->userAuthService->condition = ['oauth_id' => $body['openid']];
        $userAuthInfo = $this->userAuthService->show($request);

        try {
            Db::beginTransaction();
            if ($userAuthInfo) {
                $this->condition = ['user_id' => $userAuthInfo['user_id']];
                $userInfo = parent::show($request);
                if ($userInfo) {
                    // 更新用户
                    $this->data = [
                        'user_name' => $nickName . generate_random_string(6),
                        'nick_name' => $nickName,
                        'avatar' => $avatarUrl,
                        'login_time' => time(),
                        'login_ip' => $ip,
                    ];
                    $this->condition = ['id' => $userAuthInfo['user_id']];
                    $res = parent::update($request);
                    if (!$res) {
                        throw new BusinessException(ErrorCode::BAD_REQUEST, '用户登录失败 code: 10001');
                    }
                } else {
                    // 新建用户
                    $uuid = Common::generateSnowId();
                    $this->data = [
                        'uuid' => $uuid,
                        'user_name' => $nickName,
                        'real_name' => '',
                        'nick_name' => $nickName . generate_random_string(6),
                        'phone' => '',
                        'avatar' => '',
                        'password' => '',
                        'salt' => '',
                        'status' => 1,
                        'register_time' => time(),
                        'register_ip' => $ip,
                        'login_time' => time(),
                        'login_ip' => $ip,
                    ];
                    $lastInsertId = parent::store($request);
                    if (!$lastInsertId) {
                        throw new BusinessException(ErrorCode::BAD_REQUEST, '用户登录失败 code: 10002');
                    }

                    $this->userInfoService->data = [
                        'user_id' => $lastInsertId,
                    ];
                    $res = $this->userInfoService->store($request);
                    if (!$res) {
                        throw new BusinessException(ErrorCode::BAD_REQUEST, '用户登录失败 code: 10003');
                    }

                }
            } else {
                $this->condition = ['user_id' => $userAuthInfo['user_id']];
                $userInfo = parent::show($request);

                if ($userInfo) {
                    // 插入user_auth表
                    $this->userAuthService->data = [
                        'user_id' => $userInfo['user_d'],
                    ];

                    $this->userAuthService->store($request);
                }
            }

            Db::commit();
            return $userAuthInfo;

        } catch (\Exception $e) {
            Db::rollBack();
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }
}