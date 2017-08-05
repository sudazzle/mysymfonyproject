<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Event;
use AppBundle\Form\Extension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EventController extends Controller {
   /**
   * @Route("/admin/create/event")
   * @Template("AppBundle:Event:common.html.twig")
   */
     
    public function createAction(Request $request) {        
      $event = new Event();
      $session = $request->getSession();
      $form = $this->createFormBuilder($event)          
        ->add('title') 
        ->add('detail', 'textarea', array('label'=>'Write some detail about this event.','required' => true,'attr' => array('class' => 'ckeditor')))                    
        ->add('file', 'file', array('label' => 'Upload Image'))
        ->add('eventdate', 'date', [
              'widget' => 'single_text',
              'format' => 'dd-MM-yyyy',
              'attr' => [
                  'class' => 'form-control input-inline datepicker',
                  'data-provide' => 'datepicker',
                  'data-date-format' => 'dd-mm-yyyy'
              ]])
        ->add('enabled', 'choice', array('label' => 'Do you want to show this event on the event Page?','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   
        ->add('save', 'submit',  array('label' => 'Create Event','icon' => 'fa-plus', 'attr' => array('class' => 'btn-default btn btn-info',
                )))
        ->getForm(); 
 
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        try
        {
          $em = $this->getDoctrine()->getManager();          
          $event->upload();
          $event->createThumbnail();                   
          $em->persist($event);
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($event->url, 301);
        }
        catch(Exception $e)
        {
          $this->addFlash('error', 'Your changes were saved!');
        }
      }
        return $this->render('event/common.html.twig', array('form' => $form->createView(),
                                                              'header'=>'Add New Event', 
                                                              'url'=>$event->url, 
                                                              'title'=>'List All Events',
                                                              'maxWidth'=>$event->maxWidth,
                                                              'maxHeight'=>$event->maxHeight,
                                                              'minWidth'=>$event->minWidth,
                                                              'minHeight'=>$event->minHeight,
                                                              'maxSize'=>$event->maxSize)); 
     }
     
     /**
      * @Route("/admin/update/event/{id}")
      * @Template("AppBundle:Event:common.html.twig")
      */

     public function editAction($id, Request $request)
     {        
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('AppBundle:Event')->find($id);
        if(!$event)
        {
            throw $this->createNotFoundException('No Event found for id ' . $id);
        }

        $form = $this->createFormBuilder($event)
          ->add('title')
          ->add('detail') 
          ->add('eventdate', 'date', [
              'widget' => 'single_text',
              'format' => 'dd-MM-yyyy',
              'attr' => [
                  'class' => 'form-control input-inline datepicker',
                  'data-provide' => 'datepicker',
                  'data-date-format' => 'dd-mm-yyyy'
              ]])
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
          return $this->redirect($event->url, 301);           
        }
        $build['form'] = $form->createView();
        $build['header'] = "Update event";
        $build['url'] = "/admin/event";
        $build['title'] = "List All events";
        return $this->render('event/common.html.twig', $build);
     }

     /**
      * @Route("/admin/delete/event/{id}")
      * @Template("AppBundle:Event:common.html.twig")
      */
     public function deleteAction($id, Request $request)
     {
        $em = $this->getDoctrine()->getmanager();  
        $event = $em->getRepository('AppBundle:event')->find($id);

        if(!$event) {
            throw $this->createNotFoundException('No event found for id ' . $id);
        } 
        try {                          
          //$event->removeThumbs(); 
          //$event->removeUpload();   
          $em->remove($event);        
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($event->url, 301);
     }
     /**
      * @Route("/admin/event")
      * @Template("AppBundle:Event:display.html.twig")
      */
     public function findAll(Request $request) {        
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM AppBundle:Event a";
        $query = $em->createQuery($dql);
        $event = new Event();        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query, $request->query->get('page', 1)/*page number*/, 10/*limit per page*/ );        
        return $this->render('event/display.html.twig', array('pagination' => $pagination, 
                                                                'header' => "Event Setup", 
                                                                'thumblocation'=>$event->getThumbUploadDir()));       
     }

     /**
      * @Route("/admin/event/hide/{id}")
      */
     public function hide($id, Request $request) {
        
        $event = new Event();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE event SET enabled = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($event->url, 301);                 
     }

      /**
      * @Route("/admin/event/show/{id}")
      */
     public function show($id, Request $request) {
        $event = new Event(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE event SET enabled = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($event->url, 301);                 
     }      

     /**
      * @Route("/admin/changeimage/event/{id}")      
      */
     public function changeImage($id,Request $request) {           
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("SELECT filename FROM event WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $results = $statement->fetch();

          $old_filename = $results['filename'];
          $event = new Event(); 
          $event->setfilename($old_filename);
          
          $event->removeUpload(); 
          $event->removeThumbs();   

          $uploadedFile = $request->files->get('upfile');  
          $event->setFile($uploadedFile); 
          $event->upload();
          $event->createThumbnail(); 

          $statement = $connection->prepare("UPDATE event SET filename = :filename WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->bindValue('filename', $event->getfilename());
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($event->url, 301);   
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


//->add('isEnabled', 'choice', array('label' => 'Display event','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   

