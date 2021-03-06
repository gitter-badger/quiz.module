<?php

$view = new view();
$view->name = 'quiz';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'quiz_entity';
$view->human_name = 'Quiz';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Quiz';
$handler->display->display_options['use_ajax'] = TRUE;
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'administer quiz configuration';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '50';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['pager']['options']['id'] = '0';
$handler->display->display_options['pager']['options']['quantity'] = '9';
$handler->display->display_options['pager']['options']['expose']['items_per_page'] = TRUE;
$handler->display->display_options['pager']['options']['expose']['items_per_page_options'] = '5, 10, 20, 40, 50, 75, 100';
$handler->display->display_options['style_plugin'] = 'table';
$handler->display->display_options['style_options']['columns'] = array(
    'url'         => 'url',
    'title'       => 'title',
    'name'        => 'name',
    'type'        => 'type',
    'changed'     => 'changed',
    'created'     => 'created',
    'view_node'   => 'view_node',
    'delete_node' => 'view_node',
    'edit_node'   => 'view_node',
);
$handler->display->display_options['style_options']['default'] = '-1';
$handler->display->display_options['style_options']['info'] = array(
    'url'         => array(
        'align'        => '',
        'separator'    => '',
        'empty_column' => 0,
    ),
    'title'       => array(
        'sortable'           => 0,
        'default_sort_order' => 'asc',
        'align'              => '',
        'separator'          => '',
        'empty_column'       => 0,
    ),
    'name'        => array(
        'sortable'           => 0,
        'default_sort_order' => 'asc',
        'align'              => '',
        'separator'          => '',
        'empty_column'       => 0,
    ),
    'type'        => array(
        'sortable'           => 0,
        'default_sort_order' => 'asc',
        'align'              => '',
        'separator'          => '',
        'empty_column'       => 0,
    ),
    'changed'     => array(
        'sortable'           => 0,
        'default_sort_order' => 'asc',
        'align'              => '',
        'separator'          => '',
        'empty_column'       => 0,
    ),
    'created'     => array(
        'sortable'           => 0,
        'default_sort_order' => 'asc',
        'align'              => '',
        'separator'          => '',
        'empty_column'       => 0,
    ),
    'view_node'   => array(
        'align'        => '',
        'separator'    => ' | ',
        'empty_column' => 0,
    ),
    'delete_node' => array(
        'align'        => '',
        'separator'    => '',
        'empty_column' => 0,
    ),
    'edit_node'   => array(
        'align'        => '',
        'separator'    => '',
        'empty_column' => 0,
    ),
);
/* Relationship: Quiz properties: Uid */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'quiz_entity';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
$handler->display->display_options['relationships']['uid']['label'] = 'Author';
/* Field: Quiz properties: URL */
$handler->display->display_options['fields']['url']['id'] = 'url';
$handler->display->display_options['fields']['url']['table'] = 'views_entity_quiz_entity';
$handler->display->display_options['fields']['url']['field'] = 'url';
$handler->display->display_options['fields']['url']['exclude'] = TRUE;
$handler->display->display_options['fields']['url']['display_as_link'] = FALSE;
$handler->display->display_options['fields']['url']['link_to_entity'] = 0;
/* Field: Quiz properties: Label */
$handler->display->display_options['fields']['title']['id'] = 'title';
$handler->display->display_options['fields']['title']['table'] = 'quiz_entity';
$handler->display->display_options['fields']['title']['field'] = 'title';
$handler->display->display_options['fields']['title']['alter']['make_link'] = TRUE;
$handler->display->display_options['fields']['title']['alter']['path'] = '[url]';
/* Field: User: Name */
$handler->display->display_options['fields']['name']['id'] = 'name';
$handler->display->display_options['fields']['name']['table'] = 'users';
$handler->display->display_options['fields']['name']['field'] = 'name';
$handler->display->display_options['fields']['name']['relationship'] = 'uid';
/* Field: Quiz properties: Type */
$handler->display->display_options['fields']['type']['id'] = 'type';
$handler->display->display_options['fields']['type']['table'] = 'quiz_entity';
$handler->display->display_options['fields']['type']['field'] = 'type';
/* Field: Quiz properties: Changed */
$handler->display->display_options['fields']['changed']['id'] = 'changed';
$handler->display->display_options['fields']['changed']['table'] = 'quiz_entity';
$handler->display->display_options['fields']['changed']['field'] = 'changed';
/* Field: Quiz properties: Created */
$handler->display->display_options['fields']['created']['id'] = 'created';
$handler->display->display_options['fields']['created']['table'] = 'quiz_entity';
$handler->display->display_options['fields']['created']['field'] = 'created';
/* Field: Quiz properties: Link */
$handler->display->display_options['fields']['view_node']['id'] = 'view_node';
$handler->display->display_options['fields']['view_node']['table'] = 'quiz_entity';
$handler->display->display_options['fields']['view_node']['field'] = 'view_node';
/* Field: Quiz properties: Delete link */
$handler->display->display_options['fields']['delete_node']['id'] = 'delete_node';
$handler->display->display_options['fields']['delete_node']['table'] = 'quiz_entity';
$handler->display->display_options['fields']['delete_node']['field'] = 'delete_node';
/* Field: Quiz properties: Edit link */
$handler->display->display_options['fields']['edit_node']['id'] = 'edit_node';
$handler->display->display_options['fields']['edit_node']['table'] = 'quiz_entity';
$handler->display->display_options['fields']['edit_node']['field'] = 'edit_node';
/* Filter criterion: Quiz properties: Type */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'quiz_entity';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['exposed'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['operator_id'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['label'] = 'Quiz Type';
$handler->display->display_options['filters']['type']['expose']['operator'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
/* Filter criterion: Quiz properties: Status */
$handler->display->display_options['filters']['status']['id'] = 'status';
$handler->display->display_options['filters']['status']['table'] = 'quiz_entity';
$handler->display->display_options['filters']['status']['field'] = 'status';
$handler->display->display_options['filters']['status']['operator'] = '>=';
$handler->display->display_options['filters']['status']['value']['value'] = '0';

/* Display: Page */
$handler = $view->new_display('page', 'Page', 'page');
$handler->display->display_options['path'] = 'admin/content/quiz';
$handler->display->display_options['menu']['type'] = 'tab';
$handler->display->display_options['menu']['title'] = 'Quiz';
$handler->display->display_options['menu']['weight'] = '0';
$handler->display->display_options['menu']['context'] = 0;
$handler->display->display_options['menu']['context_only_inline'] = 0;
