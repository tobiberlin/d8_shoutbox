<?php
namespace Drupal\test_shoutbox\Form;


use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;


/**
 * Form controller for delete confirmation form for a shoutbox answer entity.
 *
 * @see \Drupal\test_shoutbox\Entity\Shoutbox
 *
 */
class ShoutboxAnswerDeleteForm extends ContentEntityConfirmFormBase {


  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure that you want to delete the answer of %name.', [
      '%name' => $this->getEntity()->getName(),
    ]);
  }


  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    /**@var \Drupal\test_shoutbox\Entity\ShoutboxAnswer */
    $entity = $this->getEntity();
    $shoutbox = $entity->getShoutbox();
    return $shoutbox->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\test_shoutbox\Entity\ShoutboxAnswer $shoutboxAnswer */
    $shoutboxAnswer = $this->getEntity();

    $shoutboxAnswer->delete();
    $form_state->setRedirectUrl($this->getRedirectUrl());

    drupal_set_message(t('The answer has been deleted'));
  }

  /**
   * Returns the URL where the user should be redirected after deletion.
   *
   * @return \Drupal\Core\Url
   *   The redirect URL.
   */
  protected function getRedirectUrl() {
    /** @var \Drupal\test_shoutbox\Entity\ShoutboxAnswer $shoutboxAnswer */
    $shoutboxAnswer = $this->getEntity();
    return $shoutboxAnswer->getShoutbox()->toUrl();
  }
}