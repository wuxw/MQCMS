<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Service\BaseService;
use App\Utils\Common;
use Hyperf\HttpServer\Contract\RequestInterface;

class AuthService extends BaseService
{
    public $table = 'admin';

    /**
     * 注册
     * @param RequestInterface $request
     * @return int
     * @throws \Exception
     */
    public function register(RequestInterface $request)
    {
        $account = $request->input('account');
        $phone = $request->input('phone');
        $password = $request->input('password');
        $ip = $request->getHeader('Host')[0];

        $this->select = ['id', 'status', 'avatar'];
        $this->condition = [['account', '=', $account]];
        $adminInfo = parent::show($request);

        if ($adminInfo) {
            if ($adminInfo['status'] == 0) {
                throw new BusinessException(ErrorCode::BAD_REQUEST, '账号已被封禁');
            } else {
                throw new BusinessException(ErrorCode::BAD_REQUEST, '账号已存在，请直接登录');
            }
        }
        // 新建用户
        $salt = Common::generateSalt();
        $data = [
            'account' => $account,
            'password' => Common::generatePasswordHash($password, $salt),
            'phone' => $phone,
            'avatar' => '',
            'status' => 1,
            'salt' => $salt,
            'register_time' => time(),
            'register_ip' => $ip,
            'login_time' => time(),
            'login_ip' => $ip,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        $this->data = $data;
        $lastInsertId = parent::store($request);

        if (!$lastInsertId) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '注册失败');
        }
        return $lastInsertId;
    }

    /**
     * 登录
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function login(RequestInterface $request)
    {
        $account = $request->input('account');
        $password = $request->input('password');

        $this->select = ['id', 'salt', 'avatar', 'password'];
        $this->condition = [['status', '=', 1], ['account', '=', $account]];
        $adminInfo = parent::show($request);

        if (empty($adminInfo)) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '账号不存在或被限制登录');
        }

        if ($adminInfo['password'] != Common::generatePasswordHash($password, $adminInfo['salt'])) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '密码不正确');
        }
        return $adminInfo;
    }
}