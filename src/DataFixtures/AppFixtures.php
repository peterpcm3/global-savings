<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Voucher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 1000; $i++) {
            $voucherUsed = $faker->boolean(20);

            $voucher = new Voucher();
            $voucher->setAmount($faker->numberBetween(10, 100));
            $voucher->setIsUsed($voucherUsed);
            $voucher->setExpireDate($faker->dateTimeBetween('-1 month', '1 month'));
            $voucher->setCreatedAt($faker->dateTime);

            $order = null;
            if ($voucherUsed) {
                $originalAmount = $faker->numberBetween(200, 999);

                $order = new Order();
                $order->setOriginalAmount($originalAmount);
                $order->setAmount($originalAmount - $voucher->getAmount());
                $order->setVoucher($voucher);
            }

            $manager->persist($voucher);

            if (null !== $order) {
                $manager->persist($order);
            }
        }

        $manager->flush();
    }
}
