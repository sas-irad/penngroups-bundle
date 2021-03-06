<?php

namespace SAS\IRAD\PennGroupsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class PennGroupsListCommand extends ContainerAwareCommand {
    
    protected function configure() {
        
        $this
            ->setName('penn-groups:list')
            ->setDescription('List the members of a penngroup')
            ->addArgument('input', InputArgument::REQUIRED, "The path of the penngroup")
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter option for penngroup listing (e.g., All, Immediate, NonImmediate)', 'All')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $penngroup = $input->getArgument('input');
        $filter    = $input->getOption('filter');
        $service   = $this->getContainer()->get('penngroups.web_service_query');
        
        $result = $service->getGroupMembers($penngroup, $filter);
        
        print_r($result);

    }
   
}