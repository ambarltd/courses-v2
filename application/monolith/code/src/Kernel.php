<?php

declare(strict_types=1);

namespace Galeas\Api;

use Doctrine\ODM\MongoDB\Types\Type;
use Galeas\Api\Service\ODM\OverrideDateType;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function getCacheDir(): string
    {
        return '/symfony_tmp/cache/'.$this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return '/symfony_tmp/logs';
    }

    /**
     * @return \Generator|iterable|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     *
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }

        Type::overrideType('date', OverrideDateType::class);
    }

    /**
     * @throws \Exception
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);

        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/config'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/config_'.$this->environment.self::CONFIG_EXTS, 'glob');

        $loader->load($confDir.'/services'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/services_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    /**
     * @throws \Symfony\Component\Config\Exception\LoaderLoadException
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/routes'.self::CONFIG_EXTS);
        $routes->import($confDir.'/routes_'.$this->environment.self::CONFIG_EXTS);
    }
}
