<?php

namespace Drupal\test_shoutbox\AccessHandler;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;


/**
 * Access checks related to shoutbox answers
 */
class ShoutboxAnswerAccessControlHandler extends EntityAccessControlHandler {



  /**
   * {@inheritdoc}
   *
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\test_shoutbox\Entity\Shoutbox $shoutbox */
    $shoutbox = $entity->getShoutbox();

    switch ($operation) {
      case 'view':
        // admin users always can view answers
        if ($account->hasPermission('administer shoutbox')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        // other users can view answers in case a) the related shoutbox is published, b) the answer is
        // published and c) the user has the permission to view shoutboxes
        if ($shoutbox->isPublished() && $entity->isPublished() && $account->hasPermission('view shoutbox entity')) {
          return AccessResult::allowed()->addCacheableDependency($shoutbox)->addCacheableDependency($entity)->cachePerPermissions();
        }
        return AccessResult::forbidden();

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'administer shoutbox')->cachePerPermissions();

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'administer shoutbox')->cachePerPermissions();
    }
    return AccessResult::forbidden();
  }


  /**
   * {@inheritdoc}
   *
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add answer to shoutbox');
  }
}