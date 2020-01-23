<?php

namespace Zeus\User;

use Zeus\IFS\HarwinCedarAPI;

/**
 * Class User
 * @package Zeus\User
 */
class UserUtility
{
    /**
     * Update User
     * @array $userData
     * @return response in JSON
     */
    public function updateUser($userData)
    {
        add_filter('send_password_change_email', '__return_false');

        $currentUser = wp_get_current_user();
        $email = sanitize_email($userData['email']);
        // Get user data
        $ifsClass = new HarwinCedarAPI();
        $result = $ifsClass->GetIdsForEmail($email);

        $parseData = json_decode($result);
        $customerId = $parseData->CustomerId;
        $userId = $currentUser->data->ID;

        $user = [];
        if ($parseData->LeadId != 'NULL') {
            $user['LeadId'] = $parseData->LeadId;
        } else {
            $user['LeadId'] = '';
        }

        $user['CompanyId'] = '';

        if (isset($userData['first-name']) && $userData['first-name'] != '') {
            $user['FirstName'] = sanitize_text_field($userData['first-name']);
        } else {
            $user['FirstName'] = '';
        }

        if (isset($userData['last-name']) && $userData['last-name'] != '') {
            $user['LastName'] = sanitize_text_field($userData['last-name']);
        } else {
            $user['LastName'] = '';
        }

        $user['OldEmail'] = $currentUser->user_email;

        if (isset($userData['email']) && $userData['email'] != '') {
            $user['NewEmail'] = sanitize_email($userData['email']);
        } else {
            $user['NewEmail'] = '';
        }

        if (isset($userData['company-name']) && $userData['company-name'] != '') {
            $user['Company'] = sanitize_text_field($userData['company-name']);
        } else {
            $user['Company'] = '';
        }

        if (isset($userData['country']) && $userData['country'] != '') {
            $user['Country'] = sanitize_text_field($userData['country']);
        } else {
            $user['Country'] = '';
        }

        // The distributor is only manually set via CMS
        $distributor = get_user_meta($userId, 'distributor', true);
        if ($distributor && $distributor != '') {
            $user['Distributor'] = 'Y';
        } else {
            $user['Distributor'] = 'N';
        }

        if (isset($userData['address1']) && $userData['address1'] != '') {
            $user['Address1'] = sanitize_text_field($userData['address1']);
        } else {
            $user['Address1'] = '';
        }

        if (isset($userData['address2']) && $userData['address2'] != '') {
            $user['Address2'] = sanitize_text_field($userData['address2']);
        } else {
            $user['Address2'] = '';
        }

        if (isset($userData['city']) && $userData['city'] != '') {
            $user['Town'] = sanitize_text_field($userData['city']);
        } else {
            $user['Town'] = '';
        }

        if (isset($userData['postcode']) && $userData['postcode'] != '') {
            $user['Postcode'] = sanitize_text_field($userData['postcode']);
        } else {
            $user['Postcode'] = '';
        }

        if (isset($userData['state']) && $userData['state'] != '') {
            $user['County'] = sanitize_text_field($userData['state']);
        } else {
            $user['County'] = '';
        }

        if (isset($userData['taxId']) && $userData['tax-id'] != '') {
            $user['TaxId'] = sanitize_text_field($userData['tax-id']);
        } else {
            $user['TaxId'] = '';
        }

        if (isset($userData['job-title']) && $userData['job-title'] != '') {
            $user['JobTitle'] = sanitize_text_field($userData['job-title']);
        } else {
            $user['JobTitle'] = '';
        }

        if (isset($userData['phone']) && $userData['phone'] != '') {
            $user['Phone'] = sanitize_text_field($userData['phone']);
        } else {
            $user['Phone'] = '';
        }

        if (isset($userData['market']) && $userData['market'] != '') {
            $user['Market'] = sanitize_text_field($userData['market']);
        } else {
            $user['Market'] = '';
        }

        update_user_meta($userId, 'first_name', $user['FirstName']);
        update_user_meta($userId, 'last_name', $user['LastName']);
        update_user_meta($userId, 'company', $user['Company']);
        update_user_meta($userId, 'phone_number', $user['Phone']);
        update_user_meta($userId, 'job_title', $user['JobTitle']);
        update_user_meta($userId, 'address_line_1', $user['Address1']);
        update_user_meta($userId, 'address_line_2', $user['Address2']);
        update_user_meta($userId, 'town_city', $user['Town']);
        update_user_meta($userId, 'county_state', $user['County']);
        update_user_meta($userId, 'country', $user['Country']);
        update_user_meta($userId, 'post_code_zip_code', $user['Postcode']);

        if ($user['NewEmail'] != '' && $user['FirstName'] != '' && $user['LastName'] != '' && $user['Company'] != '' && $user['Phone'] != '' && $user['JobTitle'] != '' && $user['Address1'] != '' && $user['Town'] != '' && $user['County'] != '' && $user['Country'] != '' && $user['Postcode'] != '') {
            update_user_meta($userId, 'complete_profile', 1);
        } else {
            update_user_meta($userId, 'complete_profile', 0);
        }

        $user['Country'] = UserUtility::getCountryISOByName($user['Country']);

        $ifsClass = new HarwinCedarAPI();
        $result = $ifsClass->updateUser($user);
        $parseData = json_decode($result);

        if (isset($parseData)) {
            if ($parseData->Result == 'true') {
                // TO DO // User update successfully
                // Redirected to user dashboard
                $response = [
                    'Result' => 'true',
                    'Message' => 'Update profile successfully.'
                ];
                return json_encode($response);
            } elseif ($parseData->Result == 'false') {
                // TO DO // User data not update
                // Redirected to update profile page
                $response = [
                    'Result' => 'false',
                    'Message' => 'Something went wrong.'
                ];
                return json_encode($response);
            }
        }
    }

