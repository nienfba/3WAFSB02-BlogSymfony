<?php

namespace App\Controller;

use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $faker = Factory::create('fr_FR');

        $articles = [];

        for ($i=0;$i<10;$i++){
            $article = [];
            $article['title'] = $faker->sentence();
            $article['content'] = $faker->realText(600);
            $article['createdAt'] = $faker->date('d/m/Y H:i');
            $article['user'] = $faker->name();
            $article['picture'] = 'https://picsum.photos/300/200?id='.uniqid();
            //$faker->imageUrl(1920, 1080, 'animals');
            $articles[] = $article;
        }

        dump($articles);
        
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/page/{page}", name="pages")
     */
    public function pages(string $page): Response
    {
        return $this->render('blog/'.$page.'.html.twig', []);
    }

     /**
     * @Route("/contacts", name="contacts")
     */
    public function contacts(): Response
    {
        return $this->render('blog/contacts.html.twig', []);
    }
}
