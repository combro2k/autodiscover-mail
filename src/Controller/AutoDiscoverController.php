<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AutoDiscoverController extends AbstractController
{
  public function generate($type)
  {
    $app = $this->getParameter('app');

    $template = 'undefined.html.twig';

    switch($type) {
      case 'outlook':
        $template = 'outlook.xml.twig';

        break;

      case 'thunderbird':
        $template = 'thunderbird.xml.twig';

        $app['imap']['spa'] = $app['imap']['spa'] === 'off' ? 'password-cleartext' : 'password-encrypted';
        $app['pop3']['spa'] = $app['pop3']['spa'] === 'off' ? 'password-cleartext' : 'password-encrypted';
        $app['smtp']['spa'] = $app['smtp']['spa'] === 'off' ? 'password-cleartext' : 'password-encrypted';


        break;
    }


    return $this->render("autodiscover/{$template}", [
      'config' => $app,
    ]);
  }
}
