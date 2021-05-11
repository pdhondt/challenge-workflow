<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $newCustomer = new User();
        $newCustomer->setUsername('customer#1');
        $newCustomer->setPassword('123');
        $newCustomer->setRoles(['ROLE_USER', 'ROLE_CUSTOMER']);
        $manager->persist($newCustomer);

        $newFirstLineAgent = new User();
        $newFirstLineAgent->setUsername('nr1agent');
        $newFirstLineAgent->setPassword('123');
        $newFirstLineAgent->setRoles(['ROLE_USER', 'ROLE_AGENT_1']);
        $manager->persist($newFirstLineAgent);

        $newSecondLineAgent = new User();
        $newSecondLineAgent->setUsername('masterOfPuppets');
        $newSecondLineAgent->setPassword('123');
        $newSecondLineAgent->setRoles(['ROLE_USER', 'ROLE_AGENT_2']);
        $manager->persist($newSecondLineAgent);

        $newManager = new User();
        $newManager->setUsername('BigBoss');
        $newManager->setPassword('MetalGearSolid6');
        $newManager->setRoles(['ROLE_USER', 'ROLE_MANAGER']);
        $manager->persist($newManager);



        $manager->flush();
    }
}
