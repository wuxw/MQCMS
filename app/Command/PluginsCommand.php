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
class PluginsCommand extends HyperfCommand
{
    protected $name = 'mq:plugins';

    protected $regRules = [
        'controller/' => 'controller+[*]?',
        'service/' => 'service+[*]?',
        'migration/' => 'migration+[*]?',
    ];

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
        $action = $this->input->getArgument('action'); // 动作
        $name = $this->input->getArgument('name'); // 包名

        if (!in_array(strtolower($action), ['up', 'down', 'create'])) {
            $this->error('wrong action. the action only contains up, down and create');
            return false;
        }
        switch (strtolower($action)) {
            case 'up':
                $this->unzipInstallPlugin($name);
                $this->line('plugin ' . $name . ' installed successfully! ', 'info');
                break;
            case 'down':
                $this->uninstallPlugin($name);
                $this->line('plugin ' . $name . ' uninstalled successfully! ', 'info');
                break;
            case 'create':
                $this->generateCreatePlugin($name);
                $this->line('plugin ' . $name . ' created successfully! ', 'info');
                break;
        }
    }

    /**
     * generate create plugin
     * @param $name
     */
    protected function generateCreatePlugin($name)
    {

    }

    /**
     * uninstall plugin
     * @param $name
     */
    protected function uninstallPlugin($name)
    {

    }

    /**
     * unzip and install plugin
     * @param $name
     */
    protected function unzipInstallPlugin($name)
    {
        // 获取压缩包根据name
        try {
            $componentTempPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '../' . 'upload' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $name;
            $zipFile = new ZipFile();
            $zipFile->openFile($componentTempPath . '.zip');
            if ($zipFile->count() === 0) {
                throw new ZipException('The compressed package is Empty.');
            }

            $classControllerName = $this->qualifyClass($name);
            $classServiceName = $this->qualifyClass($name, 'serviceNamespace');
            $installControllerPath = $this->getPath($classControllerName, '');
            $installServicePath = $this->getPath($classServiceName, '');
            $controllerRes = Common::mkDir($installControllerPath);
            $serviceRes = Common::mkDir($installServicePath);
            if (!$controllerRes || !$serviceRes) {
                throw new ZipException(sprintf('Directory %s creation failed, Please check permissions.', $name));
            }
            foreach ($this->regRules as $key => $value) {
                $entriesMatcher = $zipFile->matcher()->match("/{$value}/si")->getMatches();
                $entriesMatcherKey = array_search($key, $entriesMatcher);
                if ($entriesMatcherKey !== false) {
                    unset($entriesMatcher[$entriesMatcherKey]);
                }
                if ($key === 'controller/') {
                    $zipFile->extractTo($installControllerPath, $entriesMatcher);
                } else if ($key === 'service/') {
                    $zipFile->extractTo($installServicePath, $entriesMatcher);
                }
            }
            $zipFile->close();

        } catch (ZipException $e) {
            $this->line($e->getMessage(), 'error');
        }
    }

    protected function getArguments()
    {
        return [
            ['action', InputArgument::REQUIRED, 'The operations for installing components eg. up or down'],
            ['name', InputArgument::REQUIRED, 'The name of the commponent']
        ];
    }

    protected function getOptions()
    {
        return [
            ['cnamespace', 'CN', InputOption::VALUE_OPTIONAL, 'The controller namespace for class.', $this->getDefaultNamespace('controllerNamespace')],
            ['snamespace', 'SN', InputOption::VALUE_OPTIONAL, 'The service namespace for class.', $this->getDefaultNamespace('serviceNamespace')]
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
        return $this->container->get(ConfigInterface::class)->get($key) ?? [];
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
    protected function getDefaultNamespace($namespace='controllerNamespace'): string
    {
        $appNamespace = $namespace === 'controllerNamespace' ? 'Controller' : 'Service';
        return $this->getConfig()[$namespace] ?? "App\\{$appNamespace}\\Components";
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name, $namespace = 'controllerNamespace')
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        if ($namespace === 'controllerNamespace') {
            $namespace = $this->input->getOption('cnamespace');
        } else {
            $namespace = $this->input->getOption('snamespace');
        }
        if (empty($namespace)) {
            $namespace = $this->getDefaultNamespace('controllerNamespace');
        }
        if (empty($namespace)) {
            $namespace = $this->getDefaultNamespace('serviceNamespace');
        }

        return $namespace . '\\' . $name;
    }

    /**
     * Determine if the class already exists.
     *
     * @param string $rawName
     * @return bool
     */
    protected function alreadyExists($rawName, $namespace = 'controllerNamespace')
    {
        return is_file($this->getPath($this->qualifyClass($rawName, $namespace)));
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
