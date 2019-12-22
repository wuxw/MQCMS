<?php
declare(strict_types=1);

namespace App\Utils;

use Hyperf\HttpServer\Contract\RequestInterface;

class Upload
{
    /**
     * @var string
     */
    public $name = 'file';

    /**
     * @var string
     */
    public $uploadPath = 'upload';

    /**
     * @var string
     */
    public $extension = '';

    /**
     * @var int
     */
    public $width = 100;

    /**
     * @var int
     */
    public $height = 100;

    /**
     * @var bool
     */
    public $resize = true;

    /**
     * Upload constructor.
     * @param string $name
     * @param string $uploadPath
     * @param int $width
     * @param int $height
     * @param bool $resize
     */
    public function __construct($name='file', $uploadPath='upload', $width=100, $height=100, $resize=true)
    {
        $this->name = $name;
        $this->uploadPath = $uploadPath;
        $this->width = $width;
        $this->height = $height;
        $this->resize = $resize;
    }

    /**
     * @param RequestInterface $request
     * @return array|bool
     */
    public function uploadFile(RequestInterface $request)
    {
        if (!$request->hasFile($this->name)) {
            return false;
        }
        $basePath = dirname(__DIR__) . '/../';
        $filePath = $this->uploadPath . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR;
        $res = $this->mkDir($basePath . $filePath);
        if (!$res) {
            return false;
        }
        $fileUrl = $filePath . $this->rename() . '.' . $request->file($this->name)->getExtension();
        $request->file($this->name)->moveTo($basePath . $fileUrl);

        if (!$request->file($this->name)->isMoved()) {
            return false;
        }
        return [
            'fullpath' => $fileUrl,
            'path' => $fileUrl
        ];
    }

    /**
     * @param $path
     * @return bool
     */
    public function mkDir($path)
    {
        return is_dir($path) or ($this->mkDir(dirname($path)) and mkdir($path, 0777));
    }

    /**
     * @return string
     */
    public function rename()
    {
        return uniqid();
    }
}