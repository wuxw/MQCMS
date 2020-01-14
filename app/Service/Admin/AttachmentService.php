<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Entity\Attachment;
use App\Service\BaseService;
use App\Utils\Upload;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class AttachmentService extends BaseService
{
    /**
     * @Inject()
     * @var Attachment
     */
    public $model;

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        // 搜索
        if ($request->has('search')) {
            $searchForm = $request->input('search');
            $this->multiSingleTableSearchCondition($searchForm);
        }
        $attachList = parent::index($request);
        foreach ($attachList['data'] as $key => &$value) {
            $value['attach_full_url'] = env('APP_UPLOAD_HOST_URL', '') . $value['attach_url'];
        }
        return $attachList;
    }

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function show(RequestInterface $request)
    {
        $id = $request->input('id');
        $this->condition = ['id' => $id];
        return parent::show($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function store(RequestInterface $request)
    {
        $upload = new Upload();
        $pathInfo = $upload->uploadFile($request);

        if (in_array($upload->extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
            $attachType = 1;

        } else if (in_array($upload->extension, ['mp4', 'avi'])) {
            $attachType = 2;

        } else {
            $attachType = 3;
        }
        $data = [
            'user_id' => $request->getAttribute('uid'),
            'attach_origin_name' => $upload->fileInfo['name'],
            'attach_name' => $pathInfo['name'],
            'attach_url' => $pathInfo['path'],
            'attach_type' => $attachType,
            'attach_minetype' => $upload->mineType,
            'attach_extension' => $upload->extension,
            'attach_size' => $upload->fileInfo['size'],
            'status' => 1,
        ];
        $this->data = $data;
        $res = parent::store($request);
        if (!$res) {
            throw new BusinessException(ErrorCode::BAD_REQUEST, '上传失败');
        }
        $data['attach_full_url'] = env('APP_UPLOAD_HOST_URL', '') . $data['attach_url'];
        return $data;
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function update(RequestInterface $request)
    {
        $id = $request->input('id');
        $this->data = [
            'attach_name' => $request->input('attach_name'),
            'attach_url' => $request->input('attach_url'),
            'status' => $request->input('status', 1),
            'updated_at' => time(),
        ];
        $this->condition = ['id' => $id];
        return parent::update($request);
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function delete(RequestInterface $request)
    {
        $this->condition = ['id' => $request->input('id')];
        return parent::delete($request);
    }
}