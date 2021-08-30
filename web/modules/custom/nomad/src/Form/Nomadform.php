<?php

namespace Drupal\nomad\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contains form created in order to create counting table.
 */
class Nomadform extends FormBase {

  /**
   * Contains form id.
   */
  public function getFormId() {
    return 'nomad_name_form';
  }

  /**
   * Variable used for tracking number of years.
   *
   * @var track
   */
  protected $track = 1;

  /**
   * Variable used for output data from database in different functions.
   *
   * @var database
   */
  protected $database;

  /**
   * Variable used for load months in different functions inside loops.
   *
   * @var globalMonths
   */
  protected $globalMonths;

  /**
   * Variable used for tracking number of tables.
   *
   * @var tablecount
   */
  protected $tablecount = 1;

  /**
   * Using build form function to create Tables.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Creating months and put them to global variable in order to,
    // further reuse.
    $months = [
      0 => 'Jan',
      1 => 'Feb',
      2 => 'Mar',
      3 => 'Apr',
      4 => 'May',
      5 => 'Jun',
      6 => 'Jul',
      7 => 'Aug',
      8 => 'Sep',
      9 => 'Oct',
      10 => 'Nov',
      11 => 'Dec',
    ];
    $this->globalMonths = $months;
    // Fetching data from database and based on - is database output empty,
    // or not building table and storing data differently,
    // also added database output to global variable.
    $db = \Drupal::service('database');
    $select = $db->select('nomad', 'r');
    $select->fields('r', ['Table', 'Row', 'Year', 'Jan', 'Feb', 'Mar',
      'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
      'Oct', 'Nov', 'Dec',
      'id',
    ]);
    $select->orderBy('Table', 'ASC');
    $output = $select->execute()->fetchall();
    $this->database = $output;
    $values = [];
    // If DB output is not NULL - encode stdClass and build form with indexes,
    // that equal to year index and table index.
    if ($output != NULL) {
      $changed = json_decode(json_encode($output), TRUE);
      // Checking what number of years and tables, was already
      // in database in order to right build tables from database.
      foreach ($changed as $item => $value) {
        if ((int) $value['Table'] > $this->tablecount) {
          $this->tablecount = $value['Table'];
        }
        if ((int) $value['Row'] > $this->track) {
          $this->track = $value['Row'];
        }
        if (count($changed) == 1) {
          $this->track = 0;
        }
      }
      $value = [];
      for ($j = $this->tablecount; $j > 0; $j--) {
        for ($i = $this->track; $i >= 0; $i--) {
          $key = $this->searchForId($j, $i, $changed);
          if (isset($key)) {
            $value[$i] = [
              0 => ($changed[$key][$months[0]]) != NULL ? (int) ($changed[$key][$months[0]]) : '',
              1 => ($changed[$key][$months[1]]) != NULL ? (int) ($changed[$key][$months[1]]) : '',
              2 => ($changed[$key][$months[2]]) != NULL ? (int) ($changed[$key][$months[2]]) : '',
              3 => ($changed[$key][$months[3]]) != NULL ? (int) ($changed[$key][$months[3]]) : '',
              4 => ($changed[$key][$months[4]]) != NULL ? (int) ($changed[$key][$months[4]]) : '',
              5 => ($changed[$key][$months[5]]) != NULL ? (int) ($changed[$key][$months[5]]) : '',
              6 => ($changed[$key][$months[6]]) != NULL ? (int) ($changed[$key][$months[6]]) : '',
              7 => ($changed[$key][$months[7]]) != NULL ? (int) ($changed[$key][$months[7]]) : '',
              8 => ($changed[$key][$months[8]]) != NULL ? (int) ($changed[$key][$months[8]]) : '',
              9 => ($changed[$key][$months[9]]) != NULL ? (int) ($changed[$key][$months[9]]) : '',
              10 => ($changed[$key][$months[10]]) != NULL ? (int) ($changed[$key][$months[10]]) : '',
              11 => ($changed[$key][$months[11]]) != NULL ? (int) ($changed[$key][$months[11]]) : '',
              12 => ($changed[$key]['Year']) != NULL ? (int) ($changed[$key]['Year']) : '',
              13 => ($changed[$key]['Row']) != NULL ? (int) ($changed[$key]['Row']) : '',
              14 => ($changed[$key]['Table']) != NULL ? (int) ($changed[$key]['Table']) : '',
              15 => ($changed[$key]['id']),
            ];
          }
        }
        $values[$j] = $value;
        $value = [];
      }
      // Storing values about number of years and tables.
      $this->tablecount = count($values);
      $this->track = count($values[1]);
    }
    // Checking if some triggering element was used,
    // if yes, and value of this element equal to - Add Year or Add Table,
    // increment number of Years or Tables accordingly.
    if (isset($form_state->getUserInput()['_triggering_element_value'])) {
      $this->checkTablesNumber($form_state->getUserInput());
      $this->checkYearsNumber($form_state->getUserInput());
    }
    // Building form with wrapper,
    // in order to create possibility of ajax reload, by reloading wrapper.
    $form['wrapper'] = [
      '#type' => 'container',
      '#id' => 'data-wrapper',
    ];
    $form['wrapper']['actions']['add_row'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Year'),
      '#ajax' => [
        'callback' => '::addRowCallback',
        'wrapper' => 'data-wrapper',
      ],
    ];
    $form['wrapper']['actions']['remove_row'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove Year'),
      '#ajax' => [
        'callback' => '::removeRowCallback',
        'wrapper' => 'data-wrapper',
      ],
    ];
    $form['wrapper']['actions']['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#ajax' => [
        'callback' => '::addTableCallback',
        'wrapper' => 'data-wrapper',
      ],
    ];
    $form['wrapper']['actions']['remove_table'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove Table'),
      '#ajax' => [
        'callback' => '::removeTableCallback',
        'wrapper' => 'data-wrapper',
      ],
    ];
    $form['wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'wrapper' => 'data-wrapper',
      ],
    ];
    $headers = [
      t('Year'),
      t('Jan'),
      t('Feb'),
      t('Mar'),
      t('Q1'),
      t('Apr'),
      t('May'),
      t('Jun'),
      t('Q2'),
      t('Jul'),
      t('Aug'),
      t('Sep'),
      t('Q3'),
      t('Oct'),
      t('Nov'),
      t('Dec'),
      t('Q4'),
      t('YTD'),
    ];
    // Building form with indexes that equal to Year and Table.
    for ($j = 1; $j <= $this->tablecount; $j++) {
      for ($i = ($this->track - 1); $i >= 0; $i--) {

        $form['wrapper']["table$j"] = [
          '#type' => 'table',
          '#header' => $headers,
        ];
        $form['wrapper']["Year$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($values[$j][$i][12])
          ? $values[$j][$i][12] : date('Y', time()) - $i,
          '#disabled' => TRUE,
        ];
        $form['wrapper']["$months[0]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[0]$j$i"]) ?
          ($form_state->getUserInput()["$months[0]$j$i"]) : (!isset($values[$j][$i][0]) ? '' : $values[$j][$i][0]),
        ];
        $form['wrapper']["$months[1]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[1]$j$i"]) ?
          ($form_state->getUserInput()["$months[1]$j$i"]) : (!isset($values[$j][$i][1]) ? '' : $values[$j][$i][1]),
        ];
        $form['wrapper']["Mar$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[2]$j$i"]) ?
          ($form_state->getUserInput()["$months[2]$j$i"]) : (!isset($values[$j][$i][2]) ? '' : $values[$j][$i][2]),
        ];
        // Checking if there was some user inputs for calculations.
        $user_inputs = NULL;
        if ($form_state->getUserInput() != NULL) {
          $user_inputs = $form_state->getUserInput();
        }
        // Validating all values that was put by user.
        $this->validateTable($form, $form_state);
        $quarter = [NULL, NULL, NULL, NULL, NULL];
        // If validation was successfull than do calculations.
        if ($this->messenger()->all() != NULL) {
          $quarter = $this->quarterCount($user_inputs, $j, $i);
        }
        $form['wrapper']["Q1$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#step' => '.01',
          '#default_value' => !isset($quarter[0]) ? '' : $quarter[0],
          '#disabled' => TRUE,
          '#required' => FALSE,
        ];
        $form['wrapper']["$months[3]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[3]$j$i"]) ?
          ($form_state->getUserInput()["$months[3]$j$i"]) : (!isset($values[$j][$i][3]) ? '' : $values[$j][$i][3]),
        ];
        $form['wrapper']["$months[4]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[4]$i"]) ?
          ($form_state->getUserInput()["$months[4]$i"]) : (!isset($values[$j][$i][4]) ? '' : $values[$j][$i][4]),
        ];
        $form['wrapper']["$months[5]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[5]$j$i"]) ?
          ($form_state->getUserInput()["$months[5]$j$i"]) : (!isset($values[$j][$i][5]) ? '' : $values[$j][$i][5]),
        ];
        $form['wrapper']["Q2$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#step' => '.01',
          '#default_value' => !isset($quarter[1]) ? '' : $quarter[1],
          '#disabled' => TRUE,
          '#required' => FALSE,
        ];
        $form['wrapper']["$months[6]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[6]$j$i"])
          ? ($form_state->getUserInput()["$months[6]$j$i"]) : (!isset($values[$j][$i][6]) ? '' : $values[$j][$i][6]),
        ];
        $form['wrapper']["$months[7]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[7]$j$i"])
          ? ($form_state->getUserInput()["$months[7]$j$i"]) : (!isset($values[$j][$i][7]) ? '' : $values[$j][$i][7]),
        ];
        $form['wrapper']["$months[8]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[8]$j$i"])
          ? ($form_state->getUserInput()["$months[8]$j$i"]) : (!isset($values[$j][$i][8]) ? '' : $values[$j][$i][8]),
        ];
        $form['wrapper']["Q3$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#step' => '.01',
          '#default_value' => !isset($quarter[2]) ? '' : $quarter[2],
          '#disabled' => TRUE,
          '#required' => FALSE,
        ];
        $form['wrapper']["$months[9]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[9]$j$i"])
          ? ($form_state->getUserInput()["$months[9]$j$i"]) : (!isset($values[$j][$i][9]) ? '' : $values[$j][$i][9]),
        ];
        $form['wrapper']["$months[10]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[10]$j$i"])
          ? ($form_state->getUserInput()["$months[10]$j$i"]) : (!isset($values[$j][$i][10]) ? '' : $values[$j][$i][10]),
        ];
        $form['wrapper']["$months[11]$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#default_value' => isset($form_state->getUserInput()["$months[11]$j$i"])
          ? ($form_state->getUserInput()["$months[11]$j$i"]) : (!isset($values[$j][$i][11]) ? '' : $values[$j][$i][11]),
        ];
        $form['wrapper']["Q4$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#step' => '.01',
          '#default_value' => !isset($quarter[3]) ? '' : $quarter[3],
          '#disabled' => TRUE,
          '#required' => FALSE,
        ];
        $form['wrapper']["YTD$j$i"] = [
          '#title' => '',
          '#type' => 'number',
          '#step' => '.01',
          '#default_value' => $quarter[4],
          '#disabled' => TRUE,
          '#required' => FALSE,
        ];
        $form['wrapper']["Row$j$i"] = [
          '#type' => 'hidden',
          '#default_value' => (!isset($values[$j][$i][13]) ? $i : $values[$j][$i][13]),
        ];
        $form['wrapper']["Table$j$i"] = [
          '#type' => 'hidden',
          '#default_value' => (!isset($values[$j][$i][14]) ? $j : $values[$j][$i][14]),
        ];
        $form['wrapper']["id$j$i"] = [
          '#type' => 'hidden',
          '#default_value' => (isset($values[$j][$i][15])) ? ($values[$j][$i][15]) : '',
        ];
      }
    }
    // If number of years equal to 1,
    // than change global year tracking variable,
    // in order to correct save values.
    if ($this->track == 1 && isset($form_state->getUserInput()['_triggering_element_value']) == "Remove Year"
      && $values[$this->track][$this->track] == NULL) {
      $this->track = 0;
    }
    $content['message'] = [
      '#markup' => $this->t('You can use this table below, in order to manage your accounting operations.'),
    ];
    $form['wrapper']['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
    ];
    // Attaching library with css code and class to render array.
    $form['wrapper']['#attached']['library'][] = 'nomad/quarter-style';
    $form['wrapper']['#attributes']['class'][] = 'accounting_table';
    return $form;
  }

  /**
   * Using standard structure of build form to create validation.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validation function callback, that was made in order to
    // validate values entered beyond validate form function.
    $this->validateTable($form, $form_state);
  }

  /**
   * Validation made in separated form for comfortable usage.
   */
  public function validateTable(array &$form, FormStateInterface $form_state) {
    // Check if current triggering element equal to submit.
    if (isset($form_state->getUserInput()['_triggering_element_value'])
      && $form_state->getUserInput()['_triggering_element_value'] === "Submit"
      && $form_state->getUserInput()['_triggering_element_value'] !== "Remover Year"
      && $form_state->getUserInput()['_triggering_element_value'] !== "Add Year"
      && $form_state->getUserInput()['_triggering_element_value'] !== "Add Table"
      && $form_state->getUserInput()['_triggering_element_value'] !== "Remove Table") {
      $tables_array = [];
      $year = $this->track;
      if ($this->track == 0) {
        $year = 1;
      }
      $months = $this->globalMonths;
      // Load all user input values from each table, and change it to:
      // simple 0 - if empty and 1 - if not empty.
      for ($j = $this->tablecount; $j > 0; $j--) {
        $years_array = [];
        for ($i = $year - 1; $i >= 0; $i--) {
          for ($y = 0; $y <= 11; $y++) {
            $table_cell_value = isset($form_state->getUserInput()["$months[$y]$j$i"])
              ? ($form_state->getUserInput()["$months[$y]$j$i"]) : (!isset($values[$j][$i][$y]) ? '' : $values[$j][$i][$y]);
            $cell_value = $table_cell_value == '' ? 0 : 1;
            array_push($years_array, $cell_value);
          }
        }
        $transitions = 0;
        // Catching all transition during each table.
        foreach ($years_array as $key => $value) {
          if ($key != 0) {
            $a = $years_array[$key - 1];
            $b = $value;
            if ($a != $b) {
              $transitions++;
            }
          }
        }
        // If table starts from zero than number of
        // transitions cannot be higher than 2, and if table starts from 1,
        // than number of zero can be equal maximum to 1.
        // If any condition is TRUE than display error.
        if ($years_array[0] == 0 && $transitions > 2) {
          $form_state->setError($form, t('Invalid'));
        }
        if ($years_array[0] == 1 && $transitions > 1) {
          $form_state->setError($form, t('Invalid'));
        }
        array_push($tables_array, $years_array);
      }
      // If table count is more than 1, than need to compare
      // tables for same cells are to be filled if not display error.
      if (count($tables_array) > 1) {
        foreach ($tables_array as $key => $item) {
          if ($key != 0) {
            $x = $tables_array[0];
            $z = $item;
            if ($x !== $z) {
              $form_state->setError($form, t('Invalid'));
            }
          }
        }
      }
      // If all conditions was False, than display status message Valid.
      if ($form_state->getErrors() == FALSE) {
        $this->messenger()->addStatus($this->t('Valid.'));
      }
    }
  }

