<?php

namespace Omidrezasalari\CircuitBreakerBundle\DependencyInjection;

use Omidrezasalari\CircuitBreakerBundle\Service\CircuitBreaker;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class CircuitBreakerExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $storageService = $config['storage_service'] ?? 'Omidrezasalari\CircuitBreakerBundle\Service\ApcuStorage';

        if ($container->hasDefinition(CircuitBreaker::class)) {
            $definition = $container->getDefinition(CircuitBreaker::class);
            $definition->setArgument('$storage', new Reference($storageService));
        }
    }
}
