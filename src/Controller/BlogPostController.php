<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Form\BlogPostType;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\BlogPostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("")
 */
class BlogPostController extends AbstractController
{
    /**
     * @Route("/", name="blog_post_index", methods={"GET"})
     */
    public function index(BlogPostRepository $blogPostRepository): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_USER');
        $blog_posts = $this->getDoctrine()->getRepository(BlogPost::class)->findBy(
            array(),
            ['id' => 'DESC'],
            5
        );
        return $this->render('blog_post/index.html.twig', [
            'blog_posts' => $blog_posts,
        ]);
    }

    /**
     * @Route("/new", name="blog_post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $blogPost = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);
        $user = $this->getUser();
        $blogPost->setAuthor($user);

        if ($form->isSubmitted() && $form->isValid()) {
            $blogPost->setCreatedAt(new \DateTime);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($blogPost);
            $entityManager->flush();

            return $this->redirectToRoute('blog_post_index');
        }

        return $this->render('blog_post/new.html.twig', [
            'blog_post' => $blogPost,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/blog/details/{id}", name="blog_post_show", methods={"GET","POST"})
     */
    public function show(BlogPost $blogPost, Request $request): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
        $commentaire->setBlog($blogPost);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setCreatedAt(new \DateTime);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('blog_post_show', array('id' => $blogPost->getId()));
        }

        return $this->render('blog_post/show.html.twig', [
            'blog_post' => $blogPost,
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/blog&commentaire/{id}", name="blog_user_show", methods={"GET"})
     */
    public function userId(BlogPostRepository $blogPostRepository): Response
    {
        $commentaires = $this->getDoctrine()->getRepository(Commentaire::class)->findBy(
            array(),
            ['id' => 'DESC'],
            10
            
        );

        $blog_posts = $this->getDoctrine()->getRepository(BlogPost::class)->findBy(
            array(),
            ['id' => 'DESC'],
            6
        );

        return $this->render('blog_post/show_author.html.twig', [
            'blog_posts' => $blog_posts,
            'commentaires' => $commentaires,
        ]);
    }

    /**
     * @Route("/post/{id}/edit", name="blog_post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, BlogPost $blogPost): Response
    {
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('blog_post_index');
        }

        return $this->render('blog_post/edit.html.twig', [
            'blog_post' => $blogPost,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="blog_post_delete", methods={"DELETE"})
     */
    public function delete(Request $request, BlogPost $blogPost): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blogPost->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($blogPost);
            $entityManager->flush();
        }

        return $this->redirectToRoute('blog_post_index');
    }

    /**
     * @Route("/delete/{comment_id}", name="comment_delete", methods={"DELETE"})
     * @ParamConverter("commentaire", class="App:Commentaire", options={"id": "comment_id"})
     */
    public function deleteCommentaire(Request $request, Commentaire $commentaire): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commentaire->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($commentaire);
            $entityManager->flush();
            return $this->redirectToRoute('blog_post_index');
        }


        return $this->render('commentaires/_delete_form.html.twig', [
            'commentaire' => $commentaire
        ]);
    }
}
