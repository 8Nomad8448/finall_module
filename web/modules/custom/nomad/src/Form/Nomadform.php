<?php

namespace Drupal\nomad\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contains form created in order to create list of gests, that leave comments.
 */
class Nomadform extends FormBase {

  /**
   * Contains form created in order to create list of gests.
   */
  public function getFormId() {
    return 'nomad_name_form';
  }

  /**
   * Using build form function to create.
   */
  protected $dbinsert;

  /**
   * Using build form function to create.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $year_date = date('Y', time());
    $year_date = (int) $year_date;

    $year_count = [];
    $content = [];
    $value = [];
    $db = \Drupal::service('database');
    $select = $db->select('nomad', 'r');
    $select->fields('r', ['Year', 'Jan', 'Feb', 'Mar',
      'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
      'Oct', 'Nov', 'Dec',
      'id',
    ]);
    $select->orderBy('id', 'DESC');
    $output = $select->execute()->fetchall();

    $num_of_rows = $form_state->get('num_of_rows');
    if (empty($num_of_rows)) {
      $num_of_rows = 0;
    }
    if ($output != NULL) {
      $changed = json_decode(json_encode($output), TRUE);
      foreach ($changed as $key => $year) {
        array_push($value, $year['Year']);
        $values[$key] = [
          0 => ($year['Jan']) != NULL ? (int) ($year['Jan']) : ($year['Jan']),
          1 => ($year['Feb']) != NULL ? (int) ($year['Feb']) : ($year['Feb']),
          2 => ($year['Mar']) != NULL ? (int) ($year['Mar']) : ($year['Mar']),
          3 => ($year['Apr']) != NULL ? (int) ($year['Apr']) : ($year['Apr']),
          4 => ($year['May']) != NULL ? (int) ($year['May']) : ($year['May']),
          5 => ($year['Jun']) != NULL ? (int) ($year['Jun']) : ($year['Jun']),
          6 => ($year['Jul']) != NULL ? (int) ($year['Jul']) : ($year['Jul']),
          7 => ($year['Aug']) != NULL ? (int) ($year['Aug']) : ($year['Aug']),
          8 => ($year['Sep']) != NULL ? (int) ($year['Sep']) : ($year['Sep']),
          9 => ($year['Oct']) != NULL ? (int) ($year['Oct']) : ($year['Oct']),
          10 => ($year['Nov']) != NULL ? (int) ($year['Nov']) : ($year['Nov']),
          11 => ($year['Dec']) != NULL ? (int) ($year['Dec']) : ($year['Dec']),
          12 => ($year['Year']) != NULL ? (int) ($year['Year']) : ($year['Year']),
        ];
      }
    }
    $num_of_rows = count($value);
    $form_state->set('num_of_rows', $num_of_rows);
    if (empty($value)) {
      array_push($value, $year_date);
    }
    if (isset($form_state->getUserInput()['_triggering_element_value']) && ($form_state->getUserInput()['_triggering_element_value']) == "Add Year") {
      $add_year = $value[0] - 1;
      $value = array_reverse($value);
      array_push($value, $add_year);
      $value = array_reverse($value);
      $decrement_year = [
        0 => NULL,
        1 => NULL,
        2 => NULL,
        3 => NULL,
        4 => NULL,
        5 => NULL,
        6 => NULL,
        7 => NULL,
        8 => NULL,
        9 => NULL,
        10 => NULL,
        11 => NULL,
        12 => $add_year,
      ];
      $values = array_reverse($values);
      array_push($values, $decrement_year);
      $values = array_reverse($values);
    }
    $this->dbinsert = $value;
    if (isset($form_state->getUserInput()['_triggering_element_value']) && ($form_state->getUserInput()['_triggering_element_value']) == "Remove Year" && count($value) > 1) {
      $value = array_reverse($value);
      array_pop($value);
      $value = array_reverse($value);
    }
    $year_count = $value;
    $form['wrapper'] = [
      '#type' => 'container',
      '#id' => 'data-wrapper',
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

    for ($i = 0; $i < count($value); $i++) {
      $marker = 0;
      if (isset($this->dbinsert)) {
        $marker = $value[$i];
      }
      $jan = isset($values[$i][0]) != '' ? $values[$i][0] : 0;
      $feb = isset($values[$i][1]) != '' ? $values[$i][1] : 0;
      $mar = isset($values[$i][2]) != '' ? $values[$i][2] : 0;
      $qfirst = (($jan + $feb + $mar) + 1) / 3;
      $qfirst = round($qfirst, 2);
      $qfirst = ($jan + $feb + $mar) != 0 ? $qfirst : NULL;
      $apr = isset($values[$i][3]) != '' ? $values[$i][3] : 0;
      $may = isset($values[$i][4]) != '' ? $values[$i][4] : 0;
      $jun = isset($values[$i][5]) != '' ? $values[$i][5] : 0;
      $qsecond = (($apr + $may + $jun) + 1) / 3;
      $qsecond = round($qsecond, 2);
      $qsecond = ($apr + $may + $jun) != 0 ? $qsecond : NULL;
      $jul = isset($values[$i][6]) != '' ? $values[$i][6] : 0;
      $aug = isset($values[$i][7]) != '' ? $values[$i][7] : 0;
      $sep = isset($values[$i][8]) != '' ? $values[$i][8] : 0;
      $qthird = (($jul + $aug + $sep) + 1) / 3;
      $qthird = round($qthird, 2);
      $qthird = ($jul + $aug + $sep) != 0 ? $qthird : NULL;
      $oct = isset($values[$i][9]) != '' ? $values[$i][9] : 0;
      $nov = isset($values[$i][10]) != '' ? $values[$i][10] : 0;
      $dec = isset($values[$i][11]) != '' ? $values[$i][11] : 0;
      $qfourth = (($oct + $nov + $dec) + 1) / 3;
      $qfourth = round($qfourth, 2);
      $qfourth = ($oct + $nov + $dec) != 0 ? $qfourth : NULL;
      $ytd = (($qfirst + $qsecond + $qthird + $qfourth) + 1) / 4;
      $ytd = round($ytd, 2);
      $ytd = ($qfirst + $qsecond + $qthird + $qfourth) != 0 ? $ytd : NULL;

      $form['wrapper']['table'] = [
        '#type' => 'table',
        '#header' => $headers,
      ];
      $form['wrapper']["Year$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => isset($year_count) ? $year_count[$i] : $year_date - $i,
        '#disabled' => TRUE,
      ];
      $form['wrapper']["Jan$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][0]) ? '' : $values[$i][0],
      ];
      $form['wrapper']["Feb$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][1]) ? '' : $values[$i][1],
      ];
      $form['wrapper']["Mar$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][2]) ? '' : $values[$i][2],
      ];
      $form['wrapper']["Q1$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#step' => '.01',
        '#default_value' => !isset($qfirst) ? '' : $qfirst,
        '#disabled' => TRUE,
        '#required' => FALSE,
      ];
      $form['wrapper']["Apr$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][3]) ? '' : $values[$i][3],
      ];
      $form['wrapper']["May$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][4]) ? '' : $values[$i][4],
      ];
      $form['wrapper']["Jun$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][5]) ? '' : $values[$i][5],
      ];
      $form['wrapper']["Q2$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#step' => '.01',
        '#default_value' => !isset($qsecond) ? '' : $qsecond,
        '#disabled' => TRUE,
        '#required' => FALSE,
      ];
      $form['wrapper']["Jul$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][6]) ? '' : $values[$i][6],
      ];
      $form['wrapper']["Aug$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][7]) ? '' : $values[$i][7],
      ];
      $form['wrapper']["Sep$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][8]) ? '' : $values[$i][8],
      ];
      $form['wrapper']["Q3$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#step' => '.01',
        '#default_value' => !isset($qthird) ? '' : $qthird,
        '#disabled' => TRUE,
        '#required' => FALSE,
      ];
      $form['wrapper']["Oct$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][9]) ? '' : $values[$i][9],
      ];
      $form['wrapper']["Nov$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][10]) ? '' : $values[$i][10],
      ];
      $form['wrapper']["Dec$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#default_value' => !isset($values[$i][11]) ? '' : $values[$i][11],
      ];
      $form['wrapper']["Q4$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#step' => '.01',
        '#default_value' => !isset($qfourth) ? '' : $qfourth,
        '#disabled' => TRUE,
        '#required' => FALSE,
      ];
      $form['wrapper']["YTD$marker"] = [
        '#title' => '',
        '#type' => 'number',
        '#step' => '.01',
        '#default_value' => $ytd,
        '#disabled' => TRUE,
        '#required' => FALSE,
      ];
      $num_of_rows = count($year_count);
      $form_state->setValue('num_of_rows', $num_of_rows);
    }
    $content['message'] = [
      '#markup' => $this->t('You can use this table below, in order to manage your accounting operations.'),
    ];
    $form['wrapper']['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
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
    $form['wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'wrapper' => 'data-wrapper',
      ],
    ];
    $form['wrapper']['#attached']['library'][] = 'nomad/quarter-style';
    $form['wrapper']['#attributes']['class'][] = 'accounting_table';
    return $form;
  }

  /**
   * Using standart structure of build form to create validation.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Adding form submit according to build_form structure.
   */
  public function addRowCallback(array &$form, FormStateInterface $form_state) {
    $num_of_rows = $form_state->get('num_of_rows');
    if (!$num_of_rows == 0) {
      $data = \Drupal::service('database')->insert('nomad')
        ->fields([
          'Year' => ((int) (date("Y", time())) - $num_of_rows),
          'Jan' => NULL,
          'Feb' => NULL,
          'Mar' => NULL,
          'Apr' => NULL,
          'May' => NULL,
          'Jun' => NULL,
          'Jul' => NULL,
          'Aug' => NULL,
          'Sep' => NULL,
          'Oct' => NULL,
          'Nov' => NULL,
          'Dec' => NULL,
        ])
        ->execute();
      // Rebuild form with 1 extra row.
      $num_of_rows++;
      $form_state->setValue('num_of_rows', $num_of_rows);
    }
    return $form['wrapper'];
  }

