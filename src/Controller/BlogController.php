<?php

namespace App\Controller;

use Faker\Factory;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        // Récupération des articles valid triés par date de publication décroissante
        $articles = $articleRepository->findBy(['valid'=>true], ['publishedAt'=>'DESC']);

        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('blog/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/article/{id}", name="article")
     */
    public function article(Article $article, Request $request, EntityManagerInterface $manager): Response
    {
        //int $id, ArticleRepository $articleRepository
        //$article = $articleRepository->findOneById($id);

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        //On va lier l'objet formulaire avec la requête HTTP
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /* On renseigne les données complémentaire nécessaire !*/
            $comment->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $comment->setValid(true);
            $comment->setArticle($article);

            /** On enregistre le commentaire */
            $manager->persist($comment);
            $manager->flush();

            /* Mettre dans la session que le commentaira bien été enregistré !*/
            $this->addFlash('success','Votre commentaire a bien été ajouté');

            return $this->redirectToRoute('article', ['id' => $article->getId()]);
        }

        
        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('blog/article.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/category/{id}", name="category")
     */
    public function category(Category $category): Response
    {
        //int $id, ArticleRepository $articleRepository
        //$article = $articleRepository->findOneById($id);
        
        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('blog/category.html.twig', [
            'category' => $category
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
