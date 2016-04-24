<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use AppBundle\Entity\Job;
use AppBundle\Entity\Connection;

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
            ->addArgument(
                'days',
                InputArgument::OPTIONAL,
                'Number of days ago',
                Connection::FOLLOW_UP_14_DAYS
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days       = $input->getArgument('days');
        $jobManager = $this->getContainer()->get('job_manager');
        $jobType    = ($days == Connection::FOLLOW_UP_14_DAYS? Job::CONNECTION_MAIL_FOLLOW_UP_14: Job::CONNECTION_MAIL_FOLLOW_UP_42);

        if ($jobManager->isRunning($jobType)) {
            $output->writeln('Connection follow-up mail job is still running.');

            return;
        }

        gc_enable();

        $jobManager->start($jobType);
        $output->writeln('Connection follow-up mail job has started.');

        $ctr                = 0;
        $numSent            = ($days == Connection::FOLLOW_UP_14_DAYS? 0: 1);
        $templating         = $this->getContainer()->get('templating');
        $connectionManager  = $this->getContainer()->get('connection_manager');
        $userMailer         = $this->getContainer()->get('app.user_mailer');

        try {
            while ((($connection = $connectionManager->getFindCreatedDaysAgo($days, $numSent)) instanceof Connection)) {
                $output->writeln(sprintf('Processing connection #%s', $connection->getId()));

                $learner        = $connection->getLearner();
                $fluentFpeaker  = $connection->getFluentSpeaker();

                $output->writeln(sprintf('Sending follow-up mail to learner #%s', $learner->getId()));
                $userMailer->sendConnectionFollowUpEmailMessage($learner, $days);

                $output->writeln(sprintf('Sending follow-up mail to fluent speaker #%s', $fluentFpeaker->getId()));
                $userMailer->sendConnectionFollowUpEmailMessage($fluentFpeaker, $days);

                $connection->setNumSent(($connection->getNumSent()+1));
                $connectionManager->save($connection);
                $output->writeln(sprintf('Marked connection #%s', $connection->getId()));

                if ($ctr%20 == 0) {
                    unset($learner);
                    unset($fluentFpeaker);
                    unset($connection);

                    gc_collect_cycles();
                }

                $ctr++;
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        $jobManager->finish($jobType);
        $output->writeln(sprintf('%d connection were being processed.', $ctr));
        $output->writeln('End connection follow-up mail job.');

        unset($jobManager);
        unset($ctr);
        unset($days);
        unset($numSent);
        unset($templating);
        unset($connectionManager);
        unset($userMailer);

        gc_collect_cycles();
    }
}