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

class RemoveMarketsAndCuponsCommand extends Command
{
    protected static $defaultName = 'RemoveMarketsAndCupons';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Remove Markets And Cupons')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $em = $this->container->get('doctrine')->getManager();
        $markets = $this->container->get('doctrine')->getRepository(Market::class)->findAll();
        foreach ($markets as $market) {
            $em->remove($market);
        }
        $coupons = $this->container->get('doctrine')->getRepository(Coupon::class)->findAll();

        foreach ($coupons as $coupon) {
            $em->remove($coupon);
        }
        $em->flush();

        $io->success('Done.');
    }
}
