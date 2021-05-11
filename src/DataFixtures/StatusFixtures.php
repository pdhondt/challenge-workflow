<?php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StatusFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $newStatus = new Status();
        $newStatus->setDescriptor("open");
        $manager->persist($newStatus);

        $newStatus = new Status();
        $newStatus->setDescriptor("closed");
        $manager->persist($newStatus);

        $newStatus = new Status();
        $newStatus->setDescriptor("waiting for customer feedback");
        $manager->persist($newStatus);

        $newStatus = new Status();
        $newStatus->setDescriptor("won't fix");
        $manager->persist($newStatus);

        $newStatus = new Status();
        $newStatus->setDescriptor("fixed");
        $manager->persist($newStatus);

        $newStatus = new Status();
        $newStatus->setDescriptor("in progress");
        $manager->persist($newStatus);


        $manager->flush();
    }
}
