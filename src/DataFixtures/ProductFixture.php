<?php

namespace App\DataFixtures;

use App\Entity\Product;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $date = new DateTime("now");
        $product = new Product();
        $product->setName('Priceless widget');
        $product->setPrice(14.50);
        $product->setDescription('Ok, I guess it *does* have a price');
        $product->setDate($date);
        $manager->persist($product);
        $manager->flush();

        // create 20 products! Bam!
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setPrice(mt_rand(10, 100));
            $product->setDescription('Ok, I guess it *does* have a price');
            $product->setDate($date);
            $manager->persist($product);
            $manager->flush();
        }


    }
}
