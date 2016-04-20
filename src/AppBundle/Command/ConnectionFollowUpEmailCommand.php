<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class ConnectionFollowUpEmailCommand
 * @package AppBundle\Command
 */
class ConnectionFollowUpEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('kb:connection:followup')
            ->setDescription('Follow up mail after connection has made')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}