<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 30/10/2017
 * Time: 17:30
 */

namespace AppBundle\Service;


use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start /B cmd /C "php ../bin/console swiftmailer:spool:send >NUL 2>NUL"', 'r'));
        } else {
            exec('bash -c "exec nohup setsid php ../bin/console swiftmailer:spool:send > /dev/null 2>&1 &"');
        }
    }
}