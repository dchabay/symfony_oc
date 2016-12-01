<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
  public function indexAction($page)
  {
    if ($page < 1) {
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
    }

    // Notre liste d'annonce en dur
    $em = $this->getDoctrine()->getManager();
    
    // La méthode findAll retourne toutes les catégories de la base de données
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findAll();
/*
    // On boucle sur les catégories pour les lier à l'annonce
    for ($listAdverts as $advert) {
      $advert->addCategory($category);
    }

      $listAdverts = array(
      $em->getRepository('OCPlatformBundle:Advert')->find(14),
      $em->getRepository('OCPlatformBundle:Advert')->find(12),
      $em->getRepository('OCPlatformBundle:Advert')->find(13)
    );*/

    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
    ));
  }

  public function viewAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em
      ->getRepository('OCPlatformBundle:Advert')
      ->find($id)
    ;

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On avait déjà récupéré la liste des candidatures
    $listApplications = $em
      ->getRepository('OCPlatformBundle:Application')
      ->findBy(array('advert' => $advert))
    ;

    // On récupère maintenant la liste des AdvertSkill
    $listAdvertSkills = $em
      ->getRepository('OCPlatformBundle:AdvertSkill')
      ->findBy(array('advert' => $advert))
    ;

    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert'           => $advert,
      'listApplications' => $listApplications,
      'listAdvertSkills' => $listAdvertSkills
    ));
  }
    
  public function addAction(Request $request)
  {

    // On récupère l'EntityManager
    $em = $this->getDoctrine()->getManager();

    // Création de l'entité Advert
    $advert = new Advert();
    $lipsum = substr(simplexml_load_file('http://www.lipsum.com/feed/xml?amount=1&what=paras&start=2')->lipsum, 0, 3);
    $advert->setTitle('Recherche développeur ' . $lipsum . '.');
    $advert->setAuthor('Alexandre ' . substr(md5(rand()), 0, 3));
    $advert->setContent(substr(md5(rand()), 0, 5) . " Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…");

    // On récupère toutes les compétences possibles
    $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();

    // Pour chaque compétence
    foreach ($listSkills as $skill) {
      // On crée une nouvelle « relation entre 1 annonce et 1 compétence »
      $advertSkill = new AdvertSkill();

      // On la lie à l'annonce, qui est ici toujours la même
      $advertSkill->setAdvert($advert);
      // On la lie à la compétence, qui change ici dans la boucle foreach
      $advertSkill->setSkill($skill);

      // Arbitrairement, on dit que chaque compétence est requise au niveau 'Expert'
      $advertSkill->setLevel('Expert');

      // Et bien sûr, on persiste cette entité de relation, propriétaire des deux autres relations
      $em->persist($advertSkill);
    }
    
    // Création de l'entité Image
    $image = new Image();
    $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
    $image->setAlt('Job de rêve');


    // Création d'une première candidature
    $application1 = new Application();
    $application1->setAuthor('Jeanne ' . substr(md5(rand()), 0, 3) );
    $application1->setContent("J'ai toutes les qualités requises. " . substr(md5(rand()), 0, 3));

    // On lie les candidatures à l'annonce
    $application1->setAdvert($advert);

      
    // On lie l'image à l'annonce
    $advert->setImage($image);

      
    // Doctrine ne connait pas encore l'entité $advert. Si vous n'avez pas défini la relation AdvertSkill
    // avec un cascade persist (ce qui est le cas si vous avez utilisé mon code), alors on doit persister $advert
    $em->persist($advert);


    $em->persist($application1);
      
      
    // Étape 2 : On « flush » tout ce qui a été persisté avant
    $em->flush();

    // Reste de la méthode qu'on avait déjà écrit
    if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      // Puis on redirige vers la page de visualisation de cettte annonce
      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }

    // Si on n'est pas en POST, alors on affiche le formulaire
    return $this->render('OCPlatformBundle:Advert:add.html.twig');
  }
  
  
  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // La méthode findAll retourne toutes les catégories de la base de données
    $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

    // On boucle sur les catégories pour les lier à l'annonce
    foreach ($listCategories as $category) {
      $advert->addCategory($category);
    }

    // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
    // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

    // Étape 2 : On déclenche l'enregistrement
    $em->flush();      
      
      
    if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

      return $this->redirectToRoute('oc_platform_view', array('id' => 5));
    }

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert
    ));
  }

  public function deleteAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On boucle sur les catégories de l'annonce pour les supprimer
    foreach ($advert->getCategories() as $category) {
      $advert->removeCategory($category);
    }

    // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
    // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

    // On déclenche la modification
    $em->flush();
      
    return $this->render('OCPlatformBundle:Advert:delete.html.twig');
  }

  public function menuAction($limit)
  {
    // On fixe en dur une liste ici, bien entendu par la suite on la récupérera depuis la BDD !
      
    //$em = $this->getDoctrine()->getManager();
    //$advert1 = $em->getRepository('OCPlatformBundle:Advert')->find(1);
    //$advert2 = $em->getRepository('OCPlatformBundle:Advert')->find(2);
    //$advert3 = $em->getRepository('OCPlatformBundle:Advert')->find(3);
    $listAdverts = array(
      array('id' => 2, 'title' => 'Recherche développeur Symfony'),
      array('id' => 5, 'title' => 'Mission de webmaster'),
      array('id' => 9, 'title' => 'Offre de stage webdesigner')
    );

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      // Tout l'intérêt est ici : le contrôleur passe les variables nécessaires au template !
      'listAdverts' => $listAdverts
    ));
  }
    public function listAction()
    {
      $listAdverts = $this
        ->getDoctrine()
        ->getManager()
        ->getRepository('OCPlatformBundle:Advert')
        ->getAdvertWithApplications()
      ;

      foreach ($listAdverts as $advert) {
        // Ne déclenche pas de requête : les candidatures sont déjà chargées !
        // Vous pourriez faire une boucle dessus pour les afficher toutes
        $advert->getApplications();
      }
    }
   
    public function categVipAction()
    {
      $listAdverts = $this
        ->getDoctrine()
        ->getManager()
        ->getRepository('OCPlatformBundle:Advert')
        ->getAdvertWithCategories(array('Développement web', 'Intégration'));
      ;

      foreach ($listAdverts as $advert) {
        // Ne déclenche pas de requête : les candidatures sont déjà chargées !
        // Vous pourriez faire une boucle dessus pour les afficher toutes
        $advert->getCategories();
      }
      

    return $this->render('OCPlatformBundle:Advert:categVip.html.twig', array(
      'listAdverts' => $listAdverts
    ));
      
    }
   
    public function lastApplicationsAction()
    {
      $listAdverts = $this
        ->getDoctrine()
        ->getManager()
        ->getRepository('OCPlatformBundle:Application')
        ->getApplicationsWithAdvert(10);
      ;

//      foreach ($listAdverts as $advert) {
//        // Ne déclenche pas de requête : les candidatures sont déjà chargées !
//        // Vous pourriez faire une boucle dessus pour les afficher toutes
//        $advert->getCategories();
//      }
      

    return $this->render('OCPlatformBundle:Advert:lastApplications.html.twig', array(
      'listAdverts' => $listAdverts
    ));
      
    }
}
