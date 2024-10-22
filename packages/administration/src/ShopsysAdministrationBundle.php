<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle;

use Shopsys\AdministrationBundle\DependencyInjection\Compiler\InicializeControllersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysAdministrationBundle extends Bundle
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new InicializeControllersCompilerPass());
    }
}
