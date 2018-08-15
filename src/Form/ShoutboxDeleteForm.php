<?php
namespace Drupal\test_shoutbox\Form;


use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Form controller for delete confirmation form for a shoutbox entity.
 *
 * @see \Drupal\test_shoutbox\Entity\Shoutbox
 *
 */
class ShoutboxDeleteForm extends ContentEntityConfirmFormBase {


  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('The shoutbox for question %title and all answers will be deleted.', [
      '%title' => $this->getEntity()->label(),
    ]);
  }


  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    /**@var \Drupal\test_shoutbox\Entity\Shoutbox */
    $entity = $this->getEntity();
    return $entity->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\test_shoutbox\Entity\Shoutbox $shoutbox */
    $shoutbox = $this->getEntity();

    $shoutbox->delete();
    $form_state->setRedirectUrl($shoutbox->toUrl('collection'));

    drupal_set_message(t('The shoutbox %title has been deleted', [
      '%title' => $shoutbox->label()
    ]));
  }
}