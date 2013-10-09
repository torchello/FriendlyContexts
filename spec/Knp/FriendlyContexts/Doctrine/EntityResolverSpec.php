<?php

namespace spec\Knp\FriendlyContexts\Doctrine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Knp\FriendlyContexts\Reflection\ObjectReflector;

class EntityResolverSpec extends ObjectBehavior
{
    /**
     * @param Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param Doctrine\Common\Persistence\ObjectManager $em
     * @param Doctrine\Common\Persistence\Mapping\ClassMetadataFactory $factory
     * @param Knp\FriendlyContexts\Reflection\ObjectReflector $reflector
     * @param ReflectionClass $class1
     * @param ReflectionClass $class2
     * @param ReflectionClass $class3
     * @param ReflectionClass $class4
     **/
    function let(KernelInterface $kernel, ContainerInterface $container, ObjectManager $em, ClassMetadataFactory $factory, ObjectReflector $reflector, \ReflectionClass $class1, \ReflectionClass $class2, \ReflectionClass $class3, \ReflectionClass $class4)
    {
        $class1->getShortName()->willReturn('User');
        $class1->getNamespaceName()->willReturn('N1/Namespace');
        $class2->getShortName()->willReturn('User');
        $class2->getNamespaceName()->willReturn('N2/Namespace');
        $class3->getShortName()->willReturn('Users');
        $class3->getNamespaceName()->willReturn('N1/Namespace');
        $class4->getShortName()->willReturn('Company');
        $class4->getNamespaceName()->willReturn('N1/Namespace');

        $em->getMetadataFactory()->willReturn($factory);
        $factory->getAllMetadata()->willReturn([]);
        $reflector->getReflectionsFromMetadata(Argument::any())->willReturn([$class1, $class2, $class3, $class4]);
        $container->has('friendly.context.object.reflector')->willReturn(true);
        $container->get('friendly.context.object.reflector')->willReturn($reflector);

        $kernel->getContainer()->willReturn($container);
        $this->setKernel($kernel);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Knp\FriendlyContexts\Doctrine\EntityResolver');
    }

    function it_should_provide_one_value_for_a_singular_name()
    {
        $this->entityNameProposal('the name')->shouldReturn(['thename', 'thenames']);
    }

    function it_should_provide_two_values_for_a_plural_name()
    {
        $this->entityNameProposal('the names')->shouldReturn(['thename', 'thenames']);
    }

    function it_should_provide_three_values_for_a_ies_name()
    {
        $this->entityNameProposal('the categories')->shouldReturn(['thecategory', 'thecategories']);
    }

    function it_should_return_multiple_class(ObjectManager $em, \ReflectionClass $class1, \ReflectionClass $class2, \ReflectionClass $class3, \ReflectionClass $class4)
    {
        $this->resolve($em, 'user', '')->shouldReturn([$class1, $class2, $class3]);
        $this->resolve($em, 'companies', '')->shouldReturn([$class4]);
    }

    function it_should_return_class_when_namespace_is_specified(ObjectManager $em, \ReflectionClass $class1, \ReflectionClass $class2, \ReflectionClass $class3)
    {
        $this->resolve($em, 'user', 'N1')->shouldreturn([$class1, $class3]);
        $this->resolve($em, 'user', 'N2')->shouldreturn([$class2]);
    }
}
