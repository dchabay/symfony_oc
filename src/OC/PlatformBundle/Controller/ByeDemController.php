<?php

// src/OC/PlatformBundle/Controller/ByeDemController.php

namespace OC\PlatformBundle\Controller;

// N'oubliez pas ce use :
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ByeDemController extends Controller
{
  public function indexAction()
  {
    $content = $this->get('templating')->render('OCPlatformBundle:ByeDem:index.html.twig');
    
    return new Response($content);
  }
}