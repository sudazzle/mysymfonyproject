<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Testimonials;
use AppBundle\Form\Extension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TestimonialsController extends Controller {
   /**
   * @Route("/admin/create/testimonials")
   * @Template("AppBundle:Testimonials:common.html.twig")
   */
     
    public function createAction(Request $request) {        
      $testimonials = new Testimonials();
      $session = $request->getSession();
      $form = $this->createFormBuilder($testimonials)          
        ->add('author')
        ->add('testimonial') 
        ->add('display', 'choice', array('label' => 'Do you want to display this testimonial on the Home Page?','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )    
        //->add('page', 'choice', array('choices' => array('home' => 'Home', 'services' => 'Services', 'offers'=>'Offers & Events', 'contact'=>'Contact Us', 'gallery'=>'Photo Gallery','about'=>'About Us'),))
        ->add('file', 'file', array('label' => 'Upload Image'))
        ->add('save', 'submit',  array('label' => 'Create Testimonial','icon' => 'fa-plus', 'attr' => array('class' => 'btn-default btn btn-info',
                )))
        ->getForm(); 
 
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        try
        {
          $em = $this->getDoctrine()->getManager();          
          $testimonials->upload();
          $testimonials->createThumbnail();                   
          $em->persist($testimonials);
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($testimonials->url, 301);
        }
        catch(Exception $e)
        {
          $this->addFlash('error', 'Your changes were saved!');
        }
      }
        return $this->render('testimonials/common.html.twig', array('form' => $form->createView(),
                                                              'header'=>'Add New Testimonial', 
                                                              'url'=>$testimonials->url, 
                                                              'title'=>'List All Testimonials',
                                                              'maxWidth'=>$testimonials->maxWidth,
                                                              'maxHeight'=>$testimonials->maxHeight,
                                                              'minWidth'=>$testimonials->minWidth,
                                                              'minHeight'=>$testimonials->minHeight,
                                                              'maxSize'=>$testimonials->maxSize)); 
     }
     
     /**
      * @Route("/admin/update/testimonials/{id}")
      * @Template("AppBundle:Testimonials:common.html.twig")
      */

     public function editAction($id, Request $request)
     {        
        $em = $this->getDoctrine()->getManager();
        $testimonials = $em->getRepository('AppBundle:Testimonials')->find($id);
        if(!$testimonials)
        {
            throw $this->createNotFoundException('No testimonials found for id ' . $id);
        }

        $form = $this->createFormBuilder($testimonials)
          ->add('author')
          ->add('testimonial') 
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
          return $this->redirect($testimonials->url, 301);           
        }
        $build['form'] = $form->createView();
        $build['header'] = "Update testimonials";
        $build['url'] = "/admin/testimonials";
        $build['title'] = "List All testimonialss";
        return $this->render('testimonials/common.html.twig', $build);
     }

     /**
      * @Route("/admin/delete/testimonials/{id}")
      * @Template("AppBundle:Testimonials:common.html.twig")
      */
     public function deleteAction($id, Request $request)
     {
        $em = $this->getDoctrine()->getmanager();  
        $testimonials = $em->getRepository('AppBundle:testimonials')->find($id);

        if(!$testimonials) {
            throw $this->createNotFoundException('No testimonials found for id ' . $id);
        } 
        try {                          
          //$testimonials->removeThumbs(); 
          //$testimonials->removeUpload();   
          $em->remove($testimonials);        
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($testimonials->url, 301);
     }
     /**
      * @Route("/admin/testimonials")
      * @Template("AppBundle:Testimonials:display.html.twig")
      */
     public function findAll(Request $request) {        
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM AppBundle:testimonials a";
        $query = $em->createQuery($dql);
        $testimonials = new testimonials();        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query, $request->query->get('page', 1)/*page number*/, 10/*limit per page*/ );        
        return $this->render('testimonials/display.html.twig', array('pagination' => $pagination, 
                                                                'header' => "Testimonials Setup", 
                                                                'thumblocation'=>$testimonials->getThumbUploadDir()));       
     }

     /**
      * @Route("/admin/testimonials/hide/{id}")
      */
     public function hide($id, Request $request) {
        
        $testimonials = new Testimonials();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE testimonials SET enabled = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($testimonials->url, 301);                 
     }

     /**
      * @Route("/admin/testimonials/hideonhome/{id}")
      */
     public function hideonhome($id, Request $request) {
        
        $testimonials = new Testimonials();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE testimonials SET display = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($testimonials->url, 301);                 
     }



     /**
      * @Route("/admin/testimonials/show/{id}")
      */
     public function show($id, Request $request) {
        $testimonials = new Testimonials(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE testimonials SET enabled = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($testimonials->url, 301);                 
     }

      /**
      * @Route("/admin/testimonials/showonhome/{id}")
      */
     public function showonhome($id, Request $request) {
        $testimonials = new Testimonials(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE testimonials SET display = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($testimonials->url, 301);                 
     }

     /**
      * @Route("/admin/changeimage/testimonials/{id}")      
      */
     public function changeImage($id,Request $request) {           
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("SELECT filename FROM testimonials WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $results = $statement->fetch();

          $old_filename = $results['filename'];
          $testimonials = new Testimonials(); 
          $testimonials->setfilename($old_filename);
          
          $testimonials->removeUpload(); 
          $testimonials->removeThumbs();   

          $uploadedFile = $request->files->get('upfile');  
          $testimonials->setFile($uploadedFile); 
          $testimonials->upload();
          $testimonials->createThumbnail(); 

          $statement = $connection->prepare("UPDATE testimonials SET filename = :filename WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->bindValue('filename', $testimonials->getfilename());
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($testimonials->url, 301);   
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


//->add('isEnabled', 'choice', array('label' => 'Display testimonials','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   

