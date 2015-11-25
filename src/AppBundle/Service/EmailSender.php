<?php

namespace AppBundle\Service;

use AppBundle\Entity\Connection;
use Symfony\Component\Templating\EngineInterface;

class EmailSender
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templating
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * @param Connection $connection
     */
    public function sendConnectionCreatedEmail(Connection $connection)
    {
        $emailData = [
            [
                'recipient' => $connection->getFluentSpeaker(),
                'partner' => $connection->getLearner(),
            ],
            [
                'recipient' => $connection->getLearner(),
                'partner' => $connection->getFluentSpeaker(),
            ],
        ];


        foreach ($emailData as $data) {
            $parameters = [
                'connection' => $connection,
                'recipient' => $data['recipient'],
                'partner' => $data['partner'],
            ];
            $message = \Swift_Message::newInstance()
                ->setSubject('Fikadags')
                ->setFrom('matchning@kompisbyran.se')
                ->setTo($data['recipient']->getEmail(), $data['recipient']->getName())
                ->setBody(
                    $this->templating->render(
                        'email/connectionCreated.html.twig',
                        $parameters
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->templating->render(
                        'email/connectionCreated.txt.twig',
                        $parameters
                    ),
                    'text/plain'
                )
            ;

            $this->mailer->send($message);
        }
    }
}
