<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    public function __construct(ProductRepository $productRepository,ManagerRegistry $doctrine,CategoryRepository $categoryRepository)
    { $this->categoryRepository = $categoryRepository;
      $this->productRepository = $productRepository;
    //  $this->categoryRepository = $categoryRepository;
      $this->entityManager = $doctrine->getManager();
        
    }
    
    #[Route('/products', name: 'products')]
    public function index(): Response
    { if(!$this->getUser())
        return $this->redirectToRoute('app_login');
    
        $products = $this->productRepository->findAll();
        $categories = $this->categoryRepository->findAll();
        return $this->render('product/products.html.twig', [
            'products' => $products,
            'categories'=>$categories,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }

    #[Route('/product/{category}', name: 'product_category')]
    public function categoryProducts(Category $category): Response
    {if(!$this->getUser())
        return $this->redirectToRoute('app_login');
    
        $categories = $this->categoryRepository->findAll();
        return $this->render('product/products.html.twig', [
            'products' => $category->getProducts(),
            'categories' => $categories,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }

    #[Route('/product/details/{id}', name: 'product_details')]
    public function show(Product $product): Response
    { if(!$this->getUser())
        return $this->redirectToRoute('app_login');
    
        return $this->render('product/details.html.twig', [
            'product' => $product,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }
   
     #[IsGranted(new Expression('"ROLE_ADMIN" in role_names '))]
     #[Route('/store/product', name: 'store_product')]
    public function storeProduct(Request $request): Response
    { if(!$this->getUser())
        return $this->redirectToRoute('app_login');
    
        $product = new Product();
        $form = $this->createForm(ProductType::class,$product);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           $product= $form->getData();
           if($request->files->get('product')['image']){
            $image = $request->files->get('product')['image'];
            $image_name = time().'_'.$image->getClientOriginalName();
            $image->move($this->getParameter('image_directory'),$image_name);
            $product->setImage($image_name);
           }
           $this->entityManager->persist($product);
           $this->entityManager->flush();
           $this->addFlash('success','Votre produit est bien sauveguardée');
           return $this->redirectToRoute('products');
        }
        return $this->renderForm('product/create.html.twig', [
            'form' => $form
        ]);
    }
    
    #[IsGranted(new Expression('"ROLE_ADMIN" in role_names '))]
    #[Route('/product/delete/{id}', name: 'product_delete')]
    public function delete(Product $product): Response
    { if(!$this->getUser())
        return $this->redirectToRoute('app_login');
    
        $filesystem = new Filesystem(); 
        $imagePath = './uploads/'.$product->getImage();
        if($filesystem->exists($imagePath)){
            $filesystem->remove($imagePath);
        }

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $this->entityManager->remove($product);

        // actually executes the queries (i.e. the INSERT query)
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            'Votre produit est bien supprimé'
        );
        return $this->redirectToRoute('products');
    }
    
    #[IsGranted(new Expression('"ROLE_ADMIN" in role_names '))]
    #[Route('/product/edit/{id}', name: 'product_edit')]
    
    public function editProduct(Product $product,Request $request): Response
    {if(!$this->getUser())
        return $this->redirectToRoute('app_login');
    
        $form = $this->createForm(ProductType::class,$product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $product = $form->getData();
            if($request->files->get('product')['image']){
                $image = $request->files->get('product')['image'];
                $image_name = time().'_'.$image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'),$image_name);
                $product->setImage($image_name);
            }
          
            $this->entityManager->persist($product);

            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Votre produit est bien mis à jour'
            );
            return $this->redirectToRoute('products');
        }

        return $this->renderForm('product/edit.html.twig', [
            'form' => $form
        ]);       
    }

}
