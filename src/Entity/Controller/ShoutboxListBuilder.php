<?php

namespace Drupal\test_shoutbox\Entity\Controller;


use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;


/**
 * The list controller for our shoutbox entity.
 *
 * @see \Drupal\test_shoutbox\Entity\Shoutbox
 *
 */
class ShoutboxListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   *
   */
  public function buildHeader() {
    $header['question'] = $this->t('Question');
    $header['published'] = $this->t('Published');
    $header['answering_opened'] = $this->t('Open for answers');
    $header['answers_number'] = $this->t('Number of answers');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\test_shoutbox\Entity\Shoutbox */
    $row['question'] = $entity->link();
    $row['published'] =  $entity->isPublished() ? $this->t('published') : $this->t('not published');
    $row['answering_opened'] =  $entity->isOpenForAnswers() ? $this->t('Answers active') : $this->t('Answers closed');
    $row['answers_number'] = count($entity->getAnswers());
    return $row + parent::buildRow($entity);
  }
}