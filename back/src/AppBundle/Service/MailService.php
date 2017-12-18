<?php
/**
 * Created by PhpStorm.
 * User: SOSF - Serveur 1
 * Date: 18/10/2017
 * Time: 10:12
 */

namespace AppBundle\Service;


use AppBundle\Entity\User;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MailService
{

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function send(User $user, string $view, string $subject, array $content)
    {
        $from = $this->get('mailer_alias') ? $this->get('mailer_alias') : $this->get('mailer_user');

        /** @var TwigEngine templating */
        $twig = $this->container->get('templating');

        $message = (new \Swift_Message($subject))
            ->setFrom($from)
            ->setTo($user->getEmail())
            ->setBody(
                $twig->render($view, $content),
                'text/html'
            );

        $mailer = $this->container->get('mailer');

        if ($this->fromLocal()) {
            $mailer = \Swift_Mailer::newInstance(\Swift_SendmailTransport::newInstance($this->container->getParameter('mailer_path')));
        }

        $mailer->send($message);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start /B cmd /C "php ../bin/console swiftmailer:spool:send >NUL 2>NUL"', 'r'));
        } else {
            exec('bash -c "exec nohup setsid php ../bin/console swiftmailer:spool:send > /dev/null 2>&1 &"');
        }
    }

    private function get(string $key)
    {
        return $this->container->getParameter($key);
    }

    private function fromLocal()
    {
        $mailerHost = $this->container->getParameter('mailer_host');
        return in_array($mailerHost, ['localhost', '127.0.0.1']);
    }

    public function sendErrorMail(User $user, string $error, string $ip)
    {
        /** @var TwigEngine templating */
        $twig = $this->container->get('templating');

        $message = (new \Swift_Message('Une erreur est survenue sur le front'))
            ->setFrom('peeramid.error@sos-futur.fr')
            ->setTo(['nicolas.meyer@sos-futur.fr', 'martin.thiriau@sos-futur.fr'])
            ->setBody(
                $twig->render('Emails/front_error.html.twig', [
                    'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                    'username' => $user->getUsername(),
                    'ip' => $ip,
                    'date' => (new \DateTime())->format('d/m/Y H:i:s'),
                    'error' => $error
                ]),
                'text/html'
            );

        $mailer = $this->container->get('mailer');

        if ($this->fromLocal()) {
            $mailer = \Swift_Mailer::newInstance(\Swift_SendmailTransport::newInstance($this->container->getParameter('mailer_path')));
        }

        $mailer->send($message);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start /B cmd /C "php ../bin/console swiftmailer:spool:send >NUL 2>NUL"', 'r'));
        } else {
            exec('bash -c "exec nohup setsid php ../bin/console swiftmailer:spool:send > /dev/null 2>&1 &"');
        }
    }
}