<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use PhpZip\ZipFile;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class ComponentCommand extends HyperfCommand
{
    protected $name = 'mq:component';
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct($this->name);
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Install the downloaded components');
    }

    public function handle()
    {
        $action = $this->input->getArgument('action');
        $hashId = $this->input->getArgument('hash'); // fI7Dxj6289Bg9X

        if (!in_array(strtolower($action), ['up', 'down'])) {
            $this->error('wrong action. the action only contains up and down');
            return false;
        }
        // 获取压缩包根据hash id
        $zipFile = new ZipFile();
        $extractOnlyFiles = [
            "controller",
            "migration",
            'service'
        ];
        $basePath = dirname(__DIR__) . '/../';
        $componentPath = $basePath . 'upload/components/';
        $zipFile->openFile($componentPath . $hashId . '.zip');
        if (!$this->mkDir($componentPath . $hashId)) {
            $this->error('Directory creation failed, Please check permissions.');
            return false;
        } else {
            $zipFile->extractTo($componentPath . $hashId, $extractOnlyFiles);
        }
        print_r($zipFile->getListFiles());
        $this->line('Hello MQCMS! ', 'info');
    }

    protected function getArguments()
    {
        return [
            ['action', InputArgument::REQUIRED, 'The operations for installing components eg. up or down'],
            ['hash', InputArgument::REQUIRED, 'The name of the commponent hash id']
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
}
