<?php

namespace Drupal\test_shoutbox\Form;


use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\test_shoutbox\Entity\Shoutbox;


/**
 * Form controller for editing and adding form for our shoutbox entity.
 *
 * @see \Drupal\test_shoutbox\Entity\Shoutbox
 *
 */
class ShoutboxForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $this->setAdministrationFields($form);

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $entity = $this->entity;
    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The shoutbox for question %question has been updated.', ['%question' => $entity->toLink()->toString()]));
    } else {
      drupal_set_message($this->t('The shoutbox for question %question has been added.', ['%question' => $entity->toLink()->toString()]));
    }

    $form_state->setRedirectUrl($this->entity->toUrl());
    return $status;
  }


  /**
   * Hide administration fields for user not-fitting permissions and group those
   * fields for admins
   *
   * @param array $form
   */
  protected function setAdministrationFields(array &$form) {

    // hide published field and created date field from non admin users
    $currentUser = \Drupal::currentUser();
    if(!$currentUser->hasPermission('administer shoutbox')) {
      $form['published']['#access'] = FALSE;
      $form['answering_opened']['#access'] = FALSE;
      $form['answers_published']['#access'] = FALSE;
    }
    else {
      // create the moderation field area
      $form['administration'] = array(
        '#type' => 'details',
        '#title' => t('Administration'),
        '#open' => TRUE
      );
      $form['published']['#group'] = 'administration';
      $form['answering_opened']['#group'] = 'administration';
      $form['answers_published']['#group'] = 'administration';
    }
  }


  /**
   * Set the title for an edit form
   *
   * @param Shoutbox $shoutbox_entity
   *
   * @return array
   */
  public static function title(Shoutbox $shoutbox_entity) {
    if ($shoutbox_entity) {
      return ['#markup' => $shoutbox_entity->label(), '#allowed_tags' => Xss::getHtmlTagList()];
    }
  }
}