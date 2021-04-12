<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
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
        if($category == null)
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
}
