<?php

namespace Drupal\writemap_import\Controller;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * TODO: class docs.
 */
class WritemapImportCreateNodeController {

  /**
   * Callback for the writemap_import.writemapimportcreatenode  route.
   */
  public function content() {
    // Output a bit of regular content

    try {
      $feed = \Drupal::entityTypeManager()->getStorage('feeds_feed')->load(3);
      $markup = $feed->get('field_section_name')->getString();
      $build = [
        '#markup' => t('Hey there Bobo!') . $markup,
      ];

      return $build;

    } catch (InvalidPluginDefinitionException $e) {
      return $e->getMessage();
    } catch (PluginNotFoundException $e) {
      return $e->getMessage();
    }

  }


  /**
   * @return Imgage
   */
  public static function getDummyImage() {

    // Define a list of images from which we randomly select one.
    $images = [
      'https://farm5.staticflickr.com/4901/32222870818_1b668b0764_c.jpg',
      'https://farm5.staticflickr.com/4869/32222872398_df838afa3e_c.jpg',
      'https://farm5.staticflickr.com/4882/31155029247_ee740bc0ef_c.jpg',
      'https://farm5.staticflickr.com/4828/31155024077_e10e684e45_c.jpg',
    ];

    // Choose the image
    $chosen_image_key = array_rand($images, 1);
    $chosen_image = $images[$chosen_image_key];
    $image_destination = 'public://' . substr($chosen_image, -28);
    // Get the image from Flikr
    $data = file_get_contents($chosen_image);
    $node_para_image = file_save_data($data, $image_destination, FILE_EXISTS_REPLACE);
    return $node_para_image;
  }

  public static function getDummyText(string $type = 'html') {
    $content = "";
    $options = [];
    $client = \Drupal::httpClient();
    $uri = "";
    $uri_params = "";


    switch ($type) {
      case 'html':
        $uri = 'https://loripsum.net/api/';
        $uri_params = rand(2, 4);
        $uri_params .= '/medium/headers/decorate/bq/ul';
        break;

      case 'title':
        $uri = 'https://loripsum.net/api/';
        $uri_params = '1/medium/plaintext';
        break;
    }

    $uri_full = $uri . $uri_params;
    $method = 'GET';

    try {
      $response = $client->request($method, $uri_full, $options);
      $code = $response->getStatusCode();
      if ($code == 200) {
        $content = $response->getBody()->getContents();
      }

      return $content;
    } catch (GuzzleException $e) {
      return $e->getMessage();
    }
  }

  public static function getDummyPageContent($node) {

    // Define list of paragraph types
    $para_types = ['text', 'text_image', 'image_text'];
    // Randomly select a paragraph type for this page
    $para_type_chosen = array_rand($para_types);
    $para_type = $para_types[$para_type_chosen];


    // Create a new paragraph of the selected type
    switch ($para_type) {
      case "text":

        $node_para_text = WritemapImportCreateNodeController::getDummyText();
        $node_para = Paragraph::create([
          'type' => $para_type,
          'field_text' => [
            'value' => $node_para_text,
            'format' => 'full_html',
          ],
        ]);
        break;
      case "text_image":
      case "image_text":
        // Get some dummy text
        $node_para_text = WritemapImportCreateNodeController::getDummyText();
        // Get an image from UWT's Flickr
        $node_para_image = WritemapImportCreateNodeController::getDummyImage();

        $node_para = Paragraph::create([
          'type' => $para_type,
          'field_text' => [
            'value' => $node_para_text,
            'format' => 'full_html',
          ],
          'field_image' => [
            'target_id' => $node_para_image->id(),
            'alt' => 'Arbitrary Alt Text...this is just dummy content after all.',
          ],
        ]);
        break;
    }


    $node_para->isNew();
    $node_para->save();
    // We want to 'append' any newly generated paragraphs to the existing
    // set of paragraphs on the node.
    $node_paragraphs = $node->get('field_page_component')->getValue();
    $node_paragraphs[] = [
      'target_id' => $node_para->id(),
      'target_revision_id' => $node_para->getRevisionId(),
    ];
    return $node_paragraphs;
  }


} // /WritemapImportCreateNodeController