  /**
   * Adding form submit according to build_form structure.
   */
  public function removeRowCallback(array &$form, FormStateInterface $form_state) {
    $num_of_rows = $form_state->getValue('num_of_rows');
    if ($this->dbinsert != NULL) {
      $query = \Drupal::database()->delete('nomad');
      $query->condition('Year', ($this->dbinsert)[0]);
      $query->execute();

      $num_of_rows--;
      $form_state->setValue('num_of_rows', $num_of_rows);
    }
    // Rebuild form decremented by 1 row.
    return $form['wrapper'];
  }

  /**
   * Adding form submit according to build_form structure.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    return $form['wrapper'];
  }

  /**
   * Adding form submit according to build_form structure.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $num_of_rows = $form_state->get('num_of_rows');
    $db = \Drupal::service('database');
    $select = $db->select('nomad', 'r');
    $select->fields('r', ['Year', 'Jan', 'Feb', 'Mar',
      'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
      'Oct', 'Nov', 'Dec',
      'id',
    ]);
    $select->orderBy('id', 'DESC');
    $output = $select->execute()->fetchall();
    if ($output != NULL && isset($form_state->getUserInput()['_triggering_element_value']) && $form_state->getUserInput()['_triggering_element_value'] != "Remove Year") {
      foreach ($this->dbinsert as $key => $value) {
        $query = \Drupal::database()->update('nomad')
          ->fields([
            'Year' => $form_state->getValue("Year$value"),
            'Jan' => ($form_state->getValue("Jan$value")) != '' ? $form_state->getValue("Jan$value") : NULL,
            'Feb' => ($form_state->getValue("Feb$value")) != '' ? $form_state->getValue("Feb$value") : NULL,
            'Mar' => ($form_state->getValue("Mar$value")) != '' ? $form_state->getValue("Mar$value") : NULL,
            'Apr' => ($form_state->getValue("Apr$value")) != '' ? $form_state->getValue("Apr$value") : NULL,
            'May' => ($form_state->getValue("May$value")) != '' ? $form_state->getValue("May$value") : NULL,
            'Jun' => ($form_state->getValue("Jun$value")) != '' ? $form_state->getValue("Jun$value") : NULL,
            'Jul' => ($form_state->getValue("Jul$value")) != '' ? $form_state->getValue("Jul$value") : NULL,
            'Aug' => ($form_state->getValue("Aug$value")) != '' ? $form_state->getValue("Aug$value") : NULL,
            'Sep' => ($form_state->getValue("Sep$value")) != '' ? $form_state->getValue("Sep$value") : NULL,
            'Oct' => ($form_state->getValue("Oct$value")) != '' ? $form_state->getValue("Oct$value") : NULL,
            'Nov' => ($form_state->getValue("Nov$value")) != '' ? $form_state->getValue("Nov$value") : NULL,
            'Dec' => ($form_state->getValue("Dec$value")) != '' ? $form_state->getValue("Dec$value") : NULL,
          ]);
        $query->condition('Year', $value);
        $query->execute();
      }
    }
    if (isset($form_state->getUserInput()['_triggering_element_value']) == "Submit" && ($output) == NULL) {
      $first_line = $this->dbinsert;
      $first_line = array_reverse($first_line);
      foreach ($first_line as $key => $value) {
        $data = \Drupal::service('database')->insert('nomad')
          ->fields([
            'Year' => $form_state->getValue("Year$value"),
            'Jan' => ($form_state->getValue("Jan$value")) != '' ? $form_state->getValue("Jan$value") : NULL,
            'Feb' => ($form_state->getValue("Feb$value")) != '' ? $form_state->getValue("Feb$value") : NULL,
            'Mar' => ($form_state->getValue("Mar$value")) != '' ? $form_state->getValue("Mar$value") : NULL,
            'Apr' => ($form_state->getValue("Apr$value")) != '' ? $form_state->getValue("Apr$value") : NULL,
            'May' => ($form_state->getValue("May$value")) != '' ? $form_state->getValue("May$value") : NULL,
            'Jun' => ($form_state->getValue("Jun$value")) != '' ? $form_state->getValue("Jun$value") : NULL,
            'Jul' => ($form_state->getValue("Jul$value")) != '' ? $form_state->getValue("Jul$value") : NULL,
            'Aug' => ($form_state->getValue("Aug$value")) != '' ? $form_state->getValue("Aug$value") : NULL,
            'Sep' => ($form_state->getValue("Sep$value")) != '' ? $form_state->getValue("Sep$value") : NULL,
            'Oct' => ($form_state->getValue("Oct$value")) != '' ? $form_state->getValue("Oct$value") : NULL,
            'Nov' => ($form_state->getValue("Nov$value")) != '' ? $form_state->getValue("Nov$value") : NULL,
            'Dec' => ($form_state->getValue("Dec$value")) != '' ? $form_state->getValue("Dec$value") : NULL,
          ])
          ->execute();
      }
    }
  }

}
