<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class BlogController extends Controller
{
    /**
     * @Route("/", name="blog_index")
     */

     public function indexAction(Request $request)
    {
        // return $this->render('blog/index.html.twig'); //renderメソッドは他のページヘのリンク返します。
        $em = $this->getDoctrine()->getManager();
      
        $posts = $em->getRepository(Post::class)->findAll();

        
        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
        ]);
    }


    //ページ末尾に/{そのユーザーのID}を打ち込むとidナンバー１の書き込みを見れる
    //requirementsの記述は{id} は数字しかマッチしないようにしてる。requirements属性を利用するとそのプレースホルダーにマッチさせたい条件を正規表現で指定することができます。
    /**
     * @Route("/{id}", name="blog_show",requirements={"id"="\d+"})
     */
   
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        
        return $this->render('blog/show.html.twig', ['post' => $post]);
    }

     /**
     * @Route("/new", name="blog_new")
     */
    public function newAction(Request $request)
    {
        // フォームの組立
        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add('content')
            ->getForm();

          
        // PSST判定&バリデーション
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // エンティティを永続化
            $post->setCreatedAt(new \DateTime());
            $post->setUpdatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }
        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="blog_edit", requirements={"id"="\d+"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }

        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add('content')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // フォームから送信されてきた値と一緒に更新日時も更新して保存
            $post->setUpdatedAt(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }

        // 新規作成するときと同じテンプレートを利用
        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/{id}/delete", name="blog_delete", requirements={"id"="\d+"})
     */

    function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }
        // 削除
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('blog_index');
    }
      
}

       