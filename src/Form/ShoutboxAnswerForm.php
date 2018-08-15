<?php
namespace Drupal\test_shoutbox\Form;


use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\test_shoutbox\Entity\Shoutbox;
use Drupal\test_shoutbox\Entity\ShoutboxAnswer;

class ShoutboxAnswerForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   *
   * We only show a form when we can get a Shoutbox entity
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();
    $shoutbox = $this->getShoutboxFromContext();

    if ($shoutbox) {
      $form_state->setStorage(array('shoutbox' => $shoutbox));
      $form = parent::buildForm($form, $form_state);
      $currentUser = \Drupal::currentUser();

      // pre-set the published field depending on the shoutbox settings on a add form
      if ($this->entity->isNew()) {
        $form['published']['widget']['value']['#default_value'] = $shoutbox->publishAnswersDirectly();
      }

      $this->setModerationFields($form, $currentUser);

      // pre-fill the email field if possible
      if ($currentUser->isAuthenticated()) {
        $form['email']['widget'][0]['value']['#default_value'] = $currentUser->getEmail();
      }
    }

    // add accordeon support
    $form['#attributes']['class'][] = 'js-accordion-panel';
    $form['#attributes']['class'][] = 'shoutbox-answer-form';

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   */
  public function save(array $form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    /** @var \Drupal\test_shoutbox\Entity\Shoutbox */
    $shoutbox = $storage['shoutbox'];
    $this->entity->set('shoutbox', $shoutbox->id());

    $status = parent::save($form, $form_state);
    $message = $this->getSavedMessage($status, $shoutbox);
    if (!empty($message)) {
      drupal_set_message($message);
    }

    $form_state->setRedirectUrl($shoutbox->toUrl());
    return $status;
  }


  /**
   * Depending on the context we get the shoutbox in different ways
   *  - the add form is shown only on shoutbox detail page so we can get the
   *    shoutbox from the current request
   *  - on edit form for answer the shoutbox can be get from the shoutbox
   *    answer itself
   *
   * @return \Drupal\test_shoutbox\Entity\Shoutbox|NULL
   */
  protected function getShoutboxFromContext() {
    $shoutbox = \Drupal::request()->attributes->get('shoutbox_entity');

    if (!$shoutbox) {
      $shoutbox = $this->entity->getShoutbox();
    }

    return $shoutbox;
  }


  /**
   * Hide moderation fields from user with not fitting permissions and group
   * those fields for admins
   *
   * @param array $form
   * @param $currentUser
   */
  protected function setModerationFields(array &$form, $currentUser) {
    if(!$currentUser->hasPermission('administer shoutbox')) {
      $form['published']['#access'] = FALSE;
      $form['created']['#access'] = FALSE;
    }
    else {
      // create the moderation field area
      $form['moderation'] = array(
        '#type' => 'details',
        '#title' => t('Moderation'),
        '#open' => TRUE
      );
      $form['published']['#group'] = 'moderation';
      $form['created']['#group'] = 'moderation';

      // hide created date field on add form
      if ($this->entity->id() === NULL) {
        $form['created']['#access'] = FALSE;
      }
    }
  }


  /**
   * Get the message shown to the user after successfull saving
   *
   * @param $status
   * @param \Drupal\test_shoutbox\Entity\Shoutbox $shoutbox
   *
   * @return string
   */
  protected function getSavedMessage($status, Shoutbox $shoutbox) {
    $message = '';

    if ($status == SAVED_UPDATED) {
      $message = t('The answer has been updated.');
    }
    else {
      if ($shoutbox->publishAnswersDirectly()) {
        $message = t('Your answer to the question %question has been added.', ['%question' => $shoutbox->label()]);
      }
      else {
        $message = t('Your answer to the question %question has been saved and will be published after moderation..', ['%question' => $shoutbox->label()]);
      }
    }

    return $message;
  }


  /**
   * Set the title for an edit form
   *
   * @param \Drupal\test_shoutbox\Entity\ShoutboxAnswer $shoutbox_answer
   *
   * @return array
   */
  public static function title(ShoutboxAnswer $shoutbox_answer) {
    if ($shoutbox_answer) {
      $title = t('Edit answer of @name for question %question', [
        '@name' => $shoutbox_answer->getName(),
        '%question' => $shoutbox_answer->getShoutbox()->label()
      ]);
      return ['#markup' => $title, '#allowed_tags' => Xss::getHtmlTagList()];
    }
  }
}