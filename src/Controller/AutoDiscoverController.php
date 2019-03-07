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

        foreach (['imap', 'pop3', 'smtp'] as $i) {
          $app[$i]['spa'] = $app[$i]['spa'] === 'off' ? 'password-cleartext' : 'password-encrypted';

          if ($app[$i]['ssl'] === 'on') {
             $app[$i]['encryption'] = 'SSL';
          } else {
            $app[$i]['encryption'] = $app[$i]['encryption'] === 'TLS' ? 'STARTTLS' : 'plain';
          }
        }

        break;
    }

    $response = new Response();
    $response->headers->set('Content-Type', 'text/xml');

    return $this->render("autodiscover/{$template}", [
      'config' => $app,
    ], $response);
  }
}
