<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use App\Service\FileUploader;
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
        $categories = $categoryRepository->findBy([],['updateAt'=>'DESC','createdAt'=>'DESC']);

        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('admin/category/list.html.twig', [
            'categories' => $categories
        ]);
    }   

    /**
     * @Route("/category/add", name="category_add")
     * @Route("/category/edit/{id}", name="category_edit")
     */
    public function categoryAdd(Category $category=null, Request $request, EntityManagerInterface $manager, FileUploader $fileUploader): Response
    {
        if ($category == null) 
            $category = new Category();
        
        $form = $this->createForm(CategoryType::class, $category);

        //On va lier l'objet formulaire avec la requête HTTP
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictureUpload = $form->get('pictureUpload')->getData();
            if ($pictureUpload) {
                $picture = $fileUploader->upload($pictureUpload,'category');
                $category->setPicture($picture);
            }
            /** On enregistre la catégorie */
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
        $articles = $articleRepository->findBy([],['updatedAt'=>'DESC','createdAt'=>'DESC']);

        // Récupération de la Reponse fournie par la vue Twig. On lui passe les articles
        return $this->render('admin/article/list.html.twig', [
            'articles' => $articles
        ]);
    }   


     /**
     * @Route("/article/add", name="article_add")
     * @Route("/article/edit/{id}", name="article_edit")
     */
    public function articleAdd(Article $article=null, Request $request, EntityManagerInterface $manager, FileUploader $fileUploader): Response
    {
        if ($article == null) 
            $article = new Article();
        
        $form = $this->createForm(ArticleType::class, $article);

        //On va lier l'objet formulaire avec la requête HTTP
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictureUpload = $form->get('pictureUpload')->getData();
            if ($pictureUpload) {
                $picture = $fileUploader->upload($pictureUpload, 'article');
                $article->setPicture($picture);
            }
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
     * @Route("/article/publish/{id}", name="article_publish")
     */
    public function articlePublish(Article $article, EntityManagerInterface $manager): Response
    {
        $article->setValid(!$article->getValid());
        $manager->persist($article);
        $manager->flush();
        
        return $this->redirectToRoute('article_list');
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

    

    /**
     * @Route("/comment/list", name="comment_list")
     */
    public function commentList(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy([],['createdAt'=>'DESC']);

        // Récupération de la Reponse fournie par la vue Twig. On lui passe les comments
        return $this->render('admin/comment/list.html.twig', [
            'comments' => $comments
        ]);
    }

     /**
     * @Route("/comment/publish/{id}", name="comment_publish")
     */
    public function commentPublish(Comment $comment, EntityManagerInterface $manager): Response
    {
        $comment->setValid(!$comment->getValid());
        $manager->persist($comment);
        $manager->flush();
        
        return $this->redirectToRoute('comment_list');
    }

    /**
     * @Route("/comment/del/{id}", name="comment_del", methods={"GET"})
     */
    public function commentDel(Comment $comment): Response
    {
        return $this->render('admin/comment/del.html.twig', [
            'comment' => $comment
        ]);
    }

     /**
     * @Route("/comment/del/{id}", name="comment_del_confirm", methods={"DELETE"})
     */
    public function commentDelConfirm(Comment $comment, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid('delete-value', $request->request->get('_token'))) {
            $manager->remove($comment);
            $manager->flush();

            $this->addFlash('success','Le commentaire de '.$comment->getPseudo().'  a bien été supprimé');
        }
        else {
            $this->addFlash('danger','Erreur de token ! Merci de reconfirmer !');
        }

        return $this->redirectToRoute('comment_list');

    }

}
