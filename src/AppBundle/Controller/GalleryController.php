<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Gallery;
use AppBundle\Form\Extension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GalleryController extends Controller {
   /**
   * @Route("/admin/create/gallery")
   * @Template("AppBundle:Gallery:common.html.twig")
   */
     
    public function createAction(Request $request) {        
      $gallery = new Gallery();
      $session = $request->getSession();
      $form = $this->createFormBuilder($gallery)        
        ->add('file', 'file', array('label' => 'Upload Image'))
        ->add('caption')
        ->add('enabled', 'choice', array('label' => 'Do you want to show this photo on the gallery Page?','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   
        ->add('save', 'submit',  array('label' => 'Add a Photo','icon' => 'fa-plus', 'attr' => array('class' => 'btn-default btn btn-info',
                )))
        ->getForm(); 
 
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        try
        {
          $em = $this->getDoctrine()->getManager();          
          $gallery->upload();
          $gallery->createThumbnail();                   
          $em->persist($gallery);
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($gallery->url, 301);
        }
        catch(Exception $e)
        {
          $this->addFlash('error', 'Your changes were saved!');
        }
      }
        return $this->render('gallery/common.html.twig', array('form' => $form->createView(),
                                                              'header'=>'Add New Photo', 
                                                              'url'=>$gallery->url, 
                                                              'title'=>'List All Gallery Photos',
                                                              'maxWidth'=>$gallery->maxWidth,
                                                              'maxHeight'=>$gallery->maxHeight,
                                                              'minWidth'=>$gallery->minWidth,
                                                              'minHeight'=>$gallery->minHeight,
                                                              'maxSize'=>$gallery->maxSize)); 
     }
     
     /**
      * @Route("/admin/update/gallery/{id}")
      * @Template("AppBundle:Gallery:common.html.twig")
      */

     public function editAction($id, Request $request)
     {        
        $em = $this->getDoctrine()->getManager();
        $gallery = $em->getRepository('AppBundle:Gallery')->find($id);
        if(!$gallery)
        {
            throw $this->createNotFoundException('No gallery found for id ' . $id);
        }

        $form = $this->createFormBuilder($gallery)
          ->add('caption')          
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
          return $this->redirect($gallery->url, 301);           
        }
        $build['form'] = $form->createView();
        $build['header'] = "Update Photo Caption";
        $build['url'] = "/admin/gallery";
        $build['title'] = "List All Gallery Photos";
        return $this->render('gallery/common.html.twig', $build);
     }

     /**
      * @Route("/admin/delete/gallery/{id}")
      * @Template("AppBundle:Gallery:common.html.twig")
      */
     public function deleteAction($id, Request $request)
     {
        $em = $this->getDoctrine()->getmanager();  
        $gallery = $em->getRepository('AppBundle:Gallery')->find($id);

        if(!$gallery) {
            throw $this->createNotFoundException('No gallery found for id ' . $id);
        } 
        try {                          
          $gallery->removeThumbs(); 
          $gallery->removeUpload();   
          $em->remove($gallery);        
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($gallery->url, 301);
     }
     /**
      * @Route("/admin/gallery")
      * @Template("AppBundle:Gallery:display.html.twig")
      */
     public function findAll(Request $request) {        
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM AppBundle:gallery a";
        $query = $em->createQuery($dql);
        $gallery = new gallery();        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query, $request->query->get('page', 1)/*page number*/, 10/*limit per page*/ );        
        return $this->render('gallery/display.html.twig', array('pagination' => $pagination, 
                                                                'header' => "Gallery Setup", 
                                                                'thumblocation'=>$gallery->getThumbUploadDir()));       
     }

     /**
      * @Route("/admin/gallery/hide/{id}")
      */
     public function hide($id, Request $request) {
        
        $gallery = new gallery();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE gallery SET enabled = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($gallery->url, 301);                 
     }

          /**
      * @Route("/admin/gallery/show/{id}")
      */
     public function show($id, Request $request) {
        $gallery = new Gallery(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE gallery SET enabled = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($gallery->url, 301);                 
     }      
}