    /**
     * Register User
     * @array $userData
     * @return response in JSON
     */
    public function createUser($userData)
    {
        include("./wp-admin/includes/user.php");

        add_filter('send_password_change_email', '__return_false');

        if ($userData['first-name'] == '' || $userData['last-name'] == '' || $userData['email'] == '' || $userData['company-name'] == '' || $userData['country'] == '') {
            $response = [
                'Result' => 'false',
                'Message' => 'Something went wrong.'
            ];
            return json_encode($response);
        }

        $user = [];
        $user['LeadId'] = '';
        $user['CompanyId'] = '';

        if (isset($userData['first-name']) && $userData['first-name'] != '') {
            $user['FirstName'] = sanitize_text_field($userData['first-name']);
        } else {
            $user['FirstName'] = '';
        }
        $_SESSION['userRegisterData']['firstname'] = $user['FirstName'];

        if (isset($userData['last-name']) && $userData['last-name'] != '') {
            $user['LastName'] = sanitize_text_field($userData['last-name']);
        } else {
            $user['LastName'] = '';
        }
        $_SESSION['userRegisterData']['lastname'] = $user['LastName'];

        if (isset($userData['email']) && $userData['email'] != '') {
            $user['Email'] = sanitize_email($userData['email']);
        } else {
            $user['Email'] = '';
        }
        $_SESSION['userRegisterData']['email'] = $user['Email'];

        if (isset($userData['company-name']) && $userData['company-name'] != '') {
            $user['Company'] = sanitize_text_field($userData['company-name']);
        } else {
            $user['Company'] = '';
        }
        $_SESSION['userRegisterData']['companyName'] = $user['Company'];

        if (isset($userData['country']) && $userData['country'] != '') {
            $user['Country'] = sanitize_text_field($userData['country']);
        } else {
            $user['Country'] = '';
        }
        $_SESSION['userRegisterData']['country'] = $user['Country'];

        $user['Distributor'] = 'N';

        if (isset($userData['address1']) && $userData['address1'] != '') {
            $user['Address1'] = sanitize_text_field($userData['address1']);
        } else {
            $user['Address1'] = '';
        }
        $_SESSION['userRegisterData']['address1'] = $user['Address1'];

        if (isset($userData['address2']) && $userData['address2'] != '') {
            $user['Address2'] = sanitize_text_field($userData['address2']);
        } else {
            $user['Address2'] = '';
        }
        $_SESSION['userRegisterData']['address2'] = $user['Address2'];

        if (isset($userData['city']) && $userData['city'] != '') {
            $user['Town'] = sanitize_text_field($userData['city']);
        } else {
            $user['Town'] = '';
        }
        $_SESSION['userRegisterData']['city'] = $user['Town'];

        if (isset($userData['postcode']) && $_POST['postcode'] != '') {
            $user['Postcode'] = sanitize_text_field($userData['postcode']);
        } else {
            $user['Postcode'] = '';
        }
        $_SESSION['userRegisterData']['postcode'] = $user['Postcode'];

        if (isset($userData['state']) && $userData['state'] != '') {
            $user['County'] = sanitize_text_field($userData['state']);
        } else {
            $user['County'] = '';
        }
        $_SESSION['userRegisterData']['county'] = $user['County'];

        if (isset($userData['taxId']) && $userData['tax-id'] != '') {
            $user['TaxId'] = sanitize_text_field($userData['tax-id']);
        } else {
            $user['TaxId'] = '';
        }
        $_SESSION['userRegisterData']['taxId'] = $user['TaxId'];

        if (isset($userData['job-title']) && $userData['job-title'] != '') {
            $user['JobTitle'] = sanitize_text_field($userData['job-title']);
        } else {
            $user['JobTitle'] = '';
        }
        $_SESSION['userRegisterData']['jobTitle'] = $user['JobTitle'];

        if (isset($userData['phone']) && $userData['phone'] != '') {
            $user['Phone'] = sanitize_text_field($userData['phone']);
        } else {
            $user['Phone'] = '';
        }
        $_SESSION['userRegisterData']['phone'] = $user['Phone'];

        if (isset($userData['market']) && $userData['market'] != '') {
            $user['Market'] = sanitize_text_field($userData['market']);
        } else {
            $user['Market'] = '';
        }
        $_SESSION['userRegisterData']['market'] = $user['Market'];

        // check user exist in CMS
        $cmsUser = email_exists($user['Email']);

        if (isset($userData['password']) && $userData['password'] != '') {
            $password = sanitize_text_field($userData['password']);
        } else {
            $password = '';
        }

        if (isset($userData['password-confirm']) && $userData['password-confirm'] != '') {
            $passwordConfirm = sanitize_text_field($userData['password-confirm']);
        } else {
            $passwordConfirm = '';
        }

        if ($password !== $passwordConfirm) {
            $result = 'false';
            $message = 'Passwords don\'t match.';
        } else if (isset($cmsUser) && $cmsUser != '') {
            $result = 'false';
            $message = 'User already exists.';

            $subject = 'You already have a MyHarwin Account';
            $name = $user['FirstName'];
            $passwordResetLink = wp_lostpassword_url();
            $contactLink = get_site_url(null, 'contact/');
            $body = <<< HTML
<h2>Hello $name,</h2>

We noticed that you tried to create a new MyHarwin account with this email address, but one already exists.

To reset your password, <a href="$passwordResetLink">click here</a>

If this wasn't you, please <a href="$contactLink">contact us</a> for assistance.

Team Harwin
HTML;
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            wp_mail($user['Email'], $subject, $body, $headers);

        } else {
            $userId = wp_create_user($user['Email'], $password, $user['Email']);

            wp_new_user_notification($userId, null, 'user');

            if ($userId) {
                // Set the nickname
                wp_update_user(
                    [
                        'ID' => $userId,
                        'user_nicename' => $user['FirstName'] . ' ' . $user['LastName'],
                        'display_name' => $user['FirstName'],
                        'role' => 'subscriber'
                    ]
                );

                update_user_meta($userId, 'first_name', $user['FirstName']);
                update_user_meta($userId, 'last_name', $user['LastName']);
                update_user_meta($userId, 'company', $user['Company']);
                update_user_meta($userId, 'phone_number', $user['Phone']);
                update_user_meta($userId, 'job_title', $user['JobTitle']);
                update_user_meta($userId, 'address_line_1', $user['Address1']);
                update_user_meta($userId, 'address_line_2', $user['Address2']);
                update_user_meta($userId, 'town_city', $user['Town']);
                update_user_meta($userId, 'county_state', $user['County']);
                update_user_meta($userId, 'country', $user['Country']);
                update_user_meta($userId, 'post_code_zip_code', $user['Postcode']);
                update_user_meta($userId, 'complete_profile', 0);

                // Send email to administrator to verify user is a distributor
                if (isset($userData['distributor']) && filter_var($userData['distributor'], FILTER_VALIDATE_BOOLEAN) && $to = get_field('distributor_request_emails', 'option')) {
                    $subject = 'MyHarwin Distributor Request for ' . $user['Company'];
                    $name = $user['FirstName'] . ' ' . $user['LastName'];
                    $email = $user['Email'];
                    $company = $user['Company'];
                    $editLink = admin_url('user-edit.php?user_id=' . $userId);
                    $body = <<< HTML
The following user $name ($email) has registered as the distributor $company.

Please review and assign the correct distributor <a href="$editLink">here</a> or copy and paste this link into your browser:
$editLink
HTML;
                    $headers = ['Content-Type: text/html; charset=UTF-8'];

                    wp_mail($to, $subject, $body, $headers);
                }

                $result = 'true';
                $message = 'User created successfully.';
            } else {
                $result = 'false';
                $message = 'Something went wrong.';
            }
        }

        $response = [
            'Result' => $result,
            'Message' => $message,
            'Data' => $user,
            'UserId' => $userId
        ];
        return json_encode($response);
    }

