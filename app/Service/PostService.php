<?php
declare(strict_types = 1);

namespace App\Service;

use App\Utils\Common;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;

class PostService extends BaseService
{
    public $table = 'post';

    /**
     * 帖子列表分页
     * @param RequestInterface $request
     * @return mixed
     */
    public function index(RequestInterface $request)
    {
        // 类型： recommend: 推荐 default: 默认
        $type = $request->input('type', 'default');

        $this->condition = [
            ['status', '=', 1],
            ['is_publish', '=', 1],
        ];
        if ($type === 'recommend') {
            $this->condition[] = ['is_recommend', '=', 1];
        }
        $this->orderBy = 'id DESC';
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
        $isPublish = $request->input('is_publish', 1);
        $data = [
            'user_id' => $uid,
            'post_content' => $request->input('post_content'),
            'link_url' => $request->input('link_url', ''),
            'label_type' => $request->input('label_type', 1),
            'is_good' => $request->input('link_url', '') ? 1 : 0,
            'relation_tags' => $request->input('relation_tags', ''),
            'address' => $request->input('address', ''),
            'addr_lat' => $request->input('addr_lat', ''),
            'addr_lng' => $request->input('addr_lng', ''),
            'attach_urls' => $request->input('attach_urls', ''),
            'attach_ids' => $request->input('attach_ids', ''),
            'is_publish' => $isPublish,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        $this->data = $data;
        Db::beginTransaction();
        try {
            $lastInsertId = parent::store($request);
            foreach ($relationTagIds as $value) {
                $relationData[] = [
                    'user_id' => $uid,
                    'tag_id' => $value,
                    'post_id' => $lastInsertId,
                    'created_at' => time(),
                    'updated_at' => time()
                ];
            }
            Db::table('tag_post_relation')->insert($relationData);
            Db::commit();
            return $lastInsertId;

        } catch (\Exception $e) {
            Db::rollBack();
            $message = $isPublish ? '发布失败' : '保存失败';
            throw new BusinessException((int)$e->getCode(), $message);
        }
        return parent::store($request);
    }

}