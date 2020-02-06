<?php

namespace Zeus\GravityForms;

use GFAPI;
use Timber\User;

/**
 * Class GravityForms
 * @package Zeus\Wordpress
 */
class GravityForms
{
    /**
     * GravityForms constructor.
     */
    public function __construct()
    {
        add_filter('pre_option_rg_gforms_disable_css', '__return_true');
        add_filter('gform_pre_render', [$this, 'amendFormFields']);
        add_filter('gform_submit_button', [$this, 'changeSubmitButton'], 10, 2);
        add_filter('gform_confirmation', [$this, 'pushSubmissionGtm'], 10, 4);
        add_filter('gform_notification_3', [$this, 'setEmailToAddress'], 10, 3);
        add_action('gform_pre_submission_3', [$this, 'addExpertDetails'], 10, 1);
        add_action('gform_after_submission_6', [$this, 'createQuestion'], 10, 2);
        add_filter('gform_entry_field_value', [$this, 'fieldDisplaySerialisedData'], 10, 4);
        add_filter('gform_merge_tag_filter', [$this, 'mergeTagDisplaySerialisedData'], 10, 4);
        add_filter('gform_export_field_value', [$this, 'exportDisplaySerialisedData'], 10, 4);
        add_filter('gform_field_value_name', [$this, 'populateName']);
        add_filter('gform_field_value_email', [$this, 'populateEmail']);
        add_filter('gform_field_value_phone', [$this, 'populatePhone']);
        add_filter('gform_field_value_company', [$this, 'populateCompany']);
    }

    /**
     * @return array
     */
    public static function listGravityForms()
    {
        $forms = GFAPI::get_forms();
        $results = [];
        foreach ($forms as $form) {
            $results[$form['id']] = $form['title'];
        }

        return $results;
    }

    /**
     * Amend form fields for all forms
     *
     * @param $form
     * @return mixed
     */
    public function amendFormFields($form)
    {
        foreach ($form['fields'] as &$field) {
            $field->size = $field->size ?? 'large';
            $field['cssClass'] .= " {$field->type}_wrapper";
            $field['cssClass'] .= " size_{$field->size}";
        }

        return $form;
    }

    /**
     * Amend submit button to be a button rather than input
     *
     * @param $button
     * @param $form
     * @return mixed
     */
    public function changeSubmitButton($button, $form)
    {
        $button = str_replace('input', 'button', $button);
        $button = str_replace('/', '', $button);
        $button .= "{$form['button']['text']}</button>";

        return $button;
    }

    /**
     * Pushes submissions from gravity forms to the data layer
     *
     * @param $confirmation
     * @param $form
     * @param $entry
     * @param $ajax
     * @return string
     */
    public function pushSubmissionGtm($confirmation, $form, $entry, $ajax)
    {
        $confirmation .=
        "<script>" .
        "    window.dataLayer = window.dataLayer || [];" .
        "    window.dataLayer.push({" .
        "        'event': 'gravityFormSubmitted'," .
        "        'formId': '" . $form['id'] . "'" .
        "    });" .
        "</script>";

        return $confirmation;
    }

    /**
     * @param $id
     * @param $data
     * @return array|\WP_Error
     */
    public static function submitForm($id, $data)
    {
        if ($form = GFAPI::get_form($id)) {
            $form_values = $data;

            $input_fields = [];
            foreach ($form['fields'] as $field) {
                $label = str_replace(' ', '_', strtolower($field->label));
                if (isset($form_values[$label])) {
                    $input_fields['input_' . $field['id']] = $form_values[$label];
                }
            }

            return GFAPI::submit_form($id, $input_fields);
        }
    }

    /**
     * @param $notification
     * @param $form
     * @param $entry
     * @return mixed
     */
    public function setEmailToAddress($notification, $form, $entry)
    {
        if ('Admin Notification' === $notification['name']) {
            //get email address value
            $expert = new User(rgar($entry, '5'));

            if (!empty($notification['to'])) {
                $notification['to'] .= ',';
            }

            $notification['to'] .= $expert->user_email;
        }

        //return altered notification object
        return $notification;
    }

