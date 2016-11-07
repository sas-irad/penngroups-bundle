<?php

namespace SAS\IRAD\PennGroupsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class PennGroupsMembershipsCommand extends ContainerAwareCommand {
    
    protected function configure() {
        
        $this
            ->setName('penn-groups:memberships')
            ->setDescription('List the memberships for a subject')
            ->addArgument('input', InputArgument::REQUIRED, "The subject id of the member")
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $subject_id = $input->getArgument('input');
        $service   = $this->getContainer()->get('penngroups.web_service_query');
        
        $result = $service->getGroups($subject_id);
        
        print_r($result);

    }
   
}