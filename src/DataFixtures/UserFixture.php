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
        $newCustomer->setFirstName('Bob');
        $newCustomer->setLastName('Johnson');
        $newCustomer->setEmail('generic@bloopmail.com');
        $newCustomer->setRoles(['ROLE_USER','ROLE_CUSTOMER']);
        $manager->persist($newCustomer);

        $newFirstLineAgent = new User();
        $newFirstLineAgent->setUsername('nr1agent');
        $newFirstLineAgent->setPassword($this->passwordEncoder->encodePassword($newFirstLineAgent,'123'));
        $newFirstLineAgent->setFirstName('Slab');
        $newFirstLineAgent->setLastName('Bulkhead');
        $newFirstLineAgent->setEmail('foo@bloopmail.com');
        $newFirstLineAgent->setRoles(['ROLE_USER','ROLE_AGENT_1']);
        $manager->persist($newFirstLineAgent);

        $newSecondLineAgent = new User();
        $newSecondLineAgent->setUsername('masterOfPuppets');
        $newSecondLineAgent->setPassword($this->passwordEncoder->encodePassword($newSecondLineAgent, '123'));
        $newSecondLineAgent->setFirstName('Big');
        $newSecondLineAgent->setLastName('McLargeHuge');
        $newSecondLineAgent->setEmail('bar@bloopmail.com');
        $newSecondLineAgent->setRoles(['ROLE_USER','ROLE_AGENT_2']);
        $manager->persist($newSecondLineAgent);

        $newManager = new User();
        $newManager->setUsername('BigBoss');
        $newManager->setPassword($this->passwordEncoder->encodePassword($newManager,'MetalGearSolid6'));
        $newManager->setFirstName('Roll');
        $newManager->setLastName('Fizzlebeef');
        $newManager->setEmail('Snake@bloopmail.com');
        $newManager->setRoles(['ROLE_USER','ROLE_MANAGER']);
        $manager->persist($newManager);

        // TODO: delete this user at the end. this user has all roles for testing purposes.
        $newAdmin = new User();
        $newAdmin->setUsername('admin');
        $newAdmin->setPassword($this->passwordEncoder->encodePassword($newAdmin,'MasterAdmin451'));
        $newAdmin->setFirstName('Punch');
        $newAdmin->setLastName('Slamchest');
        $newAdmin->setEmail('Snake@bloopmail.com');
        $newAdmin->setRoles(['ROLE_USER','ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_AGENT_1', 'ROLE_AGENT_2', 'ROLE_CUSTOMER']);
        $manager->persist($newAdmin);


        $manager->flush();

        // to add fixture data to database, run:
        // php bin/console doctrine:fixtures:load
    }
}
