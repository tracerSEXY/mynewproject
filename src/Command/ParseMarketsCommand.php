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

class ParseMarketsCommand extends Command
{
    protected static $defaultName = 'ParseMarkets';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Parse markets')
        ;
    }

    protected function parseMarkets()
    {
        $em = $this->container->get('doctrine')->getManager();
        $text = file_get_contents('https://www.groupon.co.uk/discount-codes/all-brands/a');
        $regexpMarket = '/<li class="browse-merchant-item"><a (.*?)<\\/a><\\/li>/';
        $regexpMarketTitle = '/class="browse-merchant-link">(.*?)<\\/a>/';
        $regexpMarketLink = '/href="(.*?)"/';
        preg_match_all($regexpMarket , $text , $markets);
        foreach ($markets[0] as $market)
        {
            preg_match($regexpMarketTitle, $market, $marketTitle);
            preg_match($regexpMarketLink, $market, $marketLink);
            if(!empty($marketTitle))
            {
                $newMarket = new Market();
                $newMarket->setTitle($marketTitle[1]);
                $newMarket->setLink($marketLink[1]);

                $marketObject = $this->container->get('doctrine')->getRepository(Market::class)->findOneBy(['title' => $marketTitle[1]]);
                if (!$marketObject)
                {
                    $em->persist($newMarket);
                }
            }
        }
        $em->flush();
    }

    protected function removeAllMarkets()
    {
        $em = $this->container->get('doctrine')->getManager();
        $markets = $this->container->get('doctrine')->getRepository(Market::class)->findAll();
        foreach ($markets as $market) {
            $em->remove($market);
        }
        $em->flush();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->parseMarkets();
        
        $io->success('Done.');
    }
}
