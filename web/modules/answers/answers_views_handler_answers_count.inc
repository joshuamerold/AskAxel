<?php

/**
 * @file
 * Definition of views_handler_area_result.
 */

/**
 * Views area handler to display answers count.
 *
 * @ingroup views_area_handlers
 */
class AnswersCountViewsHandler extends views_handler_area {

  /**
   * Render the field.
   *
   * @param bool $empty
   *   There is no values .
   */
  public function render($empty = FALSE) {
    $output = '';
    $count = count($this->view->result);
    $total = isset($this->view->total_rows) ? $this->view->total_rows : count($this->view->result);

    switch ($total) {
      case 0:
        return t("No Answers yet.");

      case 1:
        return t("1 Answer");

      default:
        return $total . " " . t("Answers");

    }
  }

}
