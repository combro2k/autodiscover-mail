<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AutoDiscoverController extends AbstractController
{
  public function generate(Request $request, $type)
  {
    $app = $this->getParameter('app');

    $template = 'undefined.html.twig';

    $app['domain'] = $request->server->get('HTTP_HOST');
    $app['email'] = 'user@example.org';


    switch($type) {
    case 'ios':
      $template = 'ios.xml.twig';

      if ($request->query->has('email')) {
        $app['email'] = $email = $request->query->get('email');

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $email = explode('@', $email);
          $app['domain'] = array_pop($email);
        }
      }


      break;

    case 'outlook':
      $template = 'outlook.xml.twig';

      break;

    case 'thunderbird':
      if ($request->query->has('emailaddress')) {
        $app['email'] = $email = $request->query->get('emailaddress');

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $email = explode('@', $email);
          $app['domain'] = array_pop($email);
        }
      }

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
