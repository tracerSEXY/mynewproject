<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Market;

class MarketController extends AbstractController
{
    /**
     * @Route("/market/{marketId}", name="market")
     */
    public function index($marketId)
    {
        $market = $this->getDoctrine()->getRepository(Market::class)->find($marketId);
        $coupons = $market->getCoupon();

        return $this->render('market/market.html.twig', [
            'coupons' => $coupons,
            'market' => $market
        ]);
    }
}
