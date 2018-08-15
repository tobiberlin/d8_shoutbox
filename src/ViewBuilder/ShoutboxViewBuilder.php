<?php

namespace Drupal\test_shoutbox\ViewBuilder;


use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

class ShoutboxViewBuilder extends EntityViewBuilder {


  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $build = parent::getBuildDefaults($entity, $view_mode);

    // add the answers
    $build['answers'] = array(
      '#theme' => 'shoutbox_answers_list',
      '#shoutbox' => $entity
    );

    return $build;

  }

}