  /**
   * Incrementing number of years by 1 and add default data to database.
   */
  public function addRowCallback(array &$form, FormStateInterface $form_state) {
    // Load global variables with months and year count.
    $months = $this->globalMonths;
    $year = $this->track - 1;
    // Go through all years and create 1 more row for each in database.
    for ($j = 1; $j <= $this->tablecount; $j++) {
      $data = \Drupal::service('database')->insert('nomad')
        ->fields([
          'Year' => (int) ($form_state->getValue("Year$j$year")),
          'Row' => (int) ($form_state->getValue("Row$j$year")),
          'Table' => (int) ($form_state->getValue("Table$j$year")),
          "$months[0]" => NULL,
          "$months[1]" => NULL,
          "$months[2]" => NULL,
          "$months[3]" => NULL,
          "$months[4]" => NULL,
          "$months[5]" => NULL,
          "$months[6]" => NULL,
          "$months[7]" => NULL,
          "$months[8]" => NULL,
          "$months[9]" => NULL,
          "$months[10]" => NULL,
          "$months[11]" => NULL,
        ])
        ->execute();
    }
    // Rebuild form incremented by 1 year.
    return $form['wrapper'];
  }

  /**
   * Decrementing number of years by 1 and remove it data from database.
   */
  public function removeRowCallback(array &$form, FormStateInterface $form_state) {
    // Go through all years and remove 1 year from each row in database.
    if (isset($this->track)) {
      for ($j = 1; $j <= $this->tablecount; $j++) {
        $query = \Drupal::database()->delete('nomad');
        $query->condition('Row', $this->track);
        $query->execute();

      }
    }
    // Rebuild form decremented by 1 year.
    return $form['wrapper'];
  }

