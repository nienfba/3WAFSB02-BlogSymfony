<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
         $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $user = new User();
        $user->setFirstName("Fabien")
            ->setLastname('Sellès')
            ->setEmail('fab@alti-com.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setValid(true)
            ->setPassword($this->passwordEncoder->encodePassword($user,'12345678'))
            ->setAvatar('https://picsum.photos/300/200?id='.uniqid());

        $manager->persist($user);
        
        for ($j=0;$j<10;$j++) {
            $category = new Category();
            $category->setTitle($faker->sentence())
                ->setDescription($faker->realText(300))
                ->setCreatedAt(new \DateTime($faker->date('Y-m-d H:i')))
                ->setPicture('https://picsum.photos/300/200?id='.uniqid());

            $manager->persist($category);

            for ($i=0;$i<5;$i++) {
                $article = new Article();
                $article->setTitle($faker->sentence())
                ->setContent($faker->realText(600))
                ->setCreatedAt(new \DateTime($faker->date('Y-m-d H:i')))
                ->setPublishedAt(new \DateTime($faker->date('Y-m-d H:i')))
                ->setPicture('https://picsum.photos/300/200?id='.uniqid())
                ->setCategory($category)
                ->setUser($user)
                ->setValid(true);

                $manager->persist($article);

                for ($k=0;$k<5;$k++) {
                    $comment = new Comment();
                    $comment->setPseudo($faker->userName())
                    ->setComment($faker->realText(300))
                    ->setEmail($faker->email())
                    ->setValid(true)
                    ->setCreatedAt(new \DateTime($faker->date('Y-m-d H:i')))
                    ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }
        $manager->flush();
    }
}
