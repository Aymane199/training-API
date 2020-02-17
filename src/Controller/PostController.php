<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/monapi/post", name="post", methods={"GET"})
     */
    public function index(PostRepository $postRepository)
    {
        return   $this->json($postRepository->findAll(),200,[],['groups'=>'post:read']);
    }
    /**
     * @Route("/monapi/post", name="post_store", methods={"POST"})
     */
    public function store(Request $request,SerializerInterface $serializer,EntityManagerInterface $em
        ,ValidatorInterface $validator)
    {
        try {
            $jsonRequest = $request->getContent();

            $post = $serializer->deserialize($jsonRequest,Post::class,'json');
            $post->setCreation(new \DateTime());

            $errors = $validator->validate($post);
            if(count($errors)>0){
                return $this->json($errors,400);
            }

            $em->persist($post);
            $em->flush();

            return $this->json($post,201,[],['groups'=>'post:read']);
        }catch(NotEncodableValueException $e){
            return $this->json([
                'status'=> 400,
                'message'=> $e->getMessage()
            ],400);
        }
    }

}
