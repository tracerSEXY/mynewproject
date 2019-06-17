<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Market;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index()
    {
        $markets = $this->getDoctrine()->getRepository(Market::class)->findAll();

        return $this->render('main/main.html.twig', array(
            'markets' => $markets,
        ));
    }
}
