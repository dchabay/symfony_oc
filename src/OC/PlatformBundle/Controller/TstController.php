<?php

// src/OC/PlatformBundle/Controller/TstController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TstController extends Controller
{
  // On modifie viewAction, car elle existe déjà
  public function viewAction($id, Request $request)
  {
    return $this->redirectToRoute('oc_platform_home');
  }
}