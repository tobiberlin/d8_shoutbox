<?php

namespace Drupal\test_shoutbox\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Url;

/**
 * Class Shoutbox
 *
 * @package Drupal\test_shoutbox\Entity
 *
 * @ContentEntityType(
 *  id = "shoutbox_answer",
 *  label = @Translation("Answer on a shoutbox"),
 *  handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\test_shoutbox\Form\ShoutboxAnswerForm",
 *       "edit" = "Drupal\test_shoutbox\Form\ShoutboxAnswerForm",
 *       "delete" = "Drupal\test_shoutbox\Form\ShoutboxAnswerDeleteForm",
 *     },
 *     "access" =
 *   "Drupal\test_shoutbox\AccessHandler\ShoutboxAnswerAccessControlHandler",
 *  },
 *  base_table = "shoutbox_answers",
 *  admin_permission = "administer shoutbox",
 *  entity_keys = {
 *    "id" = "id",
 *    "published" = "published"
 *  },
 *  links = {
 *    "edit-form" = "/shoutbox-answer/{shoutbox_answer}/edit",
 *    "delete-form" = "/shoutbox-answer/{shoutbox_answer}/delete"
 *  }
 * )
 */
class ShoutboxAnswer extends ContentEntityBase implements EntityPublishedInterface {

  use EntityPublishedTrait;

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

    // The name of the answerer
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setRequired(TRUE)
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

    // The e-mail of the answerer
    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('E-mail'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value'   => '',
        'max_length'      => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label'  => 'hidden',
        'type'   => 'email_default',
        'weight' => -15,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'email_default',
        'weight' => -15,
      ]);

    // the answer of the user
    $fields['answer'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Answer'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label'  => 'hidden',
        'type'   => 'string',
        'weight' => -10,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'string_long',
        'weight' => -10,
      ]);

    // the shoutbox this answer belongs to
    $fields['shoutbox'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Shoutbox id'))
      ->setSettings(array(
        'target_type' => 'shoutbox_entity',
        'default_value' => 0,
      ));

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    // Make the published field visible on the shoutbox edit form
    $fields['published']->setDisplayOptions('form', [
      'type'     => 'boolean_checkbox',
      'settings' => [
        'display_label' => TRUE,
      ],
      'weight'   => 0,
    ])
     ->setDefaultValue(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the answer was created.'))
      ->setDisplayOptions('view', [
        'label'  => 'hidden',
        'type'   => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'datetime_timestamp',
        'weight' => 10,
      ]);
    return $fields;
  }


  /**
   * Get the shoutbox related to this answer
   *
   * @return \Drupal\test_shoutbox\Entity\Shoutbox|null
   */
  public function getShoutbox() {
    $referenceItem  = $this->get('shoutbox')->first();
    $entityReference = $referenceItem->get('entity');
    $entityAdapter = $entityReference->getTarget();
    return $entityAdapter->getValue();
  }


  /**
   * Get the created time
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }


  /**
   * Get the name of the author
   */
  public function getName() {
    return $this->get('name')->value;
  }


  /**
   * Get the email of the author
   */
  public function getEmail() {
    return $this->get('email')->value;
  }


  /**
   * Set the created time
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  public function getAdminLinks() {
    $operations = [];
    if ($this->access('update') && $this->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => t('Edit'),
        'weight' => 10,
        'url' => $this->ensureDestination($this->toUrl('edit-form')),
      ];
    }
    if ($this->access('delete') && $this->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => t('Delete'),
        'weight' => 100,
        'url' => $this->ensureDestination($this->toUrl('delete-form')),
      ];
    }

    return $operations;
  }


  /**
   * Ensures that a destination is present on the given URL.
   * Taken from EntityListBuilder::ensureDestination()
   *
   * @param \Drupal\Core\Url $url
   *   The URL object to which the destination should be added.
   *
   * @return \Drupal\Core\Url
   *   The updated URL object.
   */
  protected function ensureDestination(Url $url) {
    return $url->mergeOptions(['query' => \Drupal::destination()->getAsArray()]);
  }


  /**
   * @inheritdoc
   *
   * We add some logic here to invalidate our custom cache tag
   */
  protected function invalidateTagsOnSave($update) {
    parent::invalidateTagsOnSave($update);
    $this->invalidateCacheTagForAnswersList();
  }

  /**
   * @inheritdoc
   *
   * We add some logic here to invalidate our custom cache tag
   */
  protected static function invalidateTagsOnDelete(EntityTypeInterface $entity_type, array $entities) {
    parent::invalidateTagsOnDelete($entity_type, $entities);
    $tags = array();
    /** @var \Drupal\test_shoutbox\Entity\ShoutboxAnswer $entity */
    foreach ($entities as $entity) {
      if (!in_array($entity->getCacheTagAnswerList(), $tags)) {
        $tags[] = $entity->getCacheTagAnswerList();
      }
    }
    Cache::invalidateTags($tags);
  }

  /**
   * Invalidate our custom cache tag shoutbox_X_list.
   *
   * This cache tag is used when rendering the shoutbox answers list beneath a
   * shoutbox. By invalidating this specific cache tag we can invalidate the
   * list connected to the shoutbox to which this answer belongs to. Any other
   * available cache tag would invalidate any cache where an entity of type
   * "shoutbox_answer" is listed  not respecting the fact that not every answer
   * changes the content of every shoutbox.
   */
  protected function invalidateCacheTagForAnswersList() {
    Cache::invalidateTags(array($this->getCacheTagAnswerList()));
  }


  /**
   * @return string
   * The cache tag: shoutbox_X_answerlist where X is the id of the shoutbox
   */
  public function getCacheTagAnswerList() {
    return Shoutbox::getCacheTagAnswerList($this->getShoutbox()->id());
  }
}