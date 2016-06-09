<?php

namespace Trappar\AliceGeneratorBundle\Tests;

use Symfony\Component\Yaml\Yaml;
use Trappar\AliceGeneratorBundle\FixtureGenerationContext;
use Trappar\AliceGeneratorBundle\FixtureGenerator;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\Post;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\User;
use Trappar\AliceGeneratorBundle\Tests\Test\FixtureGeneratorTestCase;

class FixtureGeneratorTest extends FixtureGeneratorTestCase
{
    public function testServiceIsLoadingCorrectly()
    {
        $this->assertInstanceOf(FixtureGenerator::class, $this->fixtureGenerator);
    }

    public function testMultipleEntities()
    {
        $user = $this->createTestData();
        
        $yaml = $this->fixtureGenerator->generateYaml($user);
        $this->assertYamlGeneratesEqualEntity($user, $yaml);
    }
    
    public function testNoRecursion()
    {
        $user = $this->createTestData();
        
        $yaml = $this->fixtureGenerator->generateYaml($user,
            FixtureGenerationContext::create()->setMaximumRecursion(0)
        );

        $this->assertYamlEquals([
            User::class => [
                'User-1' => [
                    'username' => $user->getUsername(),
                    'created'  => $this->datetimeProvider->fixture($user->getCreated()),
                    'roles'    => [ 'ROLE_ADMIN' ]
                ]
            ]
        ], $yaml);
    }
    
    public function testObjectConstraint()
    {
        $user = $this->createTestData();
        $post = $user->getPosts()->first();
        
        $context = FixtureGenerationContext::create()
            ->addEntityConstraint($post);
        
        $yaml = $this->fixtureGenerator->generateYaml($user, $context);
        $parsed = $this->parseYaml($yaml);
        
        $this->assertArrayNotHasKey('Post-2', $parsed[Post::class]);
    }

    /**
     * @return User
     */
    private function createTestData()
    {
        $user = new User();
        $user->setUsername('testUser');
        $user->setRoles(['ROLE_ADMIN']);

        $post1 = new Post();
        $post1->setTitle('How To Do Something')
            ->setBody('Just do it!')
            ->setPostedBy($user);

        $post2 = new Post();
        $post2->setTitle('Web Development Made Easy')
            ->setBody('Just do it!')
            ->setPostedBy($user);

        $user->getPosts()->add($post1);
        $user->getPosts()->add($post2);

        return $user;
    }

}