<?php
/**
 *          RAFAEL FERREIRA © 2014 || MailChimp Form
 * ------------------------------------------------------------------------
 *                      ** MailChimp Class    **
 * ------------------------------------------------------------------------
 */
require_once("mailchimp.php");

class Model_MailChimp{
    public static function subscribe($email, $merge_vars) {
        $instance = new Mailchimp(get_theme_mod('mailchimp_api_key'));
        return $instance->lists->subscribe(get_theme_mod('mailchimp_list_id'), array("email" => $email), $merge_vars, 'html', false);
    }

    public static function subscribe_with_confirmation($email, $merge_vars) {
        $instance = new Mailchimp(get_theme_mod('mailchimp_api_key'));
        return $instance->lists->subscribe(get_theme_mod('mailchimp_list_id'), array("email" => $email), $merge_vars);
    }
}