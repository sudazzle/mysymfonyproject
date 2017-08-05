<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Offer;
use AppBundle\Form\Extension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OfferController extends Controller {
   /**
   * @Route("/admin/create/offer")
   * @Template("AppBundle:Offer:common.html.twig")
   */
     
    public function createAction(Request $request) {        
      $offer = new Offer();
      $session = $request->getSession();
      $form = $this->createFormBuilder($offer)          
        ->add('title')
        ->add('detail1', 'textarea', array('label'=>'Some detail on this offer','required' => true,'attr' => array('class' => 'ckeditor')))  
        ->add('detail2', 'textarea', array('label'=>'Some more detail on this offer','required' => true,'attr' => array('class' => 'ckeditor')))        
        ->add('enabled', 'choice', array('label' => 'Do you want to show this offer on the offer Page?','choices' => array(true => 'Yes', false => 'No'), 'expanded' => true, 'multiple' => false) )   
        ->add('save', 'submit',  array('label' => 'Create An Offer','icon' => 'fa-plus', 'attr' => array('class' => 'btn-default btn btn-info',
                )))
        ->getForm(); 
 
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        try
        {
          $em = $this->getDoctrine()->getManager();    
                           
          $em->persist($offer);
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($offer->url, 301);
        }
        catch(Exception $e)
        {
          $this->addFlash('error', 'Your changes were saved!');
        }
      }
        return $this->render('offer/common.html.twig', array('form' => $form->createView(),
                                                              'header'=>'Add New Offer', 
                                                              'url'=>$offer->url, 
                                                              'title'=>'List All Offers')); 
     }
     
     /**
      * @Route("/admin/update/offer/{id}")
      * @Template("AppBundle:Offer:common.html.twig")
      */

     public function editAction($id, Request $request)
     {        
        $em = $this->getDoctrine()->getManager();
        $offer = $em->getRepository('AppBundle:Offer')->find($id);
        if(!$offer)
        {
            throw $this->createNotFoundException('No offer found for id ' . $id);
        }

        $form = $this->createFormBuilder($offer)
          ->add('title')
          ->add('detail1', 'textarea', array('label'=>'Some detail on this offer','required' => true,'attr' => array('class' => 'ckeditor')))  
          ->add('detail2', 'textarea', array('label'=>'Some more detail on this offer','required' => true,'attr' => array('class' => 'ckeditor')))
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
          return $this->redirect($offer->url, 301);           
        }
        $build['form'] = $form->createView();
        $build['header'] = "Update offer";
        $build['url'] = "/admin/offer";
        $build['title'] = "List All Offers";
        return $this->render('offer/common.html.twig', $build);
     }

     /**
      * @Route("/admin/delete/offer/{id}")
      * @Template("AppBundle:Offer:common.html.twig")
      */
     public function deleteAction($id, Request $request)
     {
        $em = $this->getDoctrine()->getmanager();  
        $offer = $em->getRepository('AppBundle:Offer')->find($id);

        if(!$offer) {
            throw $this->createNotFoundException('No offer found for id ' . $id);
        } 
        try {                          
          //$offer->removeThumbs(); 
          //$offer->removeUpload();   
          $em->remove($offer);        
          $em->flush();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($offer->url, 301);
     }
     /**
      * @Route("/admin/offer")
      * @Template("AppBundle:Offer:display.html.twig")
      */
     public function findAll(Request $request) {        
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM AppBundle:Offer a";
        $query = $em->createQuery($dql);
        $offer = new Offer();        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query, $request->query->get('page', 1)/*page number*/, 10/*limit per page*/ );        
        return $this->render('offer/display.html.twig', array('pagination' => $pagination, 
                                                                'header' => "Offer Setup"
                                                                ));       
     }

     /**
      * @Route("/admin/offer/hide/{id}")
      */
     public function hide($id, Request $request) {
        
        $offer = new Offer();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE offer SET enabled = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($offer->url, 301);                 
     }

     /**
      * @Route("/admin/offer/show/{id}")
      */
     public function show($id, Request $request) {
        $offer = new Offer(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE offer SET enabled = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($offer->url, 301);                 
     }   
}