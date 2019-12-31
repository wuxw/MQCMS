<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\Common;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Utils\CodeGen\Project;
use Hyperf\Utils\Str;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $namespace = $this->input->getOption('namespace');

        if (!in_array(strtolower($action), ['up', 'down'])) {
            $this->error('wrong action. the action only contains up and down');
            return false;
        }
        $this->unzip($hashId);
        $name = $this->qualifyClass($hashId);
        $path = $this->getPath($name, '');
        Common::mkDir($path);
        $this->line('component ' . $hashId . ' installed successfully! ', 'info');
    }

    protected function unzip($hashId)
    {
        // 获取压缩包根据hash id
        try {
            $zipFile = new ZipFile();
            $basePath = dirname(__DIR__) . '/../';
            $componentPath = $basePath . 'upload/components/' . $hashId;
            $zipFile->openFile($componentPath . '.zip');
            if ($zipFile->count() === 0) {
                throw new ZipException('The compressed package is Empty.');
            }
            Common::delDirFile($componentPath);
            $res = Common::mkDir($componentPath);
            if (!$res) {
                throw new ZipException('Directory creation failed, Please check permissions.');
            }

            $zipFile->extractTo($componentPath);
            $zipFile->close();

        } catch (ZipException $e) {
            $this->line($e->getMessage(), 'error');
        }
    }

    protected function getArguments()
    {
        return [
            ['action', InputArgument::REQUIRED, 'The operations for installing components eg. up or down'],
            ['hash', InputArgument::REQUIRED, 'The name of the commponent hash id']
        ];
    }

    protected function getOptions()
    {
        return [
            ['namespace', 'N', InputOption::VALUE_OPTIONAL, 'The namespace for class.', $this->getDefaultNamespace()]
        ];
    }

    /**
     * Get the custom config for generator.
     */
    protected function getConfig(): array
    {
        $class = Arr::last(explode('\\', static::class));
        $class = Str::replaceLast('Command', '', $class);
        $key = 'devtool.mqcms.' . Str::snake($class, '.');
        return $this->getContainer()->get(ConfigInterface::class)->get($key) ?? [];
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

    /**
     * @return string
     */
    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Controller\\Components';
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $namespace = $this->input->getOption('namespace');
        if (empty($namespace)) {
            $namespace = $this->getDefaultNamespace();
        }

        return $namespace . '\\' . $name;
    }

    /**
     * Determine if the class already exists.
     *
     * @param string $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return is_file($this->getPath($this->qualifyClass($rawName)));
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name, $extension = '.php')
    {
        $project = new Project();
        return BASE_PATH . '/' . $project->path($name, $extension);
    }
}
