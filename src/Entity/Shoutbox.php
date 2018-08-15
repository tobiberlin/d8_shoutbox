<?php

namespace Drupal\test_shoutbox\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Class Shoutbox
 *
 * @package Drupal\test_shoutbox\Entity
 *
 * @ContentEntityType(
 *  id = "shoutbox_entity",
 *  label = @Translation("Shoutbox entity"),
 *    handlers = {
 *     "view_builder" = "Drupal\test_shoutbox\ViewBuilder\ShoutboxViewBuilder",
 *     "list_builder" = "Drupal\test_shoutbox\Entity\Controller\ShoutboxListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\test_shoutbox\Form\ShoutboxForm",
 *       "edit" = "Drupal\test_shoutbox\Form\ShoutboxForm",
 *       "delete" = "Drupal\test_shoutbox\Form\ShoutboxDeleteForm",
 *     },
 *     "access" = "Drupal\test_shoutbox\AccessHandler\ShoutboxAccessControlHandler",
 *   },
 *   base_table = "shoutbox",
 *   admin_permission = "administer shoutbox",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "question",
 *     "published" = "published"
 *   },
 *   links = {
 *     "canonical" = "/shoutbox/{shoutbox_entity}",
 *     "edit-form" = "/shoutbox/{shoutbox_entity}/edit",
 *     "delete-form" = "/shoutbox/{shoutbox_entity}/delete",
 *     "collection" = "/shoutbox-list"
 *   }
 * )
 */
class Shoutbox extends ContentEntityBase implements EntityPublishedInterface {

  use EntityPublishedTrait;


  /**
   * @var \Drupal\test_shoutbox\Entity\ShoutboxAnswer[]|NULL
   * Array of shoutbox answers related to the shoutbox. NULL if not loaded so far.
   * Can be an empty array when no answers were found
   */
  protected $answers = NULL;



  /**
   * {@inheritdoc}
   *
   * Define the field properties.
   *
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the shoutbox.'))
      ->setReadOnly(TRUE);


    // The question for the shoutbox
    $fields['question'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Question'))
      ->setDescription(t('The question which should be answered by users.'))
      ->setSettings([
        'default_value'   => '',
        'max_length'      => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label'  => 'hidden',
        'type'   => 'string',
        'weight' => -20,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'string_textfield',
        'weight' => -20,
      ]);

    // add a description displayed to the users
    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('Detals about the question.'))
      ->setDisplayOptions('view', [
        'label'  => 'hidden',
        'type'   => 'string',
        'weight' => -10,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'text_long',
        'weight' => -10,
      ]);

    // if answers are open or closed
    $fields['answering_opened'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Answering open'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type'     => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight'   => 0,
      ]);
    // if answers are open or closed
    $fields['answers_published'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Answers should be published directly without moderation'))
      ->setDescription(t('If checked answers will be published just after creation. If not checked every answer needs to be published manually after moderation.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type'     => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight'   => 0,
      ]);
    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    // Make the published field visible on the shoutbox edit form
    $fields['published']->setDisplayOptions('form', [
        'type'     => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight'   => 0,
      ]);

    return $fields;
  }

  /**
   * Checks if answers are possible for the shoutbox
   *
   * @return bool
   */
  public function isOpenForAnswers() {
    return (bool) $this->get('answering_opened')->value;
  }

  /**
   * Checks if answers should be published directly without moderation
   *
   * @return bool
   */
  public function publishAnswersDirectly() {
    return (bool) $this->get('answers_published')->value;
  }

  /**
   * load all related answers
   */
  protected function loadAnswers() {
    $cid = 'shoutbox_'. $this->id() .'_answers';
    $answers = &drupal_static($cid);
    if (!isset($answers)) {
      $answers = \Drupal::entityTypeManager()->getStorage('shoutbox_answer')->loadByProperties([
        'shoutbox' => $this->id()
      ]);
    }

    return $answers;
  }


  /**
   * Get the answers related to the shoutbox
   */
  public function getAnswers() {
    if ($this->answers === NULL) {
      $this->answers = $this->loadAnswers();
    }

    return $this->answers;
  }

  /**
   * {@inheritdoc}

   */
  public function delete() {
    parent::delete();
    $this->deleteAllAnswers();
  }


  /**
   * Delete all answers related to the shoutbox
   */
  public function deleteAllAnswers() {
    $answers = $this->getAnswers();
    if ($answers) {
      $this->entityTypeManager()->getStorage('shoutbox_answer')->delete($answers);
    }
  }


  /**
   * @param int|string $shoutbox_id
   * The id of the shoutbox
   *
   * @return string
   * The cache tag: shoutbox_X_answerlist where X is the id of the shoutbox
   */
  public static function getCacheTagAnswerList($shoutbox_id) {
    return 'shoutbox_'. $shoutbox_id .'_answerlist';
  }
}