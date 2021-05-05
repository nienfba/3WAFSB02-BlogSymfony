<?php

namespace App\Controller;

use Faker\Factory;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\MessageGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{

    const MAX_ARTICLE_BY_PAGE = 10;

    /**
     * @Route("/", name="home")
     * @Route("/view/{page}", name="homePage")
     */
    public function index(int $page = 0, ArticleRepository $articleRepository, MessageGenerator $messageGenerator): Response
    {
        //On récupète le nombre d'article
        $countArticle = $articleRepository->count();
        $pageNumber = floor($countArticle/self::MAX_ARTICLE_BY_PAGE);

        // Récupération des articles valid triés par date de publication décroissante
        $articles = $articleRepository->findAllByDateAndValid(self::MAX_ARTICLE_BY_PAGE, self::MAX_ARTICLE_BY_PAGE*$page);

        
        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('blog/index.html.twig', [
            'articles'      => $articles,
            'message'       => $messageGenerator->getHappyMessage(),
            'nextPage'      => ($page<$pageNumber)?$page+1:null,
            'previousPage'  => ($page>0)?$page-1:null
        ]);
    }

    /**
     * @Route("/article/{slug}", name="article")
     */
    public function article(Article $article, Request $request, EntityManagerInterface $manager): Response
    {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        //On va lier l'objet formulaire avec la requête HTTP
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /* On renseigne les données complémentaire nécessaire au commentaire !*/
            $comment->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $comment->setValid(true);
            $comment->setArticle($article);

            /** On enregistre le commentaire */
            $manager->persist($comment);
            $manager->flush();

            /* Mettre dans la session que le commentaira bien été enregistré !*/
            $this->addFlash('success','Votre commentaire a bien été ajouté');

            return $this->redirectToRoute('article', ['slug' => $article->getSlug()]);
        }

        
        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('blog/article.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/categories", name="categories")
     */
    public function categories(CategoryRepository $categoryRepository): Response
    {
        //int $id, ArticleRepository $articleRepository
        //$article = $articleRepository->findOneById($id);

        $categories = $categoryRepository->findBy([], ['title'=>'ASC']);
        
        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('blog/categories.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/category/{slug}", name="category")
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
