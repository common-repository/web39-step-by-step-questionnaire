<?php
declare(strict_types=1);

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQ.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQTemplates.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQQuestion.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQAdmin.php';

class W39SQRepository
{
    public static function loadParams(array $params): ?W39SQ
    {
        try {
            if (empty($params['id'])) throw new \DomainException('Не определен ID');

            $w39sq = W39SQ::load((int)sanitize_text_field($params['id']));
            $w39sq->name = sanitize_text_field($params['name']);
            $w39sq->title = sanitize_text_field($params['title']);
            $w39sq->description = wp_kses($params['description'], W39SQAdmin::FILTER);
            $w39sq->email = sanitize_email($params['email']);
            $w39sq->email_from = sanitize_email($params['email-from']);
            $w39sq->email_subject = sanitize_text_field($params['email-subject']);
            $w39sq->user_style = wp_strip_all_tags($params['user-style']);

            $_variables = [];
            //Функция возвращает список дефолтных переменных для шаблона ($template - приватная переменная)
            foreach ($w39sq->getTemplateVariables() as $item) {
                $_variables[] = new W39SQVariable(
                    $item->variable,
                    $item->type,
                    $item->type == W39SQVariable::VAR_COLOR ? sanitize_hex_color($params[$item->variable]) : sanitize_text_field($params[$item->variable]),
                    $item->caption
                );
            }
            $w39sq->variables = $_variables;

            //Загружаем вопросы
            $w39sq->question->clear();
            $w39sq->question->withCost = isset($params['with-cost']);
            $w39sq->question->currency = sanitize_text_field($params['currency'] ?? '₽');
            /** @var array $question */
            foreach ($params['question'] as $question) {
                $w39sq->question->addQuestion(W39SQQuestion::create(
                    $question['question'],
                    (int)($question['multi']) == 1,
                    ((int)($question['info-image']) == 1) ? sanitize_url($question['info-input-image']) : wp_kses($question['info-input-txt'], W39SQAdmin::FILTER),
                    (int)($question['info-image']) == 1,
                    array_map(function (array $answer) {
                        return W39SQAnswer::create(
                            sanitize_text_field($answer['text'] ?? __('Ошибка поля answer-text', 'w39sq')),
                            (int)sanitize_text_field($answer['mincost'] ?? 0),
                            (int)sanitize_text_field($answer['maxcost'] ?? 0));
                    }, $question['answer'] ?? [])
                ));
            }

            ///последний экран
            $w39sq->question->last->title = sanitize_text_field($params['last-title'] ?? '');
            $w39sq->question->last->name = isset($params['last-name']);
            $w39sq->question->last->phone = isset($params['last-phone']);
            $w39sq->question->last->mask_phone = sanitize_text_field($params['last-mask-phone'] ?? '');
            $w39sq->question->last->email = isset($params['last-email']);
            $w39sq->question->last->other = isset($params['last-other']);
            $w39sq->question->last->other_text = sanitize_text_field($params['last-other-text'] ?? '');
            $w39sq->question->last->caption_submit = sanitize_text_field($params['last-caption-submit'] ?? __('Отправить', 'w39sq'));
            $w39sq->question->last->info_img = ((int)($params['last-info-img']) == 1);
            $w39sq->question->last->info = $w39sq->question->last->info_img
                ? sanitize_url($params['last-info-input-image'])
                : wp_kses($params['last-info-input-txt'], W39SQAdmin::FILTER);
            ///Ответ на запрос
            $w39sq->response->title = sanitize_text_field($params['response-title'] ?? '');
            $w39sq->response->description = wp_kses($params['response-description'] ?? '', W39SQAdmin::FILTER);
            $w39sq->response->type_contact = (int)sanitize_text_field($params['response-type-contact']);
            $w39sq->response->contact = sanitize_text_field($params['response-contact'] ?? '');

            return $w39sq;
        } catch (\Throwable $e) {
            w39sq_echo_thr($e);
            return null;
        }
    }
}