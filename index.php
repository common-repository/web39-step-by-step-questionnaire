<?php
/**
 * Plugin Name: Web39 Step-By-Step Questionnaire
 * Plugin URI : https://website39.site/wp-content/plugins/web39-step-quest
 * Description: Плагин Опросник-Заявка.
 * Author: Моисеенко Роман Александрович
 * Version: 0.9.2
 * Использование плагинов WordPress | 91
 * Author URI :
 * License: GPL-2.0+
 * License URI: http://www.gnu.Org/licenses/gpl-2.0.txt
 */

define('W39SQ_PLUGIN_VERSION', '0.9.2');
define('W39SQ_PLUGIN', __FILE__);
define('W39SQ_PLUGIN_BASENAME', plugin_basename(W39SQ_PLUGIN));
define('W39SQ_PLUGIN_NAME', trim(dirname(W39SQ_PLUGIN_BASENAME), '/'));
define('W39SQ_PLUGIN_DIR', untrailingslashit(dirname(W39SQ_PLUGIN)));
define('W39SQ_PLUGIN_URL', plugins_url() . '/' . W39SQ_PLUGIN_NAME . '/');

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQ.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQOptions.php'; //Файл структура опций для плагина

if (is_admin()) {
    require_once W39SQ_PLUGIN_DIR . '/admin/admin.php';
} else {
    require_once W39SQ_PLUGIN_DIR . '/includes/controller.php';
}

add_action('init', 'w39sq_init');

function w39sq_init()
{
    global $wpdb;

    load_plugin_textdomain('w39sq', false, dirname( plugin_basename( __FILE__ )) . '/lang/');
    //Проверка на наличие записи в опциях, если нет, то запускаем инициализацию БД и др.
    try {
        if (!$current_version = get_option(W39SQOptions::VERSION)) {
            //отсутствует Опция плагина
            add_option(W39SQOptions::VERSION, W39SQ_PLUGIN_VERSION);
            w39sq_initDB();
        } elseif ($current_version != W39SQ_PLUGIN_VERSION) {
            //Несовпадение версий
            update_option(W39SQOptions::VERSION, W39SQ_PLUGIN_VERSION);
            w39sq_initDB();
        } elseif ($wpdb->get_var("show tables like '" . W39SQ::tableName() . "'") != W39SQ::tableName()) {
            //Отсутствует таблица плагина
            w39sq_initDB();
        }

    } catch (\Throwable $e) {
        w39sq_echo_thr($e);}
    do_action('w39sq_init');
}

function w39sq_initDB() {
    $table_name = W39SQ::tableName();
    $sql = "CREATE TABLE " . $table_name . " (
             id mediumint(9) NOT NULL AUTO_INCREMENT,
             name varchar(255) NOT NULL COLLATE utf8_general_ci, 
             title varchar(255) COLLATE utf8_general_ci,
             description mediumtext COLLATE utf8_general_ci,
             email varchar(255) COLLATE utf8_general_ci,
             email_from varchar(255) COLLATE utf8_general_ci,
             email_subject varchar(255) COLLATE utf8_general_ci,
             template INT COLLATE utf8_general_ci,
             style longtext COLLATE utf8_general_ci,
             user_style longtext COLLATE utf8_general_ci,
             code longtext COLLATE utf8_general_ci,
             question longtext COLLATE utf8_general_ci,
             response longtext COLLATE utf8_general_ci,
             variables longtext COLLATE utf8_general_ci,
             other longtext COLLATE utf8_general_ci,
             created_at BIGINT NOT NULL COLLATE utf8_general_ci,
             updated_at BIGINT COLLATE utf8_general_ci,
             count INT  COLLATE utf8_general_ci,
             UNIQUE KEY id (id)
          );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function w39sq_echo_thr(\Throwable $e) //временная ф-ция для отладки
{
    echo 'Message: ' . esc_attr($e->getMessage()) . '<br>';
    echo 'Line: ' . esc_attr($e->getLine()) . '<br>';
    echo 'Code: ' . esc_attr($e->getCode()) . '<br>';
    echo 'File: ' . esc_attr($e->getFile()) . '<br>';
}

/****/
register_activation_hook(__FILE__, function () {
    if (!current_user_can('activate_plugins')) {// проверяем права пользователя на активацию плагинов
        return;
    }
    $plugin = sanitize_text_field($_REQUEST['plugin'] ?? '');
    check_admin_referer( "activate-plugin_{$plugin}" );
    //
});

register_deactivation_hook(__FILE__, function () {
    if (!current_user_can('deactivate_plugins')) {// проверяем права пользователя на деактивацию плагинов
        return;
    }
    //
});

register_uninstall_hook(__FILE__, 'w39sq_drop_data_plugin');//Удаляем следы Плагина при удалении

function w39sq_drop_data_plugin()
{
    global $wpdb;
    $wpdb->query('DROP TABLE IF EXISTS ' . W39SQ::tableName());
    delete_option(W39SQOptions::VERSION);
}