  /**
   * Incrementing number of tables by 1 and add default data to database.
   */
  public function addTableCallback(array &$form, FormStateInterface $form_state) {
    // Load global variables with months and year count.
    $months = $this->globalMonths;
    // Add correction if number of years equal to zero.
    if ($this->track == 0) {
      $this->track++;
    }
    // Add table and exact same number of years for new table,
    // as it has been in first table.
    for ($j = $this->tablecount; $j == $this->tablecount; $j++) {
      for ($i = 0; $i < $this->track; $i++) {
        $data = \Drupal::service('database')->insert('nomad')
          ->fields([
            'Year' => (int) ($form_state->getValue("Year$j$i")),
            'Row' => $i,
            'Table' => (int) ($form_state->getValue("Table$j$i")),
            "$months[0]" => NULL,
            "$months[1]" => NULL,
            "$months[2]" => NULL,
            "$months[3]" => NULL,
            "$months[4]" => NULL,
            "$months[5]" => NULL,
            "$months[6]" => NULL,
            "$months[7]" => NULL,
            "$months[8]" => NULL,
            "$months[9]" => NULL,
            "$months[10]" => NULL,
            "$months[11]" => NULL,
          ])
          ->execute();
      }
    }
    // Rebuild table with new table with all years.
    $form_state->setRebuild();
    return $form['wrapper'];
  }

