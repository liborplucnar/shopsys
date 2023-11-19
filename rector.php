<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        //__DIR__ . '/project-base/app/src',
        __DIR__ . '/packages/framework/src',
        __DIR__ . '/packages/framework/tests',
        __DIR__ . '/project-base/app/src',
        __DIR__ . '/project-base/app/tests',//*/
        __DIR__ . '/project-base/app/tests/App/Functional/Component/Doctrine/QueryBuilderExtenderTest.php',
    ]);

    $rectorConfig->rules([
        //\Shopsys\FrameworkBundle\Component\Rector\AddVoidReturnTypeWhereNoReturnRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector::class,
        //\Shopsys\FrameworkBundle\Component\Rector\ConstructorParamTypeByPropertyType::class,
        //\Shopsys\FrameworkBundle\Component\Rector\ReturnTypeByAnnotation::class,
        //\Shopsys\FrameworkBundle\Component\Rector\AnnotationArrayToArrayOfMixedRector::class,
        \Shopsys\FrameworkBundle\Component\Rector\GuessReturnTypeByImplementationRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
        //\Shopsys\FrameworkBundle\Component\Rector\ReturnTypeByParent::class, // same as AddReturnTypeDeclarationBasedOnParentClassMethodRector? :(
    ]);
};
