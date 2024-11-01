<?php
declare(strict_types=1);


require_once W39SQ_PLUGIN_DIR . '/classes/W39SQAdmin.php';

class W39SQLoadShort
{
    static function init()
    {
        add_shortcode('w39sq', array('W39SQLoadShort', 'load_shortcode'));
        add_action('wp_enqueue_scripts', array('W39SQLoadShort', 'wp_enqueue_scripts'));
    }

    static function load_shortcode($atts)
    {
        try {
            $w = W39SQ::load((int)$atts['id']);
            //Передаем параметры для js
            wp_localize_script(
                'w39sq-plugin',
                'w39sq_object',
                [
                    'id' => $w->Id(),
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('request_user'),
                    'required' => __('Заполните это поле!', 'w39sq'),
                    //TODO добавить параметр в класс
                    'currency' => 1, //Расположение знака валюты 0 - слева, 1 - справа
                ]
            );

            ob_start();
            echo wp_kses($w->getHTML(), array_merge(
                W39SQAdmin::FILTER_PLUGIN,
                W39SQAdmin::FILTER,
                W39SQAdmin::FILTER_INPUT,
                W39SQAdmin::FILTER_URL
            ));
            return ob_get_clean();
        } catch (DomainException $e) {
            return $e->getMessage();
        }

    }

    static function wp_enqueue_scripts()
    {
        wp_enqueue_style('w39sq-plugin', plugins_url('/../assets/style.css', __FILE__), array(), W39SQ_PLUGIN_VERSION, 'screen');
        wp_enqueue_script('w39sq-plugin', plugins_url('/../assets/w39sq.js', __FILE__), array('jquery'), W39SQ_PLUGIN_VERSION, true);
        wp_enqueue_script('jquery_maskedinput', plugins_url('/../assets/jquery.maskedinput.min.js', __FILE__), array('jquery'), '1.4.1', true);
    }
}