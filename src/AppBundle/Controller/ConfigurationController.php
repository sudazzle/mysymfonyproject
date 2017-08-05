<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Configuration;
use AppBundle\Form\Extension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ConfigurationController extends Controller {
      

    /**
    * @Route("/admin/update/configuration")   
    * @Template() 
    * @Method("POST")
    */
    public function updateConfiguration(Request $request)
    {
        
        /*$configuration = new Configuration();
        $qb = $this->createQueryBuilder('AppBundle:Configuration');
        $id = $request->request->get('id');
        $value = $request->request->get('value');       
        $linkurl = $request->request->get('linkurl');
        $q = $qb->update('AppBundle:Configuration a')
            ->set('a.value', $value)    
            ->set('a.linkurl', $linkurl)               
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();  */

            return $this->redirect('/admin/banner', 301);      
    }

     /**
      * @Route("/admin/configuration")
      * @Template("AppBundle:Configuration:display.html.twig")
      */
     public function findAll(Request $request) {        
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM AppBundle:Configuration a";
        $query = $em->createQuery($dql);
        $configuration = new Configuration();
        $configs = $query->getResult(); 
        foreach ($configs as $config)    
        {
           if($config->getParameter() == 'COMPANY_NAME')
           {
              $companyname = array(
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'COUNTRY')
           {
              $country = array(
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'STATE')
           {
              $state = array(
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'PROVINCE')
           {
              $province = array(
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'ADDRESS')
           {
              $address = array(
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'CONTACTNO')
           {
              $contactno = array(
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'EMAIL')
           {
              $email = array(
                "value" => $config->getValue(),  
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'COMPANYMISSION')
           {
              $mission = array( 
                "value" => $config->getValue(),  
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'COMPANYVISSION')
           {
              $vision = array(
                "value" => $config->getValue(),  
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'FACEBOOK')
           {
              $facebook = array(
                "enabled" => $config->getEnabled(),
                "filename" => $config->getfilename(),
                "linkurl" => $config->getlinkurl(),
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'TWITTER')
           {
              $twitter = array(
                "enabled" => $config->getEnabled(),
                "filename" => $config->getfilename(),
                "linkurl" => $config->getlinkurl(),
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'PINTEREST')
           {
              $pinterest = array(
                "enabled" => $config->getEnabled(),
                "filename" => $config->getfilename(),
                "linkurl" => $config->getlinkurl(),
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'GOOGLEPLUS')
           {
              $googleplus = array(
                "enabled" => $config->getEnabled(),
                "filename" => $config->getfilename(),
                "linkurl" => $config->getlinkurl(),
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'LINKEDIN')
           {
              $linkedin = array(
                "enabled" => $config->getEnabled(),
                "filename" => $config->getfilename(),
                "linkurl" => $config->getlinkurl(),
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'INTERESTINGLINK1')
           {
              $interestinglink1 = array(
                "enabled" => $config->getEnabled(),
                "filename" => $config->getfilename(),
                "linkurl" => $config->getlinkurl(), 
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }
           elseif($config->getParameter() == 'INTERESTINGLINK2')
           {
              $interestinglink2 = array(
                "enabled" => $config->getEnabled(),             
                "linkurl" => $config->getlinkurl(),
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }

           elseif($config->getParameter() == 'INTERESTINGLINK3') 
           {
              $interestinglink3 = array(
                "enabled" => $config->getEnabled(),              
                "linkurl" => $config->getlinkurl(),
                "value" => $config->getValue(),
                "id" => $config->getId()
              );
           }

           elseif($config->getParameter() == 'COMPANYLOGO')
           {
              $companylogo = array(                             
                "filename" => $config->getfilename(),               
                "id" => $config->getId()
              );
           }

           else
           {
              $companydetail = array(
                "value" => $config->getValue(),  
                "id" => $config->getId()
              );
           }
           
        }        
        return $this->render('configuration/display.html.twig', array('header' => "Configuration Setup", 
                                                                'maxWidth'=>$configuration->maxWidth,
                                                                'maxHeight'=>$configuration->maxHeight,
                                                                'minWidth'=>$configuration->minWidth,
                                                                'minHeight'=>$configuration->minHeight,
                                                                'maxSize'=>$configuration->maxSize,
                                                                'companyname' => $companyname,
                                                                'country' => $country,
                                                                'state' => $state,
                                                                'province' => $province,
                                                                'address' => $address,
                                                                'contactno' => $contactno,
                                                                'email' => $email,
                                                                'facebook' => $facebook,
                                                                'twitter' => $twitter,
                                                                'pinterest' => $pinterest,
                                                                'googleplus' => $googleplus, 
                                                                'linkedin' => $linkedin,
                                                                'mission' => $mission,
                                                                'vision' => $vision,
                                                                'companylogo' => $companylogo, 
                                                                'companydetail' => $companydetail,
                                                                'interestinglink1' => $interestinglink1,
                                                                'interestinglink2' => $interestinglink2,
                                                                'interestinglink3' => $interestinglink3,
                                                                'thumblocation'=>$configuration->getThumbUploadDir())); 
     }     
     
     /**
      * @Route("/admin/configuration/hide/{id}")
      */
     public function hide($id, Request $request) {
        
        $configuration = new Configuration();
        try
        {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE configuration SET enabled = 0 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }
        return $this->redirect($configuration->url, 301);                 
     }

    /**
      * @Route("/admin/configuration/show/{id}")
      */
     public function show($id, Request $request) {
        $configuration = new Configuration(); 
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("UPDATE configuration SET enabled = 1 WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
        } catch(Exception $e) {
          $this->addFlash('error', 'Your changes were not saved!');
        }        
        return $this->redirect($configuration->url, 301);                 
     }

    /**
      * @Route("/admin/changeimage/configuration/{id}")      
      */
     public function changeImage($id,Request $request) {           
        try {
          $em = $this->getDoctrine()->getEntityManager();
          $connection = $em->getConnection();
          $statement = $connection->prepare("SELECT filename FROM configuration WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->execute();
          $results = $statement->fetch();

          $old_filename = $results['filename'];
          $configuration = new Configuration(); 
          $configuration->setfilename($old_filename);
          
          $configuration->removeUpload(); 
          $configuration->removeThumbs();   

          $uploadedFile = $request->files->get('upfile');  
          $configuration->setFile($uploadedFile); 
          $configuration->upload();
          $configuration->createThumbnail(); 

          $statement = $connection->prepare("UPDATE configuration SET filename = :filename WHERE id = :id");
          $statement->bindValue('id', $id);
          $statement->bindValue('filename', $configuration->getfilename());
          $statement->execute();
          $this->addFlash('success', 'Your changes were saved!');
          return $this->redirect($configuration->url, 301);   
        }        
        catch(Exception $e)
        {
          $this->addFlash('error', 'Your changes were not saved!');
        }      
     }
}