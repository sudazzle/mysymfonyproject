<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Lesson;
use AppBundle\Form\Extension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LessonController extends Controller {
   /**
   * @Route("/admin/create/lesson")
   * @Template("AppBundle:Lesson:common.html.twig")
   */
     
    public function createAction(Request $request) {        
      $lesson = new Lesson();
      $session = $request->getSession();
      $form = $this->createFormBuilder($lesson)          
        ->add('title')
        ->add('detail') 
        ->add('display', 'choice', array('label' => 'Do you want to display this lesson on the Home Page?','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )    
        //->add('page', 'choice', array('choices' => array('home' => 'Home', 'services' => 'Services', 'offers'=>'Offers & Events', 'contact'=>'Contact Us', 'gallery'=>'Photo Gallery','about'=>'About Us'),))
        ->add('file', 'file', array('label' => 'Upload Image'))
        ->add('enabled', 'choice', array('label' => 'Do you want to show this lesson on the Lesson Page?','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   
        ->add('save', 'submit',  array('label' => 'Create Lesson','icon' => 'fa-plus', 'attr' => array('class' => 'btn-default btn btn-info',
                )))
        ->getForm(); 
 
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        try
        {
          $em = $this->getDoctrine()->getManager();          
          $lesson->upload();
          $lesson->createThumbnail();                   
          $em->persist($lesson);
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($lesson->url, 301);
        }
        catch(Exception $e)
        {
          $this->addFlash('error', 'Your changes were saved!');
        }
      }
        return $this->render('lesson/common.html.twig', array('form' => $form->createView(),
                                                              'header'=>'Add New Lesson', 
                                                              'url'=>$lesson->url, 
                                                              'title'=>'List All Lessons',
                                                              'maxWidth'=>$lesson->maxWidth,
                                                              'maxHeight'=>$lesson->maxHeight,
                                                              'minWidth'=>$lesson->minWidth,
                                                              'minHeight'=>$lesson->minHeight,
                                                              'maxSize'=>$lesson->maxSize)); 
     }
     
     /**
      * @Route("/admin/update/lesson/{id}")
      * @Template("AppBundle:Lesson:common.html.twig")
      */

     public function editAction($id, Request $request)
     {        
        $em = $this->getDoctrine()->getManager();
        $lesson = $em->getRepository('AppBundle:Lesson')->find($id);
        if(!$lesson)
        {
            throw $this->createNotFoundException('No Lesson found for id ' . $id);
        }

        $form = $this->createFormBuilder($lesson)
          ->add('title')
          ->add('detail') 
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
          return $this->redirect($lesson->url, 301);           
        }
        $build['form'] = $form->createView();
        $build['header'] = "Update Lesson";
        $build['url'] = "/admin/lesson";
        $build['title'] = "List All lessons";
        return $this->render('lesson/common.html.twig', $build);
     }

     /**
      * @Route("/admin/delete/lesson/{id}")
      * @Template("AppBundle:Lesson:common.html.twig")
      */
     public function deleteAction($id, Request $request)
     {
        $em = $this->getDoctrine()->getmanager();  
        $lesson = $em->getRepository('AppBundle:Lesson')->find($id);

        if(!$lesson) {
            throw $this->createNotFoundException('No Lesson found for id ' . $id);
        } 
        try {                          
          //$Lesson->removeThumbs(); 
          //$Lesson->removeUpload();   
          $em->remove($lesson);        
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($lesson->url, 301);
     }
     /**
      * @Route("/admin/lesson")
      * @Template("AppBundle:Lesson:display.html.twig")
      */
     public function findAll(Request $request) {        
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM AppBundle:lesson a";
        $query = $em->createQuery($dql);
        $lesson = new Lesson();        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query, $request->query->get('page', 1)/*page number*/, 10/*limit per page*/ );        
        return $this->render('lesson/display.html.twig', array('pagination' => $pagination, 
                                                                'header' => "Lesson Setup", 
                                                                'thumblocation'=>$lesson->getThumbUploadDir()));       
     }

     /**
      * @Route("/admin/lesson/hide/{id}")
      */
     public function hide($id, Request $request) {
        
        $lesson = new Lesson();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE lesson SET enabled = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($lesson->url, 301);                 
     }

     /**
      * @Route("/admin/lesson/hideonhome/{id}")
      */
     public function hideonhome($id, Request $request) {
        
        $lesson = new Lesson();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE lesson SET display = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($lesson->url, 301);                 
     }



     /**
      * @Route("/admin/lesson/show/{id}")
      */
     public function show($id, Request $request) {
        $lesson = new Lesson(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE lesson SET enabled = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($lesson->url, 301);                 
     }

      /**
      * @Route("/admin/lesson/showonhome/{id}")
      */
     public function showonhome($id, Request $request) {
        $lesson = new Lesson(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE lesson SET display = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($lesson->url, 301);                 
     }

     /**
      * @Route("/admin/changeimage/lesson/{id}")      
      */
     public function changeImage($id,Request $request) {           
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("SELECT filename FROM lesson WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $results = $statement->fetch();

          $old_filename = $results['filename'];
          $lesson = new Lesson(); 
          $lesson->setfilename($old_filename);
          
          $lesson->removeUpload(); 
          $lesson->removeThumbs();   

          $uploadedFile = $request->files->get('upfile');  
          $lesson->setFile($uploadedFile); 
          $lesson->upload();
          $lesson->createThumbnail(); 

          $statement = $connection->prepare("UPDATE lesson SET filename = :filename WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->bindValue('filename', $lesson->getfilename());
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($lesson->url, 301);   
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


//->add('isEnabled', 'choice', array('label' => 'Display Lesson','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   

