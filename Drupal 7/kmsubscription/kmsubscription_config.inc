<?php

/**
 * @file
 * Knowledge Marketing Subscription Module Config File.
 */

/**
 * Form builder; Configure the knowledge marketing subscription.
 *
 * @see system_settings_form()
 */
function kmsuite_group_settings($form, $form_state) {

  $form['description'] = array(
    '#markup' => '<div>' . t('Group Management:  The Group Management configurations determine detailed information for each group you elect to have working in your website.  Note:  API Counts are caches for 24 hours to avoid slow page loads..</a>', array('!suite' => url('google.com'), '!kmcontact' => url('google.com'))) . '</div>',
  );

  $presets = json_decode(variable_get('kmsuite_object'), TRUE);

  //echo "<pre>"; print_r($presets); die();
  $lib = libraries_get_path('kmsuite') . '/';

  if (file_exists($lib . 'class.ecnlistmanager.php') && file_exists($lib . 'class.ecncommunicator.php') && strlen($presets['config']['token']) > 3) {

    $form['km_group_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Subscription Page Settings'),
      '#description' => t('Add additional settings to how the subscription page should look and function'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );
    $form['km_group_settings']['success_msg'] = array(
      '#type' => 'textfield',
      '#title' => t('Success Message'),
      '#default_value' => isset($presets['subscription_plugin']['page']['success_msg']) ? $presets['subscription_plugin']['page']['success_msg'] : t('Settings Saved.'),
      '#description' => t('Define the message shown upon successful subscription'),
    );
    $form['km_group_settings']['error_msg'] = array(
      '#type' => 'textfield',
      '#title' => t('Error Message'),
      '#default_value' => isset($presets['subscription_plugin']['page']['error_msg']) ? $presets['subscription_plugin']['page']['error_msg'] : t('An error occurred.'),
      '#description' => t('Define the message shown upon an error in the subscription'),
    );

    $rows = array();
    $row_elements = array();
    $data = array();
    if (isset($presets['subscription_plugin']['fields'])) {
      foreach ($presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields'] as $k => $v) {

        $data[$k] = array(
          'enable' => isset($presets['subscription_plugin']['page']['fields'][$k]['enabled']) ? $presets['subscription_plugin']['page']['fields'][$k]['enabled'] : FALSE,
          'required' => isset($presets['subscription_plugin']['page']['fields'][$k]['required']) ? $presets['subscription_plugin']['page']['fields'][$k]['required'] : FALSE,
          'weight' => isset($presets['subscription_plugin']['page']['fields'][$k]['weight']) ? $presets['subscription_plugin']['page']['fields'][$k]['weight'] : 0,
          'name' => $presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['names'][$k],
        );
      }

      // Sort the rows.
      uasort($data, 'array_uasort');

      // Build the rows.
      foreach ($data as $id => $entry) {
        // Build the table rows.
        $rows[$id] = array(
          'data' => array(
            // Cell for the cross drag&drop element.
            array('class' => array('entry-cross')),
            // Weight item for the tabledrag.
            array(
              'data' => array(
                '#type' => 'weight',
                '#title' => t('Weight'),
                '#title_display' => 'invisible',
                '#default_value' => $entry['weight'],
                '#parents' => array('field_table', $id, 'weight'),
                '#attributes' => array(
                  'class' => array('field-order-weight'),
                ),
              )),
            // Enabled checkbox.
            array(
              'data' => array(
                '#type' => 'checkbox',
                '#title' => t('Enable'),
                '#title_display' => 'invisible',
                '#default_value' => $entry['enable'],
                '#parents' => array('field_table', $id, 'enabled'),
              )),
            // Enabled checkbox.
            array(
              'data' => array(
                '#type' => 'checkbox',
                '#title' => t('Required'),
                '#title_display' => 'invisible',
                '#default_value' => $entry['required'],
                '#parents' => array('field_table', $id, 'required'),
              )),

            check_plain($entry['name']),

          ),
          'class' => array('draggable'),
        );
        // Build rows of the form elements in the table.
        $row_elements[$id] = array(
          'weight' => &$rows[$id]['data'][1]['data'],
          'enabled' => &$rows[$id]['data'][2]['data'],
          'required' => &$rows[$id]['data'][3]['data'],
        );
      }

      $form['km_group_settings']['markup'] = array(
        '#type' => 'markup',
        '#markup' => t('Below you will find the available fields that can be displayed on the page.  To add more, please use the "Fields" tab above.'),
      );

      // Add the table to the form.
      $form['km_group_settings']['table'] = array(
        '#theme' => 'table',
        // The row form elements need to be processed and build,
        // therefore pass them as element children.
        'elements' => $row_elements,
        '#header' => array(
          // We need two empty columns for the weigth field and the cross.
          array('data' => NULL, 'colspan' => 2),
          t('Enabled'),
          t('Required'),
          t('Name'),
        ),
        '#rows' => $rows,
        '#empty' => t('There are no fields available.'),
        '#attributes' => array('id' => 'field-order'),
      );
      drupal_add_tabledrag('field-order', 'order', 'sibling', 'field-order-weight');
    }

    $form['km_group_selection'] = array(
      '#type' => 'fieldset',
      '#title' => t('Step 1: Group Selection'),
      '#description' => t('Select the available lists you would like your website to have access too.  We realize you may have older groups, or groups belonging to alternate sites, you in this section you determine which groups you would like available.  NOTE:  You have to select a list and hit save before it shows as an option below.'),
      '#collapsible' => TRUE,
      '#collapsed' => isset($presets['subscription_plugin']['groups']) ? TRUE : FALSE,
    );

    require $lib . 'class.ecnapi.php';

    $token = $presets['config']['token'];
    $customerid = $presets['config']['customerid'];
    $lm = new ECNManager($token, $customerid);
    $lists = $lm->GetLists();

    $groups_arr = array();

    foreach ($lists as $group) {
     $groups_arr[(int) $group->ApiObject->GroupID] = (string) $group->ApiObject->GroupName;
    }

    $form['km_group_selection']['km_all_groups'] = array(
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => t('List Selection'),
      '#description' => t('Group Selection:  You can select as many groups as you would like for your werbsite to use.'),
      '#default_value' => isset($presets['subscription_plugin']['groups']) ? array_keys($presets['subscription_plugin']['groups']) : NULL,
      '#options' => $groups_arr,
    );

    // Step 2:  Configure the options for each group.
    $form['km_group_details'] = array(
      '#type' => 'fieldset',
      '#title' => t('Step 2: Group Configuration'),
      '#description' => t('You can configure each group to have custom fields\'s available.  Note:  When adding multiple lists to the subscription blocks later on, if a fields is not available, the field will not pass along the data.'),
      '#collapsible' => TRUE,
      '#collapsed' => isset($presets['subscription_plugin']['groups']) ? FALSE : TRUE,
    );

    if (isset($presets['subscription_plugin']['groups'])) {

      // Gather all possible fields fields implemented for this website and present as available options to the group.
      $fields_arr = array();
      if (isset($presets['subscription_plugin']['fields']['kmsubscription_field_count'])) {
        $fields_ctr = $presets['subscription_plugin']['fields']['kmsubscription_field_count'];
      }

      for ($i = 0; $i < count(@$presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields']); $i++) {
        if (is_numeric($presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields'][$i])) {
          $fields_arr[$presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields'][$i]] = $presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['names'][$i];
        }
      }

      $header = array(
        'ECN Subscriber Count',
        'Drupal Subscriber Count',
        'Used Locations',
      );
      $frequency = array(
        'none' => 'Do Not Display',
        'daily' => 'Daily',
        'biweekly' => 'Bi-Weekly',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly'
      );
      // Initiate loop to iterate through saved groups.
      foreach ($presets['subscription_plugin']['groups'] as $k => $v) {

        $form['km_group_details']['km_saved_groups']['fieldset_' . $k] = array(
          '#type' => 'fieldset',
          '#title' => t($groups_arr[$k]),
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
        );

        // Update:  Cache requests to the API incase user loads multiple lists (each API call decreases page load significantly).
        $api_count = NULL;
        if ($presets['subscription_plugin']['groups'][$k]['apitimestamp'] == NULL || $presets['subscription_plugin']['groups'][$k]['apitimestamp'] >= strtotime('+1 day', time())) {

          $api_count = number_format((int) $lm->GetSubscriberCount($k)->Result);
        }
        else {
          $api_count = $presets['subscription_plugin']['groups'][$k]['apicount'];
        }

        $rows = array();
        $rows[] = array(
          $api_count,
          'TBD',
          'TBD',
        );

        $form['km_group_details']['km_saved_groups']['fieldset_' . $k]['table_' . $k] = array(
          '#type' => 'markup',
          '#markup' => theme('table', array('header' => $header, 'rows' => $rows)),
        );

        $form['km_group_details']['km_saved_groups']['fieldset_' . $k]['apitimestamp_' . $k] = array(
          '#type' => 'hidden',
          '#default_value' => $presets['subscription_plugin']['groups'][$k]['apitimestamp'] ? $presets['subscription_plugin']['groups'][$k]['apitimestamp'] : time(),
        );
        $form['km_group_details']['km_saved_groups']['fieldset_' . $k]['apicount_' . $k] = array(
          '#type' => 'hidden',
          '#default_value' => $api_count,
        );
        $form['km_group_details']['km_saved_groups']['fieldset_' . $k]['origname_' . $k] = array(
          '#type' => 'hidden',
          '#default_value' => $groups_arr[$k],
        );

        $form['km_group_details']['km_saved_groups']['fieldset_' . $k]['alias_' . $k] = array(
          '#type' => 'textfield',
          '#title' => t('Group Alias'),
          '#default_value' => $presets['subscription_plugin']['groups'][$k]['alias'],
          '#description' => t('The alias will be shown to the public for group selection.'),
        );
        $form['km_group_details']['km_saved_groups']['fieldset_' . $k]['description_' . $k] = array(
          '#type' => 'textarea',
          '#title' => t('Group Description'),
          '#default_value' => $presets['subscription_plugin']['groups'][$k]['desc'],
          '#description' => t('The optional discription will be shown on the subscription landing page.'),
        );
        $form['km_group_details']['km_saved_groups']['fieldset_' . $k]['frequency_' . $k] = array(
          '#type' => 'select',
          '#title' => t('Frequency'),
          '#default_value' => $presets['subscription_plugin']['groups'][$k]['frequency'],
          '#options' => $frequency,
        );
      }
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save Configuration',
      '#submit' => array('kmsubscription_settings_submit'),
    );

  }
  else {
    drupal_set_message(t('Error:  Either the KM library does not exist or you do not have a proper token configured. Try downloading the library from <a href="@github">GitHub</a>', array('@github' => 'https://github.com/kherda/kmsuite')), 'error');
  }

  return $form;
}

/**
 * Custom submit handler for the config settings.
 *
 * @todo Ensure group origname is available.  Currently only works on second submit.
 */
function kmsubscription_settings_submit($form, &$form_state) {

  $presets = json_decode(variable_get('kmsuite_object'), TRUE);

  // Save the page specific data.
  $presets['subscription_plugin']['page']['success_msg'] = $form_state['values']['success_msg'];
  $presets['subscription_plugin']['page']['error_msg'] = $form_state['values']['error_msg'];
  $presets['subscription_plugin']['page']['fields'] = $form_state['values']['field_table'];

  //First Unset groups
  unset($presets['subscription_plugin']['groups']);
  // This serves two purposes:
  // 1. Declares an array for each saved group.
  // 2. Removes previous groups incase we delete one from the selection.
  // @todo: apply default origname.
  foreach ($form_state['values']['km_all_groups'] as $group) {
    $presets['subscription_plugin']['groups'][$group] = array(
      'origname' => NULL,
      'alias' => NULL,
      'desc' => NULL,
      'frequency' => NULL,
      'fields' => NULL,
      'apitimestamp' => NULL,
      'apicount' => NULL,
    );
  }

  foreach ($form_state['values'] as $k => $v) {

    if (stristr($k, 'origname')) {

      $key = explode('_', $k);
      $key = $key[1];

      if (in_array($key, $form_state['values']['km_all_groups'])) {
        $presets['subscription_plugin']['groups'][$key]['origname'] = $v;
      }
    }
    if (stristr($k, 'alias')) {

      $key = explode('_', $k);
      $key = $key[1];

      if (in_array($key, $form_state['values']['km_all_groups'])) {
        $presets['subscription_plugin']['groups'][$key]['alias'] = $v;
      }
    }

    if (stristr($k, 'description')) {
      $key = explode('_', $k);
      $key = $key[1];
      if (in_array($key, $form_state['values']['km_all_groups'])) {
        $presets['subscription_plugin']['groups'][$key]['desc'] = $v;
      }
    }

    if (stristr($k, 'frequency')) {
      $key = explode('_', $k);
      $key = $key[1];
      if (in_array($key, $form_state['values']['km_all_groups'])) {
        $presets['subscription_plugin']['groups'][$key]['frequency'] = $v;
      }
    }

    if (stristr($k, 'apitimestamp')) {
      $key = explode('_', $k);
      $key = $key[1];
      if (in_array($key, $form_state['values']['km_all_groups'])) {
        $presets['subscription_plugin']['groups'][$key]['apitimestamp'] = $v;
      }
    }

    if (stristr($k, 'apicount')) {
      $key = explode('_', $k);
      $key = $key[1];
      if (in_array($key, $form_state['values']['km_all_groups'])) {
        $presets['subscription_plugin']['groups'][$key]['apicount'] = $v;
      }
    }

    if (stristr($k, 'fields')) {
      $key = explode('_', $k);
      $key = $key[1];

      // Saved Fields that the user wants to send over.
      if (in_array($key, $form_state['values']['km_all_groups'])) {
        $presets['subscription_plugin']['groups'][$key]['fields'] = $v;
      }
    }
  }

  variable_set('kmsuite_object', json_encode($presets));
  drupal_set_message(t('Configuration settings saved.'), 'status', FALSE);
}

/**
 * Custom field settings for the Fields.
 */
function kmsubscription_field_settings($form, &$form_state) {

  $presets = json_decode(variable_get('kmsuite_object'), TRUE);

  if (isset($form_state['values']['op']) && $form_state['values']['op'] == 'Add Field') {
    $entries = $presets['subscription_plugin']['fields']['kmsubscription_field_count'];
    $entries++;
    $presets['subscription_plugin']['fields']['kmsubscription_field_count'] = $entries;
    variable_set('kmsuite_object', json_encode($presets));
  }
  if (isset($form_state['values']) && in_array('Delete', $form_state['values'])) {
    $remove_key = array_search('Delete', $form_state['values']);
    unset($presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['names'][$remove_key]);
    unset($presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields'][$remove_key]);
    $presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['names'] = array_values($presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['names']);
    $presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields'] = array_values($presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields']);
    $entries = $presets['subscription_plugin']['fields']['kmsubscription_field_count'];
    $entries--;
    $presets['subscription_plugin']['fields']['kmsubscription_field_count'] = $entries;
    variable_set('kmsuite_object', json_encode($presets));
    $form_state['input'] = array();
  }
  if (isset($form_state['values']['op']) && $form_state['values']['op'] == 'Add Type' && $form_state['values']['field_name'] != '') {
    if (in_array($form_state['values']['field_name'], $presets['subscription_plugin']['fields']['kmsubscription_fields'])) {
      drupal_set_message('The field type entered already exists.', 'error');
    }
    else {
      $options = $presets['subscription_plugin']['fields']['kmsubscription_fields'];
      $options[] = 'user_' . $form_state['values']['field_name'];
      $presets['subscription_plugin']['fields']['kmsubscription_fields'] = $options;

      variable_set('kmsuite_object', json_encode($presets));
    }
  }

  $form['km_subscriber_fields'] = array(
    '#type' => 'fieldset',
    '#title' => t('Subscriber Management'),
    '#description' => t('Select the available fields (user defined fields) you would like available for your subscriber.  Note: '),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#prefix' => '<div id="fields-fields">',
    '#suffix' => '</div>'
  );

  $rows = _build_table_array($presets);

  $form['km_subscriber_fields']['subscriber_settings_table'] = array(
    '#theme' => 'kmsubscription_list_settings_table',
    '#header' => array(
      t('Field'),
      t('Display Name'),
      t('Remove Field'),
    ),
    'rows' => $rows,
    '#prefix' => '<div id="fields-fields">',
    '#suffix' => '</div>'
  );

  $form['km_subscriber_fields']['add_field'] = array(
    '#type' => 'button',
    '#value' => 'Add Field',
    '#ajax' => array(
      'callback' => 'ajax_refresh_field',
      'wrapper' => 'fields-fields',
      'method' => 'replace',
      'effect' => 'fade',
    ),
  );

  $form['km_subscriber_fields']['subscriber_add_settings_table'] = array(
    '#theme' => 'kmsubscription_list_settings_table',
    '#description' => t('Add new field types to the field selection.'),
    '#header' => array(
      t('Field Name'),
      t('Field Type Action'),
    ),
    'rows' => array(
      'row1' => array(
        'field_name' => array(
          '#type' => 'textfield',
          '#description' => t('This is your "Key" value.  This value MUST match the UDF key put into the ECN!'),
        ),
        'add_type' => array(
          '#type' => 'button',
          '#value' => 'Add Type',
          '#ajax' => array(
            'callback' => 'ajax_refresh_field',
            'wrapper' => 'fields-fields',
            'method' => 'replace',
            'effect' => 'fade',
          ),
        ),
      ),
    ),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Save Configuration',
    '#submit' => array('kmsuite_subscriber_settings_submit'),
  );

  return $form;
}

/**
 * Custom field submit handler.
 */
function kmsuite_subscriber_settings_submit($form, &$form_state) {

  $presets = json_decode(variable_get('kmsuite_object'), TRUE);

  foreach ($form_state['values']['rows'] as $key => $value) {
    $presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields'][$key] = $value['field'];
    $presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['names'][$key] = $value['name'];
  }

  variable_set('kmsuite_object', json_encode($presets));
  drupal_set_message(t('Configuration settings saved.'), 'status', FALSE);
}

/**
 * Array builder.
 */
function _build_table_array($presets) {
  $rows = array('#tree' => TRUE);

  global $options;

  if (isset($presets['subscription_plugin']['fields']['kmsubscription_fields'])) {
    $options = $presets['subscription_plugin']['fields']['kmsubscription_fields'];
  }
  else {

    // WORK
    // Need to make sure ALL keys match 100%
    // Also need to add user_ infront of UDF's
    //
    $options = array(
      'EmailAddress',
      'FirstName',
      'LastName',
      'FullName',
      'Title',
      'Company',
      'Occupation',
      'Address1',
      'Address2',
      'City',
      'State',
      'Zip',
      'Voice',
      'Country',
      'Phone',
      'Mobile',
      'Fax',
      'Website',
      'Age',
      'Income',
      'Gender',
    );
    $presets['subscription_plugin']['fields']['kmsubscription_fields'] = $options;
    variable_set('kmsuite_object', json_encode($presets));
  }

  if (isset($presets['subscription_plugin']['fields']['kmsubscription_field_count'])) {
    $entries = $presets['subscription_plugin']['fields']['kmsubscription_field_count'];
  }
  else {
    $entries = 0;
    $presets['subscription_plugin']['fields']['kmsubscription_field_count'] = 0;
    variable_set('kmsuite_object', json_encode($presets));
  }

  for ($i = 0; $i <= $entries; $i++) {

    if (isset($presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields'][$i])) {
      $field = $presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['fields'][$i];
    }
    else {
      $field = '';
    }
    if (isset($presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['names'][$i])) {
      $name = $presets['subscription_plugin']['fields']['kmsubscription_saved_fields']['names'][$i];
    }
    else {
      $name = '';
    }

    $rows[$i] = array(
      'field' => array(
        '#type' => 'select',
        '#default_value' => $field,
        '#options' => $options,
      ),
      'name' => array(
        '#type' => 'textfield',
        '#default_value' => $name,
      ),
      'delete' => array(
        '#type' => 'button',
        '#value' => 'Delete',
        '#name' => $i,
        '#ajax' => array(
          'callback' => 'ajax_refresh_field',
          'wrapper' => 'fields-fields',
          'method' => 'replace',
          'effect' => 'fade',
        ),
      ),
    );
  }

  return $rows;
}

/**
 * Ajax Form Rebuild.
 */
function ajax_refresh_field($form, $form_state) {

  return $form['km_subscriber_fields'];
}
