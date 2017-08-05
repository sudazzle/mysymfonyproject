<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\BannerType;
use AppBundle\Entity\Banner;
use AppBundle\Form\Extension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BannerController extends Controller {
   /**
   * @Route("/admin/create/banner")
   * @Template("AppBundle:banner:common.html.twig")
   */
     
    public function createAction(Request $request) {        
      $banner = new Banner();
      $session = $request->getSession();
      $form = $this->createFormBuilder($banner)          
        ->add('mainQuote')
        ->add('subQuote')     
        ->add('page', 'choice', array('choices' => array('home' => 'Home', 'services' => 'Services', 'offers'=>'Offers & Events', 'contact'=>'Contact Us', 'gallery'=>'Photo Gallery','about'=>'About Us'),))
        ->add('file', 'file', array('label' => 'Upload Image'))
        ->add('isEnabled', 'choice', array('label' => 'Display Banner','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   
        ->add('save', 'submit',  array('label' => 'Create Banner','icon' => 'fa-plus', 'attr' => array('class' => 'btn-default btn btn-info',
                )))
        ->getForm(); 
 
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        try
        {
          $em = $this->getDoctrine()->getManager();          
          $banner->upload();
          $banner->createThumbnail();                   
          $em->persist($banner);
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($banner->url, 301);
        }
        catch(Exception $e)
        {
          $this->addFlash('error', 'Your changes were saved!');
        }
      }
        return $this->render('banner/common.html.twig', array('form' => $form->createView(),
                                                              'header'=>'Add New Banner', 
                                                              'url'=>$banner->url, 
                                                              'title'=>'List All Banners',
                                                              'maxWidth'=>$banner->maxWidth,
                                                              'maxHeight'=>$banner->maxHeight,
                                                              'minWidth'=>$banner->minWidth,
                                                              'minHeight'=>$banner->minHeight,
                                                              'maxSize'=>$banner->maxSize)); 
     }
     
     /**
      * @Route("/admin/update/banner/{id}")
      * @Template("AppBundle:banner:common.html.twig")
      */

     public function editAction($id, Request $request)
     {        
        $em = $this->getDoctrine()->getManager();
        $banner = $em->getRepository('AppBundle:Banner')->find($id);
        if(!$banner)
        {
            throw $this->createNotFoundException('No Banner found for id ' . $id);
        }

        $form = $this->createFormBuilder($banner)
          ->add('mainQuote')
          ->add('subQuote') 
          ->add('save', 'submit',array('label' => 'Update','icon' => 'fa-edit', 'attr' => array('class' => 'btn-default btn btn-info',
                )))
          ->getForm();

        $form->handleRequest($request);

        if($form->isValid())
        {  
          try
          {
            $em->flush();              
            $this->addFlash('success', 'Your changes were saved!');
            
          }
          catch(Exception $e)
          {
              $this->addFlash('error', 'Your changes were saved!');
          }
          return $this->redirect($banner->url, 301);           
        }
        $build['form'] = $form->createView();
        $build['header'] = "Update Banner";
        $build['url'] = "/admin/banner";
        $build['title'] = "List All Banners";
        return $this->render('banner/common.html.twig', $build);
     }

     /**
      * @Route("/admin/delete/banner/{id}")
      * @Template("AppBundle:banner:common.html.twig")
      */
     public function deleteAction($id, Request $request)
     {
        $em = $this->getDoctrine()->getmanager();  
        $banner = $em->getRepository('AppBundle:Banner')->find($id);

        if(!$banner) {
            throw $this->createNotFoundException('No banner found for id ' . $id);
        } 
        try {                          
          //$banner->removeThumbs(); 
          //$banner->removeUpload();   
          $em->remove($banner);        
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($banner->url, 301);
     }
     /**
      * @Route("/admin/banner")
      * @Template("AppBundle:banner:display.html.twig")
      */
     public function findAll(Request $request) {        
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM AppBundle:Banner a";
        $query = $em->createQuery($dql);
        $banner = new Banner();        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query, $request->query->get('page', 1)/*page number*/, 10/*limit per page*/ );        
        return $this->render('banner/display.html.twig', array('pagination' => $pagination, 
                                                                'header' => "Banner Setup", 
                                                                'thumblocation'=>$banner->getThumbUploadDir()));       
     }

     /**
      * @Route("/admin/banner/hide/{id}")
      */
     public function hide($id, Request $request) {
        
        $banner = new Banner();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE banner SET is_enabled = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($banner->url, 301);                 
     }

     /**
      * @Route("/admin/banner/show/{id}")
      */
     public function show($id, Request $request) {
        $banner = new Banner(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE banner SET is_enabled = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($banner->url, 301);                 
     }

     /**
      * @Route("/admin/changeimage/banner/{id}")      
      */
     public function changeImage($id,Request $request) {           
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("SELECT file_name FROM banner WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $results = $statement->fetch();

          $old_filename = $results['file_name'];
          $banner = new Banner(); 
          $banner->setfileName($old_filename);
          
          $banner->removeUpload(); 
          $banner->removeThumbs();   

          $uploadedFile = $request->files->get('upfile');  
          $banner->setFile($uploadedFile); 
          $banner->upload();
          $banner->createThumbnail(); 

          $statement = $connection->prepare("UPDATE banner SET file_name = :filename WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->bindValue('filename', $banner->getFileName());
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($banner->url, 301);   
        }        
        catch(Exception $e)
        {
          $this->addFlash('error', 'Your changes were not saved!');
        }      
     }
}

//May require codes:
//->add('mainQuote', 'textarea', array('attr' => array('class' => 'ckeditor')))
//->add('subQuote', 'textarea')  


//->add('isEnabled', 'choice', array('label' => 'Display Banner','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   