    public static function calculatePercentageComplete($user)
    {
        $completed = 0;

        $attributes = [
            'user_email',
            'first_name',
            'last_name',
            'company',
            'phone_number',
            'job_title',
            'address_line_1',
            'town_city',
            'county_state',
            'country',
            'post_code_zip_code',
        ];

        foreach ($attributes as $attribute) {
            if (property_exists($user, $attribute) && $user->$attribute != '') {
                $completed++;
            }
        }

        return round(($completed / count($attributes)) * 100);
    }

    /**
     * Returns the list of countries
     *
     * @return array
     */
    public static function countryList()
    {
        global $wpdb;
        $countries = [];

        $query = "
        SELECT
            countries.iso_code,
            countries.name
        FROM hw_countries countries ";

        $results = $wpdb->get_results($query, OBJECT);

        if (count($results) > 0) {
            foreach ($results as $result) {
                $countries[$result->name] = $result->name;
            }
        }

        $countries['Other (use State field)'] = 'Other (use State field)';

        return $countries;
    }

    /**
     * Returns the ISO code of a country by name
     *
     * @param string $name
     * @return string
     */
    public static function getCountryISOByName($name)
    {
        global $wpdb;

        $query = "
        SELECT
            countries.iso_code
        FROM hw_countries countries
        WHERE countries.name = %s ";

        $code = $wpdb->get_var($wpdb->prepare($query, [$name]));

        return $code ?? '';
    }

