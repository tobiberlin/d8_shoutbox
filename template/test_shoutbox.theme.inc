<?php

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;


/**
 * @file
 * Theming functions for test_shoutbox module
 */


/**
 * Preprocessor for shoutbox_answers_list.html.twig
 *
 */
function template_preprocess_shoutbox_answers_list(&$variables) {
  /** @var \Drupal\Core\Render\Renderer $renderer */
  $renderer = \Drupal::service('renderer');
  /** @var \Drupal\test_shoutbox\Entity\Shoutbox $shoutbox */
  $shoutbox = $variables['shoutbox'];

  // set default variables for the twig template
  $variables['heading'] = t('Answers on this shoutbox');
  $variables['intro_text'] = '';
  $variables['answers_form'] = [];
  $variables['answers_list'] = [];

  // attach our shoutbox styling and javascript
  $variables['#attached']['library'][] = 'test_shoutbox/shoutbox';
  $variables['attributes']['class'][] = 'shoutbox-answers-list';

  // message when answering is deactivated
  if (!$shoutbox->isOpenForAnswers()) {
    $variables['heading'] = t('Answers are closed');
    $variables['intro_text'] = t('We are sorry, currently no answers can be added to this shoutbox.');
  }


  // if answering is activated we go on here
  if ($shoutbox->isOpenForAnswers() ) {

    // add the answer form when the user has the permission
    $currentUser = \Drupal::currentUser();
    if ($currentUser->hasPermission('add answer to shoutbox')) {
      $newAnswerEntity = \Drupal::entityTypeManager()->getStorage('shoutbox_answer')->create([
        'entity_type' => 'shoutbox_answer',
      ]);
      $formRenderArray = \Drupal::service('entity.form_builder')->getForm($newAnswerEntity, 'add');
      $variables['answers_form'][] = $formRenderArray;

      //  cache context: permission -> the list differs on the permissions a user have
      $variables['answers_form']['#cache'] = [
        'contexts' => ['user.permissions']
      ];
      // add cache tags from shoutbox.
      $renderer->addCacheableDependency($variables['answers_form'], $shoutbox);
    }


    // list the answers
    $answers = $shoutbox->getAnswers();
    if(empty($answers)) {
      $variables['intro_text'] = t('Be the first one who adds an answer to this shoutbox!');
    }
    else {
      $renderControllerShoutboxAnswers = \Drupal::entityTypeManager()->getViewBuilder(reset($answers)->getEntityTypeId());
      /** @var \Drupal\test_shoutbox\Entity\ShoutboxAnswer $answer */
      foreach ($answers as $answer) {
        if ($answer->access('view')) {
          $variables['answers_list'][] = $renderControllerShoutboxAnswers->view($answer);
          $variables['answers_list']['#cache'] = [
            'tags' => [\Drupal\test_shoutbox\Entity\Shoutbox::getCacheTagAnswerList($shoutbox->id())]
          ];
        }
      }
    }

  }
}


/**
 * Prepares variables for shoutbox template.
 */
function template_preprocess_shoutbox_entity(&$variables) {
  // Helpful $content variable for templates.
  $variables['content'] = [];
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  // attach our shoutbox styling and javascript
  $variables['#attached']['library'][] = 'test_shoutbox/shoutbox';
  $variables['attributes']['class'][] = 'shoutbox';
}



/**
 * Prepares variables for shoutbox answer templates.
 */
function template_preprocess_shoutbox_answer(&$variables) {
  /** @var \Drupal\test_shoutbox\Entity\ShoutboxAnswer $shoutboxAnswer */
  $shoutboxAnswer = $variables['elements']['#shoutbox_answer'];
  /** @var \Drupal\Core\Render\Renderer $renderer */
  $renderer = \Drupal::service('renderer');
  $currentUser = \Drupal::currentUser();

  $variables['author'] = [
    '#markup' => '<a href="mailto:'. Html::escape($shoutboxAnswer->getEmail()) .'">'. Html::escape($shoutboxAnswer->getName()) .'</a>',
  ];
  $variables['date'] = $shoutboxAnswer->getCreatedTime();
  $variables['unpublished'] = FALSE;
  $variables['answer'] = $variables['elements']['answer'];
  $variables['answer']['#cache'] = [
    'contexts' => ['user.permissions']
  ];
  $renderer->addCacheableDependency($variables['answer'], $shoutboxAnswer);


  // attach our shoutbox styling and javascript
  $variables['#attached']['library'][] = 'test_shoutbox/shoutbox';
  $variables['attributes']['class'][] = 'shoutbox-answer';

  if(!$shoutboxAnswer->isPublished() && $currentUser->hasPermission('administer shoutbox')) {
    $variables['attributes']['class'][] = 'shoutbox-answer--unpublished';
    $variables['unpublished'] = TRUE;
  }

  // add administration links to each answer
  $variables['operations'] = FALSE;
  $operations = $shoutboxAnswer->getAdminLinks();
  if($operations) {
    $variables['operations'] = [
      '#type' => 'operations',
      '#links' => $operations,
    ];
  }

}