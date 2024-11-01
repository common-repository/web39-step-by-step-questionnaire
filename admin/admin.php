<?php
/**
 * Controller for Admin
 *
 * ***
 * в функции update_w39sq - Изменить $w = W39SQFormLoader::load($_POST);
 *
 */

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQ.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQAnswer.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQQuestion.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQRepository.php';
require_once W39SQ_PLUGIN_DIR . '/admin/includes/W39SQTableList.php';
require_once W39SQ_PLUGIN_DIR . '/admin/includes/W39SQFormLoader.php';

//Роутер ***************************************************///
function w39sq_admin_menu()
{
    add_menu_page(
        'Web39_Step_Quest',
        'Web39 Step Question',
        'manage_options',
        'w39sq',
        'w39sq_list_page',
        'dashicons-welcome-widgets-menus'
    );
    add_submenu_page(
        'w39sq',
        __('Список опросников-слайдеров', 'w39sq'),
        __('Список опросников', 'w39sq'),
        'manage_options',
        'w39sq',
        'w39sq_list_page'
    );
    add_submenu_page(
        'w39sq',
        __('Добавить новый опросник-слайдер', 'w39sq'),
        __('Новый опросник', 'w39sq'),
        'manage_options',
        'w39sq_new',
        'w39sq_new_page'
    );
    /*
     * На будущее
    add_submenu_page(
        'w39sq',
        __('Настройки плагина Опросник', 'w39sq'),
        __('Настройки', 'w39sq'),
        'manage_options',
        'w39sq_settings',
        'w39sq_settings_page'
    );*/
}

add_action('admin_menu', 'w39sq_admin_menu');
//POST обработка
add_action('admin_post_create_w39sq', 'w39sq_create');
add_action('admin_post_update_w39sq', 'w39sq_update');
add_action('admin_post_copy_w39sq', 'w39sq_copy');
add_action('admin_post_remove_w39sq', 'w39sq_remove');
//AJAX POST для форм
add_action('wp_ajax_create_answer', 'w39sq_create_answer');
add_action('wp_ajax_create_question', 'w39sq_create_question');
//AJAX для frontend
add_action('wp_ajax_w39sq_request', 'w39sq_request_user');
add_action('wp_ajax_nopriv_w39sq_request', 'w39sq_request_user');

add_action('admin_enqueue_scripts', 'w39sq_load_media_files');
///*************************************************** Роутер//


/*
add_action( 'wp_enqueue_scripts', 'w39sq_load_scripts' );

function w39sq_load_scripts() {
    wp_enqueue_style('w39sq-plugin-admin-style', plugins_url('/css/style.css', __FILE__), array(), W39SQ_PLUGIN_VERSION, 'screen');
    wp_enqueue_script('w39sq-plugin-admin-script', plugins_url('/js/admin.js', __FILE__), array('jquery'), W39SQ_PLUGIN_VERSION);
}
*/
function w39sq_load_media_files()
{
    wp_enqueue_media();
}

function w39sq_new_page()
{
    try {
        W39SQFormLoader::createW39SQ();
    } catch (\Throwable $e) {
        w39sq_echo_thr($e);
    }
}

function w39sq_list_page()
{
    try {
        if (isset($_GET['action'])) {
            $action = sanitize_text_field($_GET['action']);
            if ($action == 'delete' && isset($_GET['id'])) {
                if (!wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'bulk-w39sq')) {
                    echo esc_html__('Верификация запроса не пройдена', 'w39sq');
                    die();
                }
                foreach ($_GET['id'] as $id) {
                    W39SQ::delete((int)sanitize_text_field($id));
                }
            }
            if ($action == 'edit' && isset($_GET['id'])) {
                W39SQFormLoader::updateW39SQ(W39SQ::load(sanitize_text_field($_GET['id'])));
                return;
            }
            if ($action == 'copy' && isset($_GET['id'])) {
                W39SQFormLoader::updateW39SQ((W39SQ::load(sanitize_text_field($_GET['id'])))->copy());
                return;
            }
        }
        W39SQFormLoader::listW39SQ($_GET);
        return;
    } catch (\Throwable $e) {
        w39sq_echo_thr($e);
    }
    die();

}

function w39sq_settings_page()
{
    W39SQFormLoader::settings();
}

function w39sq_create()
{
    $reffer = admin_url('admin.php');
    try {
        $reffer .= '?page=' . sanitize_text_field($_POST['page']);
        $name = sanitize_text_field($_POST['name']);
        $template = sanitize_text_field($_POST['template']);

        $w = W39SQ::create((int)$template, $name); //Создаем Опросник из Шаблона
        if ($result = $w->save()) {
            wp_redirect($reffer . '&id=' . $w->Id() . '&action=edit');
        } else {
            wp_redirect($reffer . '&error=' . $result);
        }
    } catch (Throwable $e) {
        w39sq_echo_thr($e);
        wp_redirect($reffer);
    }
    die();
}

