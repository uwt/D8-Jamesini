<?php

namespace Drupal\my_module\Controller;

use Drupal\node\Entity\Node;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;
use Drupal\taxonomy\Entity;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;

/**
 * TODO: class docs.
 */
class MymodulecreatenodeController {

  /**
   * Callback for the my_module.mymodulecreatenode route.
   */
  public function content() {
    // Output a bit of regular content

    $bobo = entity_get_bundles();

    $feed = \Drupal::entityTypeManager()->getStorage('feeds_feed')->load(3);
    $markup = $feed->get('field_section_name')->getString();
    $build = [
      '#markup' => t('Hey there Bobo!') . $markup,
    ];
    return $build;
}

  


  public static function getDummyImage() {
    $content = "";
    
    // Add a random float for variety  
    $float_vals = ['left', 'right'];
    $float_choice = array_rand($float_vals, 1);
    $float = $float_vals[$float_choice];

    $size_vals = ['300x200','600x400', '450x300'];
    $size_choice = array_rand($size_vals, 1);
    $size = $size_vals[$size_choice];

    $color_vals = ['4b2e83/fff', 'b7a57a/000', '85754d/fff', 'd9d9d9/000', '444444/fff'];
    $color_choice = array_rand($color_vals, 1);
    $color = $color_vals[$color_choice];

    $tenets = [];
    $tenets[] = "Undaunted";
    $tenets[] = "We > Me";
    $tenets[] = "Dare to Do";
    $tenets[] = "Be the First";
    $tenets[] = "Question the Answer";
    $tenets[] = "Passion Never Rests";
    $tenets[] = "Be A World of Good";
    $tenets[] = "Together We Will";
    
    $text = $tenets[array_rand($tenets)];
    
    $url = 'https://dummyimage.com/';
    $url .= $size . '/';
    $url .= $color;
    $url .= '.jpg&text=';
    $url .= $text;


    $content = '<img src="'.$url.'" alt="'.$text.'" data-align="'.$float.'" />';
    
    return $content;
  }


  public static function getDummyText(string $type = 'html'){
    $content = "";
    $options = [];
    $client = \Drupal::httpClient();



    switch($type){
    case 'html':
      $uri = 'https://loripsum.net/api/';
      $uri_params = rand(2,4);
      $uri_params .= '/medium/headers/decorate/bq/ul';
      break;

    case 'title':
      $uri = 'https://loripsum.net/api/';
      $uri_params = '1/medium/plaintext';
      break;

    }

    $uri_full = $uri . $uri_params;
    $method = 'GET';

    $response = $client->request($method, $uri_full, $options);

    $code = $response->getStatusCode();
    if($code == 200){
      $content = $response->getBody()->getContents();
    }else{
    }

    return $content;
  }

  public static function getDummyPageContent() {
  
      $body_value  = "";
      $body_value .= MymodulecreatenodeController::getDummyImage();
      $body_value .= MymodulecreatenodeController::getDummyText();
      $body_value .= MymodulecreatenodeController::getDummyImage();
      $body_value .= MymodulecreatenodeController::getDummyText();
      return $body_value;
  }


}
