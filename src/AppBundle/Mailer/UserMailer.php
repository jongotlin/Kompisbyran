<?php

namespace AppBundle\Mailer;

use AppBundle\Entity\User;
use AppBundle\Enum\FriendTypes;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class UserMailer
 * @package AppBundle\Mailer
 */
class UserMailer extends Mailer
{
    protected $translator;

    /**
     * @param \Swift_Mailer $mailer
     * @param RouterInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     */
    public function __construct(\Swift_Mailer $mailer, RouterInterface $router, EngineInterface $templating, TranslatorInterface $translator)
    {
        $this->translator = $translator;

        parent::__construct($mailer, $router, $templating);
    }

    /**
     * @param User $user
     */
    public function sendRegistrationWelcomeEmailMessage(User $user)
    {
        $subject    = 'user.welcome.message.subject';
        $htmlBody   = 'user.welcome.message.body';

        if ($user->getType() == FriendTypes::MUSIC) {
            $subject    = 'user.friend.welcome.message.subject';
            $htmlBody   = 'user.friend.welcome.message.body';
        }

        $subject    = $this->translator->trans($subject);
        $htmlBody   = $this->translator->trans($htmlBody);

        $html       = $this->templating->render('email/welcome.html.twig', [
            'body'  =>  $htmlBody
        ]);

        $this->sendEmailMessage($html, null, $subject, $user->getEmail());
    }

    /**
     * @param User $user
     * @param User $matchUser
     * @param $body
     * @param $fromEmail
     */
    public function sendMatchEmailMessage(User $user, User $matchUser, $body, $fromEmail)
    {
        $subject    = $this->translator->trans('match.email.user.subject', [
            '%match_music_friend%'    => ($matchUser->getType() == FriendTypes::MUSIC ?  $this->translator->trans('global.music_buddy'):  $this->translator->trans('global.fika_buddy')),
            '%user_name%'             => $user->getFullName()
        ]);

        $this->sendEmailMessage(null, $body, $subject, $user->getEmail(), $fromEmail, $fromEmail);
    }
}