    /**
     * Returns the name code of a country by ISO code
     *
     * @param string $code
     * @return string
     */
    public static function getCountryNameByISO($code)
    {
        global $wpdb;

        $query = "
        SELECT
            countries.name
        FROM hw_countries countries
        WHERE countries.iso_code = %s ";

        $name = $wpdb->get_var($wpdb->prepare($query, [$code]));

        return $name ?? '';
    }

    /**
     * Update User
     * @array $userData
     */
    public function updateComms($postData)
    {
        $currentUser = wp_get_current_user();

        $userID = get_field('active_campaign_id', $currentUser);

        if (!array_key_exists('marketing-communication', $postData)) {
            $postData['marketing-communication'] = [];
        }

        if (null === get_field('active_campaign_id', $currentUser)) {
            // Create user in AC
            $data = [
                'email' => $currentUser->data->user_email,
                'first_name' => get_field('first_name', $currentUser),
                'last_name' => get_field('last_name', $currentUser),
            ];

            if (count($postData['marketing-communication']) > 0) {
                foreach ($postData['marketing-communication'] as $option) {
                    $data['p[' . $option . ']'] = $option;
                    $data['status[' . $option . ']'] = 1;
                    $data['instantresponders[' . $option . ']'] = 1;
                }
            }

            $acUser = $this->sendACRequest($data, 'contact_add');

            $userID = array_key_exists('subscriber_id', $acUser) ? $acUser['subscriber_id'] : null;

            update_user_meta($currentUser->ID, 'active_campaign_id', $userID);
            update_user_meta($currentUser->ID, 'subscribed', implode(',', $postData['marketing-communication']));
        } else {
            // Updates user in AC
            $data = [
                'id' => $userID,
                'email' => $currentUser->data->user_email,
                'first_name' => get_field('first_name', $currentUser),
                'last_name' => get_field('last_name', $currentUser),
            ];

            $currentLists = explode(',', get_field('subscribed', $currentUser));

            if (count($currentLists) > 0) {
                foreach ($currentLists as $list) {
                    if (!in_array($list, $postData['marketing-communication'])) {
                        $data['p[' . $list . ']'] = $list;
                        $data['status[' . $list . ']'] = 2;
                    }
                }
            }

            if (count($postData['marketing-communication']) > 0) {
                foreach ($postData['marketing-communication'] as $option) {
                    $data['p[' . $option . ']'] = $option;
                    $data['status[' . $option . ']'] = 1;
                    $data['instantresponders[' . $option . ']'] = 1;
                }
            }

            $acUser = $this->sendACRequest($data, 'contact_edit');

            $userID = array_key_exists('subscriber_id', $acUser) ? $acUser['subscriber_id'] : null;

            update_user_meta($currentUser->ID, 'active_campaign_id', $userID);
            update_user_meta($currentUser->ID, 'subscribed', implode(',', $postData['marketing-communication']));
        }

        return true;
    }

