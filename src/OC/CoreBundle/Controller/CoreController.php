<?php

// src/OC/CoreBundle/Controller/CoreController.php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CoreController extends Controller
{
  public function indexAction()
  {

    return $this->render('OCCoreBundle:Core:index.html.twig');
  }

  public function contactAction(Request $request)
  {
    $session = $request->getSession();
  
    // Mais faisons comme si c'était le cas
    $session->getFlashBag()->add('info', 'Message à caractère informatif : formulaire de contact "under construction".');

    // Puis on redirige vers la page de visualisation de cette annonce
    return $this->redirectToRoute('oc_core_homepage');
  }
}
