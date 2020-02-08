<?php

namespace Drupal\johns_module\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Class CountryRedirectionSubscriber
 *
 *
 * @package Drupal\johns_module\EventSubscriber
 */
class CountryRedirectionSubscriber implements EventSubscriberInterface {
  
  public function languageRedirection(GetResponseEvent $event) {
  
    global $base_url;
    $ip = $event->getRequest()->getClientIp();

    if(filter_var($ip, FILTER_VALIDATE_IP) && ($ip != '::1' || $ip != '127.0.0.1')) {
      // Use JSON encoded string and converts 
      // it into a PHP variable 
      $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
      $lc = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if($ipdat->geoplugin_countryCode == 'IN' && $lc != 'hi') {
        $response = new TrustedRedirectResponse($base_url . '/hi');
        $response->addCacheableDependency((new CacheableMetadata())->setCacheMaxAge(0));
        $event->setResponse($response);
      }
      if($ipdat->geoplugin_countryCode == 'PK'  && $lc != 'ur' ) {
        $response = new TrustedRedirectResponse($base_url . '/ur');
        $response->addCacheableDependency((new CacheableMetadata())->setCacheMaxAge(0));
        $event->setResponse($response);
      }
      // other countries
      if (!in_array($ipdat->geoplugin_countryCode, ['IN', 'PK']) && $lc != 'en') {
        $response = new TrustedRedirectResponse($base_url . '/en');
        $response->addCacheableDependency((new CacheableMetadata())->setCacheMaxAge(0));
        $event->setResponse($response);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // 'checkForRedirection' is the name of our function
    $events[KernelEvents::REQUEST][] = array('languageRedirection');
    return $events;
  }
}
