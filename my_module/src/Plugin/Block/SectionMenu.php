<?php
/**
 * @file
 * Contains \Drupal\my_module\Plugin\Block\SectionMenu.
 */

namespace Drupal\my_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
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
class SectionMenu extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    //////////////////////////////////////////////////
    // Loading and displayig the Section Menu.
    // First, we gotta get the node, then we get the section term
    // from the node, then we use the section term to establish
    // the menu name.
    //////////////////////////////////////////////////


    // code to get nid

    $node = \Drupal::routeMatch()->getParameter('node');
    $node->id();  // get current node id (current url node id)
    dpm($node->id(), '$node->id()');
    $section_id = $node->get('field_section')->getString();
    dpm($section_id, '$section_id');

    $term = Term::load($section_id);
    $menu_name = $term->label();
    //$menu_name = 'root-4';




    $menu_tree = \Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth(0);

    //Delete comments to have only enabled links
    //$parameters->onlyEnabledLinks();


    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);
    $list = [];

    foreach ($tree as $item) {
      $title = $item->link->getTitle();
      $url = $item->link->getUrlObject();
      $list[] = Link::fromTextAndUrl($title, $url);
    }

    $output['sections'] = array(
      '#theme' => 'item_list',
      '#items' => $list,
    );
    return $output;
  }


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

  public function getCacheContexts() {
    //if you depends on \Drupal::routeMatch()
    //you must set context of this block with 'route' context tag.
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}