    /**
     * @param $form
     */
    public function addExpertDetails($form)
    {
        $expert = new User(rgpost('input_5'));
        $_POST['input_6'] = $expert->first_name . ' ' . $expert->last_name;
        $_POST['input_7'] = $expert->user_email;
        $_POST['input_8'] = $expert->job_title;
    }

    /**
     * @param $entry
     * @param $form
     */
    public function createQuestion($entry, $form)
    {
        $product = get_post($entry[6]);

        $postarr = [
            'post_title'  => $entry[3],
            'post_status' => 'draft',
            'post_type'   => 'quick-questions'
        ];

        $id = wp_insert_post($postarr);

        update_field('related_products', $product, $id);
        update_field('person_email', $entry[1], $id);
        update_field('person', $entry[9], $id);
        update_field('location', $entry[7], $id);
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function outputFieldData($key, $value)
    {
        if (is_numeric($key)) {
            $label = 'Item #' . $key;
        } else {
            $label = preg_replace('/(?<=\\w)(?=[A-Z]|[0-9])/', " $1", $key);
            $label = ucwords($label);
        }

        return '<div><strong>' . $label . '</strong>: ' . $value . '</div>';
    }

    /**
     * @param $values
     * @param $disallowFields
     * @return mixed
     */
    public function formatSerialisedData($values, $disallowFields = [])
    {
        $values = unserialize(html_entity_decode($values));
        $output = '';

        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $output .= '<div style="margin-bottom: 20px;">';
                    foreach ($value as $key2 => $value2) {
                        if (!is_array($value2) && !is_object($value2) && !in_array($key2, $disallowFields)) {
                            $output .= $this->outputFieldData($key2, $value2);
                        }
                    }
                    $output .= '</div>';

                } else if (!is_object($value) && !in_array($key, $disallowFields)) {
                    $output .= '<div style="margin-bottom: 20px;">';
                    $output .= $this->outputFieldData($key, $value);
                    $output .= '</div>';
                }
            }
        }

        return $output;
    }

    /**
     * Make sure any serialised fields are serialised before being displayed
     *
     * @param $value
     * @param $field
     * @param $lead
     * @param $form
     * @return false|string
     */
    public function fieldDisplaySerialisedData($value, $field, $lead, $form)
    {
        if (is_serialized($value)) {
            $value = $this->formatSerialisedData($value);
        }

        return $value;
    }

    /**
     * Make sure any serialised fields are serialised before being displayed
     * when used in a merge tag
     *
     * @param $value
     * @param $merge_tag
     * @param $modifier
     * @param $field
     * @return string
     */
    public function mergeTagDisplaySerialisedData($value, $merge_tag, $modifier, $field)
    {
        if (is_serialized($value)) {
            $disallowFields = [];

            // Cable Gen and Samples certain basket fields should not be shown
            if (4 === intval($field->formId)) {
                $disallowFields = ['cable', 'cableUnit', 'cableLength', 'cableLengthRounded', 'cableLengthInches', 'cableDetails', 'cableLengthRounded', 'wireTotal', 'total'];
            } else if (5 === intval($field->formId)) {
                $disallowFields = ['id'];
            }

            $value = $this->formatSerialisedData($value, $disallowFields);
        }

        return $value;
    }

    /**
     * Make sure any serialised fields are serialised before being displayed
     * when exporting
     *
     * @param $value
     * @param $form_id
     * @param $field_id
     * @param $entry
     * @return string
     */
    public function exportDisplaySerialisedData($value, $form_id, $field_id, $entry)
    {
        if (is_serialized($value)) {
            $value = $this->formatSerialisedData($value);
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function populateName($value)
    {
        if ($user = wp_get_current_user()) {
            $value = $user->first_name . ' ' . $user->last_name;
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function populateEmail($value)
    {
        if ($user = wp_get_current_user()) {
            $value = $user->user_email;
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function populatePhone($value)
    {
        if ($userId = get_current_user_id()) {
            $value = get_user_meta($userId, 'phone_number', true);
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function populateCompany($value)
    {
        if ($userId = get_current_user_id()) {
            $value = get_user_meta($userId, 'company', true);
        }

        return $value;
    }
}
