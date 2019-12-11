<?php
declare(strict_types=1);

namespace App\Service;

use App\Utils\Common;
use Hyperf\HttpServer\Contract\RequestInterface;

class PostService extends BaseService
{
    protected $table = 'post';

    public function index(RequestInterface $request)
    {
        $type = $request->input('type', 'default'); // 类型： recommend: 推荐 default: 默认

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
}