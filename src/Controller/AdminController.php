<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/category/list", name="category_list")
     */
    public function categoryList(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('admin/category/list.html.twig', [
            'categories' => $categories
        ]);
    }   

    /**
     * @Route("/category/add", name="category_add")
     * @Route("/category/edit/{id}", name="category_edit")
     */
    public function categoryAdd(Category $category=null, Request $request, EntityManagerInterface $manager): Response
    {
        if ($category == null) 
            $category = new Category();
        
        $form = $this->createForm(CategoryType::class, $category);

        //On va lier l'objet formulaire avec la requête HTTP
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** On enregistre le commentaire */
            $manager->persist($category);
            $manager->flush();

            /* Mettre dans la session que le commentaira bien été enregistré !*/
            $this->addFlash('success','La catégorie a bien été ajoutée');

            return $this->redirectToRoute('category_list');
        }

        
        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('admin/category/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/category/del/{id}", name="category_del", methods={"GET"})
     */
    public function categoryDel(Category $category): Response
    {
        return $this->render('admin/category/del.html.twig', [
            'category' => $category
        ]);
    }

    /**
     * @Route("/category/del/{id}", name="category_del_confirm", methods={"DELETE"})
     */
    public function categoryDelConfirm(Category $category, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid('delete-value', $request->request->get('_token'))) {
            if (count($category->getArticles()) == 0) {
                $manager->remove($category);
                $manager->flush();

                $this->addFlash('success', 'L\'article ('.$category->getTitle().')  a bien été supprimé');
            }
            else {
                $this->addFlash('warning', 'La catégorie est rattachée à des articles et ne peut pas être supprimé !');
            }
        }
        else {
            $this->addFlash('danger','Erreur de token ! Merci de reconfirmer !');
        }

        return $this->redirectToRoute('category_list');

    }


     /**
     * @Route("/article/list", name="article_list")
     */
    public function articleList(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('admin/article/list.html.twig', [
            'articles' => $articles
        ]);
    }   


     /**
     * @Route("/article/add", name="article_add")
     * @Route("/article/edit/{id}", name="article_edit")
     */
    public function articleAdd(Article $article=null, Request $request, EntityManagerInterface $manager): Response
    {
        if ($article == null) 
            $article = new Article();
        
        $form = $this->createForm(ArticleType::class, $article);

        //On va lier l'objet formulaire avec la requête HTTP
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** On enregistre le commentaire */
            $manager->persist($article);
            $manager->flush();

            /* Mettre dans la session que le commentaira bien été enregistré !*/
            $this->addFlash('success','L\'article a bien été ajoutée');

            return $this->redirectToRoute('article_list');
        }

        
        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('admin/article/add.html.twig', [
            'form' => $form->createView(),
            'edit' => ($article->getId())?true:false
        ]);
    }

    /**
     * @Route("/article/del/{id}", name="article_del", methods={"GET"})
     */
    public function articleDel(Article $article): Response
    {
        return $this->render('admin/article/del.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/article/del/{id}", name="article_del_confirm", methods={"DELETE"})
     */
    public function articleDelConfirm(Article $article, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid('delete-value', $request->request->get('_token'))) {
            $manager->remove($article);
            $manager->flush();

            $this->addFlash('success','L\'article ('.$article->getTitle().')  a bien été supprimé');
        }
        else {
            $this->addFlash('danger','Erreur de token ! Merci de reconfirmer !');
        }

        return $this->redirectToRoute('article_list');

    }
}