function w39sq_update()
{
    try {
        $page = sanitize_text_field($_POST['page']);
        $w = W39SQRepository::loadParams($_POST);
        if ($w->validate()) {
            $w->save();
            wp_redirect(admin_url('admin.php') . '?page=' . $page);
        } else {
            //Подумать со страницей ошибок
            wp_safe_redirect($_REQUEST['_wp_http_referer']);
        }
    } catch (Throwable $e) {
        w39sq_echo_thr($e);
    }
    die();
}

function w39sq_copy()
{
    try {
        $page = sanitize_text_field($_POST['page']);
        $w = (W39SQRepository::loadParams($_POST))->copy();

        if ($w->validate()) {
            $w->save();
            wp_redirect(admin_url('admin.php') . '?page=' . $page . '&id=' . $w->Id() . '&action=edit');
        } else {
            //Подумать со страницей ошибок
            wp_safe_redirect($_REQUEST['_wp_http_referer']);
        }
    } catch (Throwable $e) {
        w39sq_echo_thr($e);
    }
    die();
}

function w39sq_remove()
{
    $page = sanitize_text_field($_POST['page']);
    W39SQ::delete((int)sanitize_text_field($_POST['id']));
    wp_redirect(admin_url('admin.php') . '?page=' . $page);
}

function w39sq_create_answer()
{
    $count_answer = (int)sanitize_text_field($_POST['answer']);
    $question = (int)sanitize_text_field($_POST['question']);
    $num_position = (int)sanitize_text_field($_POST['num_position']);
    $answer = new W39SQAnswer();
    echo wp_kses(
        W39SQFormLoader::createAnswerRow($answer, $count_answer, $question, $num_position),
        array_merge(
            W39SQAdmin::FILTER_PLUGIN,
            W39SQAdmin::FILTER,
            W39SQAdmin::FILTER_INPUT,
            W39SQAdmin::FILTER_URL
        )
    );
    die();
}

function w39sq_create_question()
{
    try {
        $num_position = (int)sanitize_text_field($_POST['count']);
        $question = new W39SQQuestion();
        $question->addAnswer(new W39SQAnswer());
        $lastmax = (int)sanitize_text_field($_POST['lastmax']);
        echo wp_kses(
            W39SQFormLoader::createBlockQuestion($question, $lastmax, $num_position),
            array_merge(
                W39SQAdmin::FILTER_PLUGIN,
                W39SQAdmin::FILTER,
                W39SQAdmin::FILTER_INPUT,
                W39SQAdmin::FILTER_URL
            )
        );
    } catch (\Throwable $e) {
        w39sq_echo_thr($e);
    }
    die();
}

//Обрабатываем запрос пользователя
function w39sq_request_user()
{
    try {
        $test = false;
        if (!wp_verify_nonce($_POST['nonce'], 'request_user')) wp_die(__('Попытка постороннего доступа', 'w39sq'));

        $w39sq = W39SQ::load((int)sanitize_text_field($_POST['id']));

        $body_success = '<h2>' . __('Опрос клиента', 'w39sq') . '</h2>';
        foreach ($_POST['_post']['questions'] as $question) {
            $body_success .= '<p><strong>' . $question['question'] . '</strong><br>';
            foreach ($question['answer'] as $answer) {
                $body_success .= ' - ' . $answer . '<br>';
            }
            $body_success .= '</p>';
        }

        $body_success .= '<h2>' . __('Контакты клиента', 'w39sq') . '</h2>';
        foreach ($_POST['_post']['user'] as $key => $item) {
            $body_success .= $key . ' - ' . sanitize_text_field($item) . '<br>';
        }

        $headers = [
            'From: ' . $w39sq->email_from,
            'content-type: text/html',
        ];

        if (!$test) wp_mail($w39sq->email, $w39sq->email_subject, $body_success, $headers);// Отправляем письмо админу
        $w39sq->generateResponse();//Отправляем результат на экран клиенту
    } catch (\Throwable $e) {
        echo '<p style="font-size: 36px; color: red; padding: 40px;">' . esc_html__('Что-то пошло не так ...', 'w39sq') . '</p>';
        //echo_thr($e); // для диагностики ошибок
        $body_error = 'Message: ' . $e->getMessage() . '\n\r' .
            'Line: ' . $e->getLine() . '\n\r' .
            'Code: ' . $e->getCode() . '\n\r' .
            'File: ' . $e->getFile();
        wp_mail(
            get_option('admin_email'),
            __('Ошибка отправки заявки W39SQ', 'w39sq'),
            $body_error,
            'From: info@wordpress.com'); // Отправляем письмо админу
    }
    wp_die();
}

# Подключаем Iris Color Picker
add_action('admin_enqueue_scripts', 'w39sq_add_admin_iris_scripts');
function w39sq_add_admin_iris_scripts($hook)
{
    // подключаем IRIS
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');

    // подключаем свой файл скрипта
    //wp_enqueue_script('plugin-script', plugins_url('js/plugin-script.js', __FILE__), array('wp-color-picker'), false, 1);
    wp_enqueue_style('w39sq-plugin-admin-style', plugins_url('/css/style.css', __FILE__), array(), W39SQ_PLUGIN_VERSION, 'screen');
    wp_enqueue_script('w39sq-plugin-admin-script', plugins_url('/js/admin.js', __FILE__), array('wp-color-picker'), false, 1);

}