  /**
   * Searching for id of right table row from database output.
   */
  public function searchForId($j, $i, $array) {
    // Returns key in order to build right structured table.
    foreach ($array as $key => $val) {
      if ($val['Table'] == $j && $val['Row'] == $i) {
        return $key;
      }
    }
  }

  /**
   * Searching for right number of rows to build.
   */
  public function checkYearsNumber($array) {
    // Conditions that changing value of year count,
    // by used triggering element.
    if ($array['_triggering_element_value'] == "Add Year") {
      $this->track++;
    }
    if ($array['_triggering_element_value'] == "Remove Year" && $this->track > 1) {
      $this->track--;
    }
  }

  /**
   * Searching for right number of tables to build.
   */
  public function checkTablesNumber($array) {
    // Conditions that changing value of table count,
    // by used triggering element.
    if ($array['_triggering_element_value'] == "Add Table") {
      $this->tablecount++;
    }
    if ($array['_triggering_element_value'] == "Remove Table" && $this->tablecount > 1) {
      $this->tablecount--;
    }
  }

  /**
   * Making calculations for the quarters and ytd, based on user input data.
   */
  public function quarterCount($f, $j, $i) {
    // Calculating function that takes all user inputs and count it by formulas.
    // Each month build in loop by stored month via index as iterator, and
    // checking all user input values.
    $months = $this->globalMonths;
    $month = [];
    $quarter_result = [];
    for ($iteration = 0; $iteration < 12; $iteration++) {
      ($f != NULL && $f["$months[$iteration]$j$i"] != NULL && $f["$months[$iteration]$j$i"] != '')
        ? ($month_result = (int) $f["$months[$iteration]$j$i"]) : $month_result = 0;
      array_push($month, $month_result);
      if (count($month) == 3) {
        $quarter = (((int) $month[0] + (int) $month[1] + (int) $month[2]) + 1) / 3;
        $quarter = round($quarter, 2);
        $quarter = ($month[0] + $month[1] + $month[2]) != 0 ? $quarter : NULL;
        array_push($quarter_result, $quarter);
        $month = [];
      }
    }
    $ytd = (($quarter_result[0] + $quarter_result[1] + $quarter_result[2] + $quarter_result[3]) + 1) / 4;
    $ytd = round($ytd, 2);
    $ytd = ($quarter_result[0] + $quarter_result[1] + $quarter_result[2] + $quarter_result[3]) != 0 ? $ytd : NULL;
    array_push($quarter_result, $ytd);
    return $quarter_result;
  }

