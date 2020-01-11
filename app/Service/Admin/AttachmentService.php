<?php
declare(strict_types=1);

namespace App\Service\Admin;

use App\Model\Attachment;
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
    public $table;

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
        return parent::index($request);
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
        $this->data = [
            'user_id' => $request->getAttribute('uid'),
            'attach_origin_name' => $upload->fileInfo['name'],
            'attach_name' => $pathInfo['name'],
            'attach_url' => $pathInfo['path'],
            'attach_type' => $attachType,
            'attach_minetype' => $upload->mineType,
            'attach_extension' => $upload->extension,
            'attach_size' => $upload->fileInfo['size'],
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time()
        ];
        parent::store($request);
        return $pathInfo;
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