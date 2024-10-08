<?php

/**
 * @file
 * Theme and preprocess functions for menus.
 */

/**
 * Implements hook_preprocess_menu().
 */
function radix_preprocess_menu(&$variables, $hook) {
  // No changes for menu toolbar.
  if ($hook == 'menu__toolbar') {
    return;
  }

  // Get the current path.
  $current_path = \Drupal::request()->getRequestUri();

  $items = $variables['items'];
  foreach ($items as $key => $item) {
    if (isset($item['url']) && is_object($item['url']) && $item['url']->toString() == $current_path) {
      $variables['items'][$key]['in_active_trail'] = TRUE;
    }

    if (isset($item['url']) && is_object($item['url']) && $item['url']->isRouted() && $item['url']->getRouteName() === '<nolink>') {
      $variables['items'][$key]['attributes']->addClass('navbar-text');
    }
  }
}

/**
 * Implements template_preprocess_menu_local_action().
 */
function radix_preprocess_menu_local_action(&$variables) {
  // Add button classes.
  $variables['link']['#options']['attributes']['class'][] = 'btn';
  $variables['link']['#options']['attributes']['class'][] = 'btn-primary';
}

/**
 * Implements template_preprocess_links__dropbutton().
 */
function radix_preprocess_links__dropbutton(&$variables) {
  $links = &$variables['links'];

  // Do nothing if we have no links.
  if (!count($links)) {
    return;
  }

  // Get the first link and use it for the dropbutton.
  $link = reset($links);

  /** @var \Drupal\Core\Url $url */
  $variables['split'] = FALSE;
  if (isset($link['link']) && ($url = $link['link']['#url'])) {
    $button = $link['link'];

    if ($variables['split'] = $url->getRouteName() !== '<nolink>') {
      $button['#options']['attributes']['class'][] = 'btn';
      $button['#options']['attributes']['class'][] = 'btn-outline-dark';
    }

    $variables['button'] = $button;

    // Remove first link from links.
    array_shift($links);

    foreach ($links as $key => $link) {
      $links[$key]['link']['#options']['attributes']['class'][] = 'dropdown-item';
    }

  } elseif (isset($link['text'])) {
    $button = $link['text'];
    $variables['button'] = $button;

    // Remove first link from links.
    array_shift($links);
  }

  // Add required classes.
  foreach ($links as $key => $link) {
    if (isset($links[$key]['text_attributes'])) {
      $links[$key]['text_attributes']->addClass('dropdown-item');
    }

    if (isset($links[$key]['attributes'])) {
      $links[$key]['attributes']->addClass('dropdown-item');
    }
  }
}
