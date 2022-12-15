<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomepageController extends AbstractController
{
    public function __construct(UserRepository $userRepository,ManagerRegistry $doctrine)
    { $this->userRepository = $userRepository;
    
    //  $this->categoryRepository = $categoryRepository;
      $this->entityManager = $doctrine->getManager();
        
    }
    
    
    
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    } 
   
}
