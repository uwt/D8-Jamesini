<?php

namespace Drupal\my_module\Controller;

use Drupal\node\Entity\Node;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;
use Drupal\taxonomy\Entity;
use Drupal\taxonomy\Entity\Term;
define("SECTION", 'sections');

/**
 * TODO: class docs.
 */
class MymodulecreatenodeController {

  /**
   * Callback for the my_module.mymodulecreatenode route.
   */
  public function content() {


    $data = [];
    $data[] = ['section' => 'My Section', 'menuid' => 'my_menu', 'menulabel' => 'My Menu', 'title' => 'My Title','body' => 'My Body'];
    $data[] = ['section' => 'My Other Section', 'menuid'=>'my_other_menu', 'menulabel' => 'My Other Menu', 'title' => 'My Other Title','body' => 'My Other Body'];

    ///// 
    // Get the current Sections
    $vid = 'sections';
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);

    $allSections = [];

    foreach($terms as $term){
      $allSections[] = $term->name;
    }


    //dpm($allSections, '$allSections before foreach()');

    /////
    // Determine if this run should: 
    // overwrite existing content - OR - create new content
    // (content meaning section, menu, and nodes)

    foreach($data as $d){
      //dpm($d['section'], '$section');
      $sec = $d['section'];
      if(in_array($sec, $allSections)){
        //dpm('Do NOT create section: ' . $sec);
      }else{
        //dpm('Create section: ' . $d['section']);

        // Create terms
        $term = Term::create([
          'name' => $sec,
          'vid' => 'sections',
        ]);
        $term->save();      

      //dpm('about to create menu...');
      $menu = Menu::load($d['menuid']);
      //dpm($menu, '$menu');

      // Create a menu
      ///*
      Menu::create([
        'id' => $d['menuid'],
        'label' => $d['menulabel'],
        'description' => 'Description for: ' . $d['menulabel'],
      ])->save();
      //*/


        $allSections[] = $sec;
      }
    }
    //dpm($allSections, '$allSections after foreach()');

    // If needed, delete the existing section, menu, and nodes
    
    // Create the section, menu, and nodes
    
      



    // Delete existing nodes and menus
    MymodulecreatenodeController::deleteStuff($data);

    // Make the nodes
    MymodulecreatenodeController::makeNode($data);

    // Output a bit of regular content
    $build = [
      '#markup' => t('Hey there Bobo!'),
    ];

    return $build;
  }

  

  /**
   * Deletes an existing section term, menu, and associated nodes.
   * Param $data Array
   *  section => String vid of the section to delete
   *  menu => String menu_id of the menu to delete
   *  nodes => Array of nids to delete
   *
   * Return nothing
   */
  public function deleteStuff(Array $data){
    //dpm($data, '$data in deleteStuff()');

    // Delete Menus

    // Delete Nodes

  }

  public function makeNode(Array $data){
    //dpm($data, '$data in makeNode');

    foreach($data as $v){
      //dpm($v['title'], 'Node Title: ');

      $node = Node::create(['type' => 'page']);
      $node->set('title', $v['title']);

      // Load the proper term
      $query = \Drupal::entityQuery('taxonomy_term')
        ->condition('vid', 'sections')
        ->condition('name', $v['section']);
      $tids = $query->execute();
      $terms = Term::loadMultiple($tids);
      $tid = 0;
      //dpm($terms, '$terms');

      foreach($terms as $term) {
        //dpm($term->getName(), $term->id());
        $tid = $term->id();
      }

      // Get random body text
      // https://loripsum.net/
      $body_value = MymodulecreatenodeController::getDummyPageContent();

      //dpm($body_value, '$body_value');
      //Body can now be an array with a value and a format.
      //If body field exists.
      //
      $body = [
        'value' => $body_value,
        'format' => 'basic_html',
      ];
      // Set the body field
      $node->set('body', $body);
      // Set the author
      $node->set('uid', 1);
      // Setting a taxonomy term (see 'Load the proper term' above)
      $node->field_section[] = ['target_id' => $tid];
      $node->status = 1;
      $node->enforceIsNew();
      $node->save();
      drupal_set_message( "Node with nid " . $node->id() . " saved!\n");



      //Create a menu link
      MenuLinkContent::create([
        'title' => $v['title'],
        'link' => 'internal:/node/' . $node->id(),
        'menu_name' => $v['menuid'],
      ])->save();



    } // /foreach

  } // /makeNode

  public static function getDummyImage() {
    $content = "";
    
    // Add a random float for variety  
    $float_vals = ['left', 'right'];
    $float_choice = array_rand($float_vals, 1);
    $float = $float_vals[$float_choice];
    //dpm($float, '$float');

    $size_vals = ['300x200','600x400', '450x300'];
    $size_choice = array_rand($size_vals, 1);
    $size = $size_vals[$size_choice];
    //dpm($size, '$size');

    $color_vals = ['4b2e83/fff', 'b7a57a/000', '85754d/fff', 'd9d9d9/000', '444444/fff'];
    $color_choice = array_rand($color_vals, 1);
    $color = $color_vals[$color_choice];
    //dpm($color, '$color');

    $dummy_text = MymodulecreatenodeController::getDummyText('title');
    $textParts = preg_split("/[\.;,][\s]/", $dummy_text, 4);
    $rnd = rand(1,2);
    $text = $textParts[$rnd];
    //dpm($dummy_text, '$dummy_text');
    //dpm($textParts, '$textParts');
    //dpm($text, '$text');
    
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
    //dpm($code, '$code');
    if($code == 200){
      $content = $response->getBody()->getContents();
    }else{
      //dpm('boooooo....no body');
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
