<?php

namespace ContainerZxDrT0I;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getDatosAcademicosTypeService extends App_KernelDevDebugContainer
{
    /**
     * Gets the private 'App\Form\DatosAcademicosType' shared autowired service.
     *
     * @return \App\Form\DatosAcademicosType
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/symfony/form/FormTypeInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/symfony/form/AbstractType.php';
        include_once \dirname(__DIR__, 4).'/src/Form/DatosAcademicosType.php';

        return $container->privates['App\\Form\\DatosAcademicosType'] = new \App\Form\DatosAcademicosType();
    }
}