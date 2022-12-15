<?php

namespace App\DataFixtures;


use App\Entity\User;
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{ 
  
  
  public function __construct(UserPasswordHasherInterface $passwordHasher)
  {
      $this->passwordHasher = $passwordHasher;
  }

   
    public function load(ObjectManager $manager): void
    { 

       $Images = [
        'astroid.jpg',
        'mercury.jpg',
        'lightspeed.jpg',
    ];

$array =[];

    for ($i = 0; $i < 4; $i++) {
      $category = new Category();
      $category->setName('category '.$i);
      
      $category->setSlug('category '.$i);
      
      $manager->persist($category);
      $manager->flush($category);
      $array[]=$category;
    }
     
     for ($i = 0; $i < 20; $i++) {
    
        $product = new Product();
        $product->setName('product '.$i);
        
        $product->setDescription('product '.$i);
        $product->setSlug('produqzdact '.$i);
        $product->setImage( $Images[mt_rand(0, 2)]);
        $product->setCategory($array[mt_rand(0, 3)]);
        $product->setPrice(mt_rand(10, 100));
        $manager->persist($product);
        
      $manager->flush($product);
      }
     $user = new User();
      $plainPassword = "123456789";
      $hashedPassword = $this->passwordHasher
          ->hashPassword($user, $plainPassword);
      $user->setUsername('admin');
      $user->setPassword($hashedPassword);
      $user->setRoles(['ROLE_ADMIN']);
      $manager->persist($user);

      $manager->flush();
    }
    
}

