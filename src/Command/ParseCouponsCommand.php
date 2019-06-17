<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Entity\Market;
use App\Entity\Coupon;

class ParseCouponsCommand extends Command
{
    protected static $defaultName = 'ParseCoupons';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Parse coupons')
        ;
    }

    protected function parseCoupons()
    {
        $em = $this->container->get('doctrine')->getManager();
        $markets = $this->container->get('doctrine')->getRepository(Market::class)->findAll();
        $regexpCoupon = '/<div class="coupons-list-row"><div data-bhw=(.*?)<div class="coupon-tile-info">/';
        $regexpCouponTitle = '/<p class="coupon-tile-title">(.*?)<\\/p>/';
        $regexpCouponDescription = '/<p class="coupon-tile-description">(.*?)<\\/p>/';
        $regexpCouponExpDate = '/<span>Expires<\\/span><span>(.*?)<\\/span>/';
        $regexpCouponImageLink = '/data-src="(.*?)"/';
        $regexpMarketImageLink = '/<div class="merchant-logo">(.*?)data-original="(.*?)"/';
        foreach ($markets as $market) {
            $text = file_get_contents( 'https://www.groupon.co.uk'. $market->getLink() );
            preg_match_all($regexpCoupon, $text, $coupons);
            preg_match($regexpMarketImageLink, $text, $marketImageLink);
            $market->setImageLink($marketImageLink[2]);
            foreach ($coupons[0] as $coupon)
            {
                preg_match($regexpCouponTitle, $coupon, $couponTitle);
                preg_match($regexpCouponDescription, $coupon, $couponDescription);
                preg_match($regexpCouponExpDate, $coupon, $couponExpDate);
                preg_match($regexpCouponImageLink, $coupon, $couponImageLink);
                if(!empty($couponTitle))
                {
                    $newCoupon = new Coupon();
                    $newCoupon->setTitle($couponTitle[1]);
                    if(!empty($couponDescription))
                    {
                        $newCoupon->setDescription($couponDescription[1]);
                    }               
                    if(!empty($couponExpDate))
                    {
                        $newCoupon->setExpDate($couponExpDate[1]);
                    }
                    if(!empty($couponImageLink))
                    {
                        $newCoupon->setImageLink($couponImageLink[1]);
                    }        
                    
                    $newCoupon->setMarket($market);

                    $couponObject = $this->container->get('doctrine')->getRepository(Coupon::class)->findOneBy(['title' => $couponTitle[1]]);
                    if (!$couponObject)
                    {
                        $em->persist($newCoupon);
                    }
                    $em->persist($newCoupon);
                }
            }
            $em->flush();
        }
    }

    protected function removeAllCoupons()
    {
        $em = $this->container->get('doctrine')->getManager();
        $coupons = $this->container->get('doctrine')->getRepository(Coupon::class)->findAll();
        foreach ($coupons as $coupon) {
            $em->remove($coupon);
        }
        $em->flush();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->parseCoupons();
        
        $io->success('Done.');
    }
}
