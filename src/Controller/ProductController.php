<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\Type\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'create_product')]
    public function createProduct(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setName('Milch');
        //$product->setPrice(1999);
        $product->setDescription('1l');

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }
    #[Route('/product/add', name: 'create_product')]
    public function new(Request $request, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager): Response
    {
        // creates a task object and initializes some data for this example
        $product = new Product();
        $product->setName('Name');
        $product->setDescription('Description');
        $product->setPrice('100');
        $product->setDate(new \DateTime('today'));

        $form = $this->createForm(ProductType::class, $product, ['action' => $request->getRequestUri()]);
        $form->handleRequest($request);
        //print_r($form);
        if ($form->isSubmitted() /*&& $form->isValid()*/) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('products');
        }
        return $this->renderForm('product/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/products', name: 'products')]
    public function findAllGreaterThanPrice(ManagerRegistry $doctrine): Response
    {
        $minPrice = 0;
        $products = $doctrine->getRepository(Product::class)->findAllGreaterThanPrice($minPrice);
        return $this->render('product/products.html.twig', ['products' => $products]);
    }


    #[Route('/product/{id}', name: 'product_show')]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $product = $doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return new Response('Check out this great product: '.$product->getName());

        // or render a template
        // in the template, print things with {{ product.name }}
        // return $this->render('product/show.html.twig', ['product' => $product]);
    }

    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function edit(Request $request, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager,int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $form = $this->createForm(ProductType::class, $product, ['action' => $request->getRequestUri()]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($product);
                $entityManager->flush();

                return $this->redirectToRoute('products');
            }
    
            return $this->renderForm('product/new.html.twig', [
                'form' => $form,
            ]);




        $product->setName('New product name!');
        $entityManager->flush();

        return $this->redirectToRoute('product_show', [
            'id' => $product->getId()
        ]);
    }
    #[Route('/product/delete/{id}', name: 'product_delete')]
    public function deleteProduct(ManagerRegistry $doctrine, Request $request, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager,int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $products = $doctrine->getRepository(Product::class)->findAll();
        return $this->render('product/show.html.twig', ['products' => $products]);
    }
}
