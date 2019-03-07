<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

use Symfony\Component\Dotenv\Dotenv;

class AutoDiscoverController extends AbstractController
{

  private function getData(Request $request, $type, $preset = null) {
    if ($preset !== null && file_exists($presetEnv = "{$this->getParameter('kernel.project_dir')}/.env.{$preset}.local")) {
      $dotenv = new Dotenv();

      $dotenv->overload($presetEnv);
    }

    $app = $this->getParameter('app');

    $app['template'] = 'undefined.html.twig';

    $app['domain'] = $request->server->get('HTTP_HOST');
    $app['email'] = 'user@example.org';

    switch($type) {
    case 'ios':
      $app['template'] = 'ios.xml.twig';

      $app['uuid'] = strtoupper(Uuid::uuid5(Uuid::NAMESPACE_URL, $request));

      if ($request->query->has('email')) {
        $app['email'] = $email = $request->query->get('email');

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $email = explode('@', $email);
          $app['domain'] = array_pop($email);
        }
      }

      break;

    case 'outlook':
      $app['template'] = 'outlook.xml.twig';

      break;

    case 'thunderbird':
      if ($request->query->has('emailaddress')) {
        $app['email'] = $email = $request->query->get('emailaddress');

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $email = explode('@', $email);
          $app['domain'] = array_pop($email);
        }
      }

      $app['template'] = 'thunderbird.xml.twig';

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

    return $app;
  }

  public function generate(Request $request, $type, $preset)
  {
    $app = $this->getData($request, $type, $preset);

    $response = new Response();
    $response->headers->set('Content-Type', 'text/xml');

    return $this->render("autodiscover/{$app['template']}", [
      'config' => $app,
    ], $response);
  }
}
