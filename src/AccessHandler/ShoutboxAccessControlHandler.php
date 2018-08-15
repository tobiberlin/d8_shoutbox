<?php

namespace Drupal\test_shoutbox\AccessHandler;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access handler for our shoutbox entity
 *
 * @see \Drupal\test_shoutbox\Entity\Shoutbox
 */

class ShoutboxAccessControlHandler extends EntityAccessControlHandler{


  /**
   * {@inheritdoc}
   *
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if ($account->hasPermission('administer shoutbox')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        if ($entity->isPublished() && $account->hasPermission('view shoutbox entity')) {
          return AccessResult::allowed()->addCacheableDependency($entity)->cachePerPermissions();
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
    return AccessResult::allowedIfHasPermission($account, 'administer shoutbox')->cachePerPermissions();
  }
}