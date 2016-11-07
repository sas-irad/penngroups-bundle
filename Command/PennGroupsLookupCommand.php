<?php

namespace SAS\IRAD\PennGroupsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use SAS\IRAD\GoogleAdminClientBundle\Service\PersonInfo;

class PennGroupsLookupCommand extends ContainerAwareCommand {
    
    protected function configure() {
        
        $this
            ->setName('penn-groups:lookup')
            ->setDescription('Lookup a Penn Groups subject by Penn ID, Pennkey or subject id')
            ->addArgument('input', InputArgument::REQUIRED, "The user's Penn ID or Pennkey")
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $input   = strtolower($input->getArgument('input'));
        $service = $this->getContainer()->get('penngroups.web_service_query');
        
        if ( preg_match("/^\d{8}$/", $input) ) {
            $result = $service->findByPennID($input);
            
        } elseif ( preg_match("/^[a-z][0-9a-z]{1,15}$/", $input) ) {
            $result = $service->findByPennkey($input);

        } else {
            $result = $service->getSubject($input);
        }

        if ( !$result ) {
            throw new \Exception("No matches for subject id: $input");
        }
        
        print_r($result);

    }
   
}