  /**
   * Decrementing number of tables by 1 and remove it data from database.
   */
  public function removeTableCallback(array &$form, FormStateInterface $form_state) {
    // Removing all rows from database that stored index of last table.
    if ($this->tablecount > 0) {
      $query = \Drupal::database()->delete('nomad');
      $query->condition('Table', $this->tablecount + 1);
      $query->execute();
    }
    return $form['wrapper'];
  }

  /**
   * Adding ajax to form submit.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    return $form['wrapper'];
  }

  /**
   * Adding form submit according to build_form structure.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Load global variables with months and database output.
    $months = $this->globalMonths;
    $output = $this->database;
    $changed = json_decode(json_encode($output), TRUE);
    // If database output is empty than load first row with current year,
    // if there is some output than only update it.
    if ($output != NULL && isset($form_state->getUserInput()['_triggering_element_value'])
      && $form_state->getUserInput()['_triggering_element_value'] != "Remove Year") {
      for ($j = $this->tablecount; $j > 0; $j--) {
        for ($i = 0; $i <= $this->track; $i++) {
          $key = $this->searchForId($j, $i, $changed);
          $query = \Drupal::database()->update('nomad')
            ->fields([
              'Year' => $form_state->getValue("Year$j$i"),
              'Row' => $form_state->getValue("Row$j$i"),
              'Table' => $form_state->getValue("Table$j$i"),
              "$months[0]" => ($form_state->getValue("$months[0]$j$i")) != '' ? $form_state->getValue("$months[0]$j$i") : NULL,
              "$months[1]" => ($form_state->getValue("$months[1]$j$i")) != '' ? $form_state->getValue("$months[1]$j$i") : NULL,
              "$months[2]" => ($form_state->getValue("$months[2]$j$i")) != '' ? $form_state->getValue("$months[2]$j$i") : NULL,
              "$months[3]" => ($form_state->getValue("$months[3]$j$i")) != '' ? $form_state->getValue("$months[3]$j$i") : NULL,
              "$months[4]" => ($form_state->getValue("$months[4]$j$i")) != '' ? $form_state->getValue("$months[4]$j$i") : NULL,
              "$months[5]" => ($form_state->getValue("$months[5]$j$i")) != '' ? $form_state->getValue("$months[5]$j$i") : NULL,
              "$months[6]" => ($form_state->getValue("$months[6]$j$i")) != '' ? $form_state->getValue("$months[6]$j$i") : NULL,
              "$months[7]" => ($form_state->getValue("$months[7]$j$i")) != '' ? $form_state->getValue("$months[7]$j$i") : NULL,
              "$months[8]" => ($form_state->getValue("$months[8]$j$i")) != '' ? $form_state->getValue("$months[8]$j$i") : NULL,
              "$months[9]" => ($form_state->getValue("$months[9]$j$i")) != '' ? $form_state->getValue("$months[9]$j$i") : NULL,
              "$months[10]" => ($form_state->getValue("$months[10]$j$i")) != '' ? $form_state->getValue("$months[10]$j$i") : NULL,
              "$months[11]" => ($form_state->getValue("$months[11]$j$i")) != '' ? $form_state->getValue("$months[11]$j$i") : NULL,
            ]);
          $query->condition("id", $changed[$key]['id']);
          $query->execute();
        }
      }
    }
    if ($output == NULL && isset($form_state->getUserInput()['_triggering_element_value']) == "Submit"
      || $output == NULL &&  isset($form_state->getUserInput()['_triggering_element_value']) == "Add Table") {
      $j = 1;
      $i = 0;
      $data = \Drupal::service('database')->insert('nomad')
        ->fields([
          'Year' => $form_state->getValue("Year$j$i"),
          'Row' => $form_state->getValue("Row$j$i"),
          'Table' => $form_state->getValue("Table$j$i"),
          "$months[0]" => ($form_state->getValue("$months[0]$j$i")) != '' ? $form_state->getValue("$months[0]$j$i") : NULL,
          "$months[1]" => ($form_state->getValue("$months[1]$j$i")) != '' ? $form_state->getValue("$months[1]$j$i") : NULL,
          "$months[2]" => ($form_state->getValue("$months[2]$j$i")) != '' ? $form_state->getValue("$months[2]$j$i") : NULL,
          "$months[3]" => ($form_state->getValue("$months[3]$j$i")) != '' ? $form_state->getValue("$months[3]$j$i") : NULL,
          "$months[4]" => ($form_state->getValue("$months[4]$j$i")) != '' ? $form_state->getValue("$months[4]$j$i") : NULL,
          "$months[5]" => ($form_state->getValue("$months[5]$j$i")) != '' ? $form_state->getValue("$months[5]$j$i") : NULL,
          "$months[6]" => ($form_state->getValue("$months[6]$j$i")) != '' ? $form_state->getValue("$months[6]$j$i") : NULL,
          "$months[7]" => ($form_state->getValue("$months[7]$j$i")) != '' ? $form_state->getValue("$months[7]$j$i") : NULL,
          "$months[8]" => ($form_state->getValue("$months[8]$j$i")) != '' ? $form_state->getValue("$months[8]$j$i") : NULL,
          "$months[9]" => ($form_state->getValue("$months[9]$j$i")) != '' ? $form_state->getValue("$months[9]$j$i") : NULL,
          "$months[10]" => ($form_state->getValue("$months[10]$j$i")) != '' ? $form_state->getValue("$months[10]$j$i") : NULL,
          "$months[11]" => ($form_state->getValue("$months[11]$j$i")) != '' ? $form_state->getValue("$months[11]$j$i") : NULL,
        ])
        ->execute();
    }
    return $form['wrapper'];
  }

}
