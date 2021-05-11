<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $newCustomer = new User();
        $newCustomer->setUsername('customer#1');
        $newCustomer->setPassword($this->passwordEncoder->encodePassword($newCustomer,'123'));
        $newCustomer->setEmail('generic@bloopmail.com');
        $newCustomer->setRoles(['ROLE_USER', 'ROLE_CUSTOMER']);
        $manager->persist($newCustomer);

        $newFirstLineAgent = new User();
        $newFirstLineAgent->setUsername('nr1agent');
        $newFirstLineAgent->setPassword($this->passwordEncoder->encodePassword($newFirstLineAgent,'123'));
        $newFirstLineAgent->setEmail('foo@bloopmail.com');
        $newFirstLineAgent->setRoles(['ROLE_USER', 'ROLE_AGENT_1']);
        $manager->persist($newFirstLineAgent);

        $newSecondLineAgent = new User();
        $newSecondLineAgent->setUsername('masterOfPuppets');
        $newSecondLineAgent->setPassword($this->passwordEncoder->encodePassword($newSecondLineAgent, '123'));
        $newSecondLineAgent->setEmail('bar@bloopmail.com');
        $newSecondLineAgent->setRoles(['ROLE_USER', 'ROLE_AGENT_2']);
        $manager->persist($newSecondLineAgent);

        $newManager = new User();
        $newManager->setUsername('BigBoss');
        $newManager->setPassword($this->passwordEncoder->encodePassword($newManager,'MetalGearSolid6'));
        $newManager->setEmail('Snake@bloopmail.com');
        $newManager->setRoles(['ROLE_USER', 'ROLE_MANAGER']);
        $manager->persist($newManager);


        $manager->flush();

        // to add fixture data to database, run:
        // php bin/console doctrine:fixtures:load
    }
}