    /**
     * Call to api with request data and endpoint
     * TODO: tidy - this is example code...
     */
    private function sendACRequest($post, $endpoint)
    {
        $url = 'https://harwin.api-us1.com';

        $params = [
            'api_key' => '6a3b3381aa92bcd8b4e51b112e8c7b8f27452dce27b5d9d6723bc9eaca566b7672f96ec4',
            'api_action' => $endpoint,
            'api_output' => 'serialize',
        ];

        // This section takes the input fields and converts them to the proper format
        $query = "";
        foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
        $query = rtrim($query, '& ');

        // This section takes the input data and converts it to the proper format
       $data = "";
        foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
        $data = rtrim($data, '& ');

        // clean up the url
        $url = rtrim($url, '/ ');

        // This sample code uses the CURL library for php to establish a connection,
        // submit your request, and show (print out) the response.
        if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');

        // If JSON is used, check if json_decode is present (PHP 5.2.0+)
        if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
            die('JSON not supported. (introduced in PHP 5.2.0)');
        }

        // define a final API request - GET
        $api = $url . '/admin/api.php?' . $query;

        $request = curl_init($api); // initiate curl object
        curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data
        //curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

        $response = (string)curl_exec($request); // execute curl post and store results in $response

        // additional options may be required depending upon your server configuration
        // you can find documentation on curl options at http://www.php.net/curl_setopt
        curl_close($request); // close curl object

        if (!$response) {
            die('Nothing was returned. Do you have a connection to Email Marketing server?');
        }

        return unserialize($response);

    }
}
