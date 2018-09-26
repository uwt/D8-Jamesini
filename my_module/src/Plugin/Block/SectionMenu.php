<?php
/**
 * @file
 * Contains \Drupal\my_module\Plugin\Block\SectionMenu.
 */

namespace Drupal\my_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
//use Drupal\Core\Link;
//use Drupal\Core\Url;
use Drupal\Core\Cache\Cache; 
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'SectionMenu' block.
 *
 * @Block(
 *   id = "section_menu",
 *   admin_label = @Translation("Sections"),
 *   category = @Translation("my_module")
 * )
 */
class SectionMenu extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    //////////////////////////////////////////////////
    // Loading and displaying the Section Menu.
    // First, we gotta get the node, then we get the section term
    // from the node, then we use the section term to establish
    // the menu name.
    //////////////////////////////////////////////////
    
    // code to get nid
    $node = \Drupal::routeMatch()->getParameter('node');
    if(isset($node)){
      $node->id();  // get current node id (current url node id)
      // Get the section from the loaded node
      $section_id = $node->get('field_section')->getString();

      // Load the term so we can get the label
      // $menu_name is the string value of the term, which exactly
      // matches the menu name.  No coincidence.
      // The 'label' is the same as the 'machine name' (if 'machine name' is even a thing)
        if($section_id !== ""){
      $term = Term::load($section_id);
      $menu_name = $term->label();

      // Load, build, render, and return the menu
      $parameters = \Drupal::menuTree()->getCurrentRouteMenuTreeParameters($menu_name);
      $menu = \Drupal::menuTree()->load($menu_name, $parameters);
      $built = \Drupal::menuTree()->build($menu);
      return $built;
        }else{
            return array();
        }
    }
  }

// @see https://drupal.stackexchange.com/a/199541
  public function getCacheTags() {
    //With this when your node change your block will rebuild
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      //if there is node add its cachetag
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } else {
      //Return default tags instead.
      return parent::getCacheTags();
    }
  }

// @see https://drupal.stackexchange.com/a/199541  
  public function getCacheContexts() {
    //if you depends on \Drupal::routeMatch()
    //you must set context of this block with 'route' context tag.
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}
