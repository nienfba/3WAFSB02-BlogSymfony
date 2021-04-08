<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        
        for ($j=0;$j<10;$j++) {
            $category = new Category();
            $category->setTitle($faker->sentence())
                ->setDescription($faker->realText(300))
                ->setPicture('https://picsum.photos/300/200?id='.uniqid());

            $manager->persist($category);

            for ($i=0;$i<10;$i++) {
                $article = new Article();
                $article->setTitle($faker->sentence())
                ->setContent($faker->realText(600))
                ->setCreatedAt(new \DateTime($faker->date('Y-m-d H:i')))
                ->setPublishedAt(new \DateTime($faker->date('Y-m-d H:i')))
                ->setPicture('https://picsum.photos/300/200?id='.uniqid())
                ->setCategory($category)
                ->setValid(true);

                $manager->persist($article);
            }
        }
        $manager->flush();
    }
}
