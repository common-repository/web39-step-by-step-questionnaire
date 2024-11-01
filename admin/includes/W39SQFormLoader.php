<?php
declare(strict_types=1);
/**
 * Viewer for Admin
 * Статические функции генерации HTML-кода
 *
 */

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQ.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQTemplates.php';
require_once W39SQ_PLUGIN_DIR . '/admin/includes/W39SQTableList.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQQuestion.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQAdmin.php';


class W39SQFormLoader
{
    const checked = 'checked=checked';
    public static function createW39SQ()
    {
        ?>
        <h1><?php echo esc_html__('Создание нового опросника', 'w39sq') ?></h1>
        <form class="form" method="POST" action="<?php echo esc_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="create_w39sq"/>
            <input type="hidden" name="page" value="w39sq"/>
            <?php echo wp_referer_field(false) ?>
            <div class="w39sq-container-edit">
                <div class="w39sq-main-container">
                    <input type="text" name="name" value="" class="w39sq-name"
                           placeholder="<?php echo esc_html__('Введите имя опросника', 'w39sq') ?>" required/>
                    <div class="w39sq-block-data-container">
                        <h3><?php echo esc_html__('Выберите шаблон', 'w39sq') ?></h3>
                        <?php foreach (W39SQTemplates::init() as $item): ?>
                            <div>
                                <label class="img-radio-button">
                                    <input type="radio" name="template" value="<?php echo esc_html($item->getTemplate()) ?>"
                                           title="<?php echo esc_html(__($item->name, 'w39sq')) ?>" required/>
                                    <img src="<?php echo esc_url($item->img) ?>" alt="<?php echo esc_html(__($item->name, 'w39sq')) ?>"
                                         title="<?php echo esc_html(__($item->name, 'w39sq')) ?>"/>
                                    <?php if (!$item->isFree()): ?>
                                        <!--span>Version PRO</span-->
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="w39sq-side-container"><?php self::leftSide(false) ?></div>
            </div>
            <button type="submit" class="button-primary"><?php echo esc_html__('Сохранить', 'w39sq') ?></button>
        </form>
        <?php
    }

    public static function updateW39SQ(W39SQ $w39sq)
    {
        ?>
        <span id="ajax_url" data-url="<?php echo wp_make_link_relative(admin_url("admin-ajax.php")) ?>"></span>
        <h1><?php echo esc_html__('Редактирование опросника', 'w39sq') ?></h1>
        <form class="form" method="POST" action="<?php echo esc_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="update_w39sq"/>
            <input type="hidden" name="page" value="w39sq"/>
            <input type="hidden" name="id" value="<?php echo esc_html($w39sq->Id()) ?>"/>
            <?php echo wp_referer_field(false) ?>
            <div class="w39sq-container-edit">
                <div class="w39sq-main-container">
                    <input type="text" name="name" value="<?php echo esc_html($w39sq->name) ?>" class="w39sq-name"
                           placeholder="<?php echo esc_html__('Введите имя опросника', 'w39sq') ?>" required/>
                    <?php echo self::_blockMain($w39sq); ?>
                    <?php echo self::_blockTemplate($w39sq); ?>
                    <?php echo self::_blockQuestions($w39sq); ?>
                    <?php echo self::_blockLastScreen($w39sq); ?>
                    <?php echo self::_blockResponse($w39sq); ?>
                    <?php echo self::_blockUserStyles($w39sq); ?>
                </div>
                <div class="w39sq-side-container">
                    <?php self::leftSide() ?>
                </div>
            </div>
            <button type="submit" class="button-primary"><?php echo esc_html__('Сохранить', 'w39sq') ?></button>
        </form>
        <?php
    }

    public static function listW39SQ($params)
    {
        self::panel();
        $table = new W39SQTableList();
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="page" value="w39sq" />';
        $table->config($params);
        $table->prepare_items();
        $table->display();
        echo '</form>';
        echo '</div>';
    }

    public static function settings()
    {
        echo '<h1>Настройки плагина</h1>';

        echo '<p>Текущая версия : ' . W39SQ_PLUGIN_VERSION . '</p>';
    }

    //Создаем карточку Вопроса
    public static function createBlockQuestion(W39SQQuestion $question, $lastmax, $num = null)
    {
        if ($num == null) $num = $lastmax;
        ob_start();
        ?>
        <div id="bq<?php echo esc_html($lastmax) ?>" data-id="<?php echo esc_html($lastmax) ?>" class="block-question"
             style="border: 1px solid #888; margin-top: 20px;">
            <div class="d-flex-row" style="border-bottom: 1px dashed #888;">
                <div style="width: 40px; margin: 10px;">
                    <p id="p-bq<?php echo esc_html($lastmax) ?>" class="bq-number" data-current="<?php echo esc_html($num) ?>"
                       style="border: 1px solid #888; border-radius: 2px; padding: 4px 8px; margin: 0; font-weight: 700"><?php echo esc_html($num + 1) ?></p>
                </div>
                <div style="width: 100%; margin: 10px;">
                    <input type="text" name="question[<?php echo esc_html($num) ?>][question]" class="w39sq-field _FNC"
                           value="<?php echo esc_html($question->question) ?>"
                           placeholder="<?php echo esc_html__('Вопрос', 'w39sq') ?>"/>
                </div>
                <div style="width: 100px; margin: 10px;">
                    <button type="button" data-num="<?php echo esc_html($lastmax) ?>"
                            class="collaps-question dashicons dashicons-arrow-up-alt2"
                            style="width: 40px; height: 30px; cursor: pointer;"></button>
                </div>
            </div>
            <div id="collaps-<?php echo esc_html($lastmax) ?>" class="d-flex-row" style="display:none;">
                <div style="width: 50px; background: #fff; padding: 4px;">
                    <p>
                        <span class="up-question touch-block dashicons dashicons-arrow-up-alt2"
                              data-num="<?php echo esc_html($lastmax) ?>"></span>
                    </p>
                    <p>
                        <span class="down-question touch-block dashicons dashicons-arrow-down-alt2"
                              data-num="<?php echo esc_html($lastmax) ?>"></span>
                    </p>
                    <p>
                        <span class="delete-question touch-block dashicons dashicons-trash"
                              data-num="<?php echo esc_html($lastmax) ?>"></span>
                    </p>
                </div>
                <div style="width: 100%; padding: 10px 20px;" class="d-flex-row">
                    <div>
                        <div>
                            <span style="font-weight: 600"><?php echo esc_html__('Множественные ответы', 'w39sq') ?></span>
                            <label for="type-question-1-<?php echo esc_html($num) ?>"><?php echo esc_html__('Да', 'w39sq') ?></label>
                            <input id="type-question-1-<?php echo esc_html($num) ?>" type="radio" name="question[<?php echo esc_html($num) ?>][multi]"
                                   class="w39sq-field _FNC" value="1" <?php if ($question->multi) echo esc_attr(self::checked) ?>/>
                            <label for="type-question-2-<?php echo esc_html($num) ?>"><?php echo esc_html__('Нет', 'w39sq') ?></label>
                            <input id="type-question-2-<?php echo esc_html($num) ?>" type="radio" name="question[<?php echo esc_html($num) ?>][multi]"
                                   class="w39sq-field _FNC" value="0" <?php if (!$question->multi) echo esc_attr(self::checked) ?>/>
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="button" class="add-answer touch-block" style="padding: 4px"
                                    data-block="<?php echo esc_html($lastmax) ?>">+<?php echo esc_html__('Добавить вариант', 'w39sq') ?></button>
                        </div>
                        <div id="block-answers-<?php echo esc_html($lastmax) ?>" data-count="<?php echo esc_html(count($question->answers)) ?>"
                             data-question="<?php echo esc_html($lastmax) ?>" style="width: 100%; max-width: 800px; margin-top: 10px;">
                            <div class="d-flex-row">
                                <div style="width: 300px"><?php echo esc_html__('Вариант ответа', 'w39sq') ?></div>
                                <div style="width: 180px"><?php echo esc_html__('Минимальная Цена', 'w39sq') ?></div>
                                <div style="width: 180px"><?php echo esc_html__('Максимальная Цена', 'w39sq') ?></div>
                                <div style="width: 50px"></div>
                            </div>
                            <?php foreach ($question->answers as $i => $answer) {
                                echo self::createAnswerRow($answer, $i, $lastmax);
                            } ?>
                        </div>
                    </div>
                    <div style="margin-left: 20px; width: 100%;">
                        <div>

                            <span style="font-weight: 600"><?php echo esc_html__('Дополнительный блок', 'w39sq') ?></span>
                            <label for="info-image-1-<?php echo esc_html($lastmax) ?>"><?php echo esc_html__('Картинка', 'w39sq') ?></label>
                            <input id="info-image-1-<?php echo esc_html($lastmax) ?>" type="radio"
                                   name="question[<?php echo esc_html($num) ?>][info-image]"
                                   class="w39sq-field _FNC check-info-block" data-num="<?php echo esc_html($lastmax) ?>"
                                   value="1" <?php if ($question->info_img) echo esc_attr(self::checked) ?>/>
                            <label for="info-image-2-<?php echo esc_html($lastmax) ?>"><?php echo esc_html__('Текст', 'w39sq') ?></label>
                            <input id="info-image-2-<?php echo esc_html($lastmax) ?>" type="radio"
                                   name="question[<?php echo esc_html($num) ?>][info-image]"
                                   class="w39sq-field _FNC check-info-block" data-num="<?php echo esc_html($lastmax) ?>"
                                   value="0" <?php if (!$question->info_img) echo esc_attr(self::checked) ?> />
                        </div>
                        <div style="margin-top: 20px;">
                            <div id="info-image-<?php echo esc_html($lastmax) ?>" class="<?php echo esc_html($question->info_img ? '' : 'w39sq-display-none') ?>">
                                <input id="id-ii<?php echo esc_html($lastmax) ?>" name="question[<?php echo esc_html($num) ?>][info-input-image]"
                                       type="hidden" value="<?php echo esc_url($question->getInfo(true)) ?>"/>
                                <img id="img-ii<?php echo esc_html($lastmax) ?>" src="<?php echo esc_url($question->getInfo(true)) ?>"
                                     class="w39sq-img-question"><br>
                                <button type="button" class="add-image"
                                        data-num="<?php echo esc_html($lastmax) ?>"><?php echo esc_html__('Загрузить', 'w39sq') ?></button>
                            </div>
                            <div id="info-txt-<?php echo esc_html($lastmax) ?>" class="<?php echo esc_html(!$question->info_img ? '' : 'w39sq-display-none') ?>">
                                <textarea name="question[<?php echo esc_html($num) ?>][info-input-txt]" class="w39sq-field _FNC"
                                          rows="8"><?php echo wp_kses($question->getInfo(false), W39SQAdmin::FILTER) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    //Создаем поле Ответа
    public static function createAnswerRow(W39SQAnswer $answer, int $numAnswer, int $numQuestion, int $numPosition = null): string
    {
        ob_start();
        $id_block = 'id-' . $numQuestion . '-' . $numAnswer;
        if ($numPosition == null) $numPosition = $numQuestion;
        ?>
        <div id="<?php echo esc_html($id_block) ?>" class="d-flex-row">
            <div style="width: 300px">
                <input type="text" name="question[<?php echo esc_html($numPosition) ?>][answer][<?php echo esc_html($numAnswer) ?>][text]"
                       class="w39sq-field _FNC" value="<?php echo esc_html($answer->text) ?>"/>
            </div>
            <div style="width: 170px">
                <input type="number" name="question[<?php echo esc_html($numPosition) ?>][answer][<?php echo esc_html($numAnswer) ?>][mincost]"
                       class="w39sq-field _FNC" min="0" value="<?php echo esc_html($answer->min_cost) ?>" style="min-width: 100px;"/>
            </div>
            <div style="width: 170px">
                <input type="number" name="question[<?php echo esc_html($numPosition) ?>][answer][<?php echo esc_html($numAnswer) ?>][maxcost]"
                       class="w39sq-field _FNC" min="0" value="<?php echo esc_html($answer->max_cost) ?>" style="min-width: 100px;"/>
            </div>
            <div style="width: 50px"><span class="delete-answer touch-block dashicons dashicons-trash"
                                           data-block="<?php echo esc_html($id_block) ?>"></span></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /////********* ВНУТРЕННИЕ БЛОКИ ОТРИСОВКИ ***
    //Правая панель с доп инфо блоками
    private static function leftSide(bool $add_button = true)
    {
        ?>
        <div class="w39sq-card">
            <div class="w39sq-card-body">
                <h3><?php echo esc_html__('Статус', 'w39sq') ?></h3>
                <div style="text-align: right">
                    <?php if ($add_button): ?>
                        <input type="submit" onclick="this.form.action.value='copy_w39sq'"
                               value="<?php echo esc_html__('Дублировать', 'w39sq') ?>" class="w39sq-submit-button">
                    <?php endif; ?>
                </div>
            </div>
            <div class="w39sq-card-footer">
                <?php if ($add_button): ?>
                    <input type="submit" value="<?php echo esc_html__('Удалить', 'w39sq') ?>" class="w39sq-submit-remove"
                           onclick="if (confirm('Delete the current form?')) {this.form.action.value = 'remove_w39sq'; return true;} return false;">
                <?php endif; ?>
                <button type="submit" class="button-primary"
                        style="margin-left: auto"><?php echo esc_html__('Сохранить', 'w39sq') ?></button>
            </div>
        </div>
        <div class="card">
            <h3><?php echo esc_html__('Вам нужна помощь?', 'w39sq') ?></h3>
            <ol>
                <li><a href="<?php echo esc_url(W39SQAdmin::LINK_DOC) ?>"><?php echo esc_html__('Документация', 'w39sq') ?></a></li>
                <li><a href="<?php echo esc_url(W39SQAdmin::LINK_FAQ) ?>"><?php echo esc_html__('Частые вопросы', 'w39sq') ?></a></li>
            </ol>
        </div>
        <?php
    }

    //Информационная панель
    private static function panel()
    {
        //Отображение панели
        $_email = '<a href="mailto:' . W39SQAdmin::EMAIL_AUTHOR . '">' . W39SQAdmin::EMAIL_AUTHOR . '</a>';
        ?>
        <div class="w39sq-panel">
            <h2><?php echo esc_html__('Плагин Пошаговый опросник', 'w39sq') ?></h2>
            <h4><?php echo esc_html__('Версия ', 'w39sq') . W39SQ_PLUGIN_VERSION ?></h4>
            <p><?php echo wp_kses(sprintf(
                    __(
                        'Плагин бесплатный, предоставляется как есть. <br>' .
                        'Если у вас есть замечание или предложения, пишите мне на электронную почту %s<br>' .
                        'Поддержать разработку плагина можно переведя любую сумму на карту <b>%s</b>',
                        'w39sq'),
                    $_email, W39SQAdmin::CARD_AUTHOR), array_merge(W39SQAdmin::FILTER_URL, W39SQAdmin::FILTER)); ?></p>
            <p> <a href="<?php echo esc_url(W39SQAdmin::LINK_MAIN) ?>"><?php echo esc_html__('Сайт разработчика', 'w39sq'); ?></a></p>
            <div style="padding-top: 10px">
                <a href="<?php echo menu_page_url('w39sq_new', false) ?>"
                   class="w39sq-a-button"><?php echo esc_html__('Создать Новый', 'w39sq'); ?></a>
            </div>
        </div>
        <?php
    }

    //Блоки из отрисовки в редактировании Опросника
    private static function _blockMain(W39SQ $w39sq): string
    {
        ob_start();
        ?>
        <div class="w39sq-block-data-container">
            <!--h3><?php echo esc_html__('Основные параметры', 'w39sq') ?></h3-->
            <label for="title-w39sq"><?php echo esc_html__('Заголовок', 'w39sq') ?></label>
            <input id="title-w39sq" type="text" name="title" class="w39sq-field" value="<?php echo esc_html($w39sq->title) ?>"
                   placeholder="<?php echo esc_html__('Заголовок', 'w39sq') ?>"/>

            <label for="description-w39sq"><?php echo esc_html__('Информационный текст под заголовком (работают теги)', 'w39sq') ?></label>
            <textarea id="description-w39sq" name="description" class="w39sq-field"
                      rows="5"><?php echo wp_kses($w39sq->description, W39SQAdmin::FILTER) ?></textarea>


            <div class="d-flex-row" style=" padding-left: 24px;  padding-bottom: 12px;">
                <div style="margin: auto 0;">
                    <label for="with-cost-w39sq"><?php echo esc_html__('Предварительный расчет стоимости', 'w39sq') ?></label>
                    <input id="with-cost-w39sq" type="checkbox" name="with-cost"
                           class="w39sq-field" <?php echo esc_html($w39sq->question->withCost ? 'checked' : '') ?>/>
                    <label for="currency-w39sq" style="padding-left: 40px;"><?php echo esc_html__('Валюта', 'w39sq') ?></label>
                    <input id="currency-w39sq" type="text" name="currency"
                           class="w39sq-field w39sq-field-sm"
                           value="<?php echo esc_html($w39sq->question->currency) ?>" <?php echo esc_html($w39sq->question->withCost ? '' : 'disabled') ?> />
                </div>
            </div>
            <span style="font-weight: 700;"><?php echo esc_html__('Почта', 'w39sq') ?></span>
            <div class="d-flex-row">
                <div style="min-width: 25%">
                    <label for="email-w39sq"><?php echo esc_html__('Кому', 'w39sq') ?></label>
                    <input id="email-w39sq" type="text" name="email" class="w39sq-field" value="<?php echo esc_html($w39sq->email) ?>"
                           placeholder="<?php echo esc_html__('Email результата', 'w39sq') ?>" required/>
                </div>
                <div style="margin-left: 40px; min-width: 25%">
                    <label for="email-from-w39sq"><?php echo esc_html__('От кого', 'w39sq') ?></label>
                    <input id="email-from-w39sq" type="text" name="email-from" class="w39sq-field"
                           value="<?php echo esc_html($w39sq->email_from) ?>"
                           placeholder="<?php echo esc_html__('От кого', 'w39sq') ?>" required/>
                </div>
                <div style="margin-left: 40px; min-width: 25%">
                    <label for="email-subject-w39sq"><?php echo esc_html__('Тема письма', 'w39sq') ?></label>
                    <input id="email-subject-w39sq" type="text" name="email-subject" class="w39sq-field"
                           value="<?php echo esc_html($w39sq->email_subject) ?>"
                           placeholder="<?php echo esc_html__('Тема письма', 'w39sq') ?>" required/>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function _blockTemplate(W39SQ $w39sq): string
    {
        ob_start();
        ?>
        <div class="w39sq-block-data-container">
            <h3><?php echo esc_html__('Настройки шаблона', 'w39sq') ?></h3>
            <!-- Загрузка переменных с шаблона -->
            <?php foreach ($w39sq->getTemplateVariables() as $variable): ?>
                <div class="w39sq-template-field" style="min-width: 277px;">
                    <div style=" display: flex; flex-direction: column;">
                    <label for="var-<?php echo esc_html($variable->variable) ?>"><?php echo esc_html__($variable->caption, 'w39sq') ?></label>
                    <input id="var-<?php echo esc_html($variable->variable) ?>" type="<?php echo esc_html($variable->typeInput()) ?>"
                           name="<?php echo esc_html($variable->variable) ?>" class="w39sq-field <?php echo esc_html(($variable->type == W39SQVariable::VAR_COLOR) ? 'color' : '')?>"
                           value="<?php echo esc_html($w39sq->getVariable($variable)) ?>"/>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
        <?php
        return ob_get_clean();
    }

    private static function _blockQuestions(W39SQ $w39sq): string
    {
        ob_start();
        ?>
        <div class="w39sq-block-data-container">
            <h3><?php echo esc_html__('Вопросы', 'w39sq') ?></h3>
            <div>
                <button type="button" id="add_question"
                        style="height: 30px;"><?php echo esc_html__('Добавить вопрос', 'w39sq') ?></button>
                <input id="count_questions" type="number" min="0" max="99"
                       data-lastmax="<?php echo esc_html($w39sq->question->count()) ?>"
                       value="<?php echo esc_html($w39sq->question->count()) ?>" style="width: 50px; text-align: right;" readonly/>
            </div>
            <div id="block_questions">
                <?php foreach ($w39sq->question->getAll() as $num => $question) {
                    echo self::createBlockQuestion($question, $num);
                } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function _blockLastScreen(W39SQ $w39sq): string
    {
        ob_start();
        ?>
        <div class="w39sq-block-data-container">
            <h3><?php echo esc_html__('Информационный слайдер', 'w39sq') ?></h3>
            <div class="d-flex-row">
                <div style="min-width: 30%;">
                    <label for="last-title"><?php echo esc_html__('Информационный текст', 'w39sq') ?></label>
                    <textarea id="last-title" type="text" name="last-title" rows="5"
                              class="w39sq-field"><?php echo wp_kses($w39sq->question->last->title, W39SQAdmin::FILTER) ?></textarea>
                </div>
                <div style="margin: auto">
                    <div class="fields-in-row">
                        <label for="last-name"><?php echo esc_html__('Поле с именем', 'w39sq') ?></label>
                        <input id="last-name" type="checkbox"
                               name="last-name" <?php echo esc_html($w39sq->question->last->name ? 'checked' : '') ?>>
                    </div>
                    <div class="fields-in-row">
                        <label for="last-phone"><?php echo esc_html__('Поле с телефоном', 'w39sq') ?></label>
                        <input id="last-phone" type="checkbox"
                               name="last-phone" <?php echo esc_html($w39sq->question->last->phone ? 'checked' : '') ?>>

                        <label for="last-mask-phone"><?php echo esc_html__('Маска', 'w39sq') ?></label>
                        <input id="last-mask-phone" type="text" name="last-mask-phone"
                               value="<?php echo esc_html($w39sq->question->last->mask_phone) ?>"
                            <?php echo esc_html($w39sq->question->last->phone ? '' : 'disabled') ?>>
                    </div>
                    <div class="fields-in-row">
                        <label for="last-email"><?php echo esc_html__('Поле с email', 'w39sq') ?></label>
                        <input id="last-email" type="checkbox"
                               name="last-email" <?php echo esc_html($w39sq->question->last->email ? 'checked' : '') ?>>
                    </div>
                    <div class="fields-in-row">
                        <label for="last-other"><?php echo esc_html__('Дополнительное поле', 'w39sq') ?></label>
                        <input id="last-other" type="checkbox"
                               name="last-other" <?php echo esc_html($w39sq->question->last->other ? 'checked' : '') ?>>

                        <input id="last-other-text" type="text" name="last-other-text"
                               value="<?php echo esc_html($w39sq->question->last->other_text) ?>"
                            <?php echo esc_html($w39sq->question->last->other ? '' : 'disabled') ?>>
                    </div>
                    <div class="fields-in-row">
                        <label for="last-caption-submit"><?php echo esc_html__('Подпись к кнопке', 'w39sq') ?></label>
                        <input id="last-caption-submit" type="text" name="last-caption-submit"
                               value="<?php echo esc_html($w39sq->question->last->caption_submit) ?>">
                    </div>
                </div>
                <div style="margin-left: 20px; min-width: 20%;">
                    <div>
                        <span style="font-weight: 600"><?php echo esc_html__('Дополнительный блок', 'w39sq') ?></span>
                        <label for="last-info-image-1"><?php echo esc_html__('Картинка', 'w39sq') ?></label>
                        <input id="last-info-image-1" type="radio" name="last-info-img"
                               class="w39sq-field check-last-info-block"
                               value="1" <?php echo esc_html($w39sq->question->last->info_img ? 'checked' : '') ?>/>
                        <label for="last-info-image-2"><?php echo esc_html__('Текст', 'w39sq') ?></label>
                        <input id="last-info-image-2" type="radio" name="last-info-img"
                               class="w39sq-field check-last-info-block"
                               value="0" <?php echo esc_html($w39sq->question->last->info_img ? '' : 'checked') ?>/>
                    </div>
                    <div style="margin-top: 20px;">
                        <div id="last-info-image" class="<?php echo esc_html($w39sq->question->last->info_img ? '' : 'w39sq-display-none') ?>">
                            <input id="last-id-ii" name="last-info-input-image" type="hidden"
                                   value="<?php echo esc_html($w39sq->question->last->getInfo()) ?>"/>
                            <img id="last-img-ii" src="<?php echo esc_url($w39sq->question->last->getInfo()) ?>"
                                 class="w39sq-img-question"><br>
                            <button type="button" class="add-image"
                                    data-num="-1"><?php echo esc_html__('Загрузить', 'w39sq') ?></button>
                        </div>
                        <div id="last-info-txt" class="<?php echo esc_html(!$w39sq->question->last->info_img ? '' : 'w39sq-display-none') ?>">
                        <textarea name="last-info-input-txt" class="w39sq-field _FNC"
                                  rows="8"><?php echo wp_kses($w39sq->question->last->getInfo(false), W39SQAdmin::FILTER) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function _blockResponse(W39SQ $w39sq): string
    {
        ob_start();
        ?>
        <div class="w39sq-block-data-container">
            <h3><?php echo esc_html__('Блок с выводом об успешной отправке запроса', 'w39sq') ?></h3>
            <label for="response-title"><?php echo esc_html__('Заголовок', 'w39sq') ?></label>
            <input id="response-title" type="text" name="response-title"
                   class="w39sq-field" value="<?php echo esc_html($w39sq->response->title) ?>"/>

            <label for="response-description"><?php echo esc_html__('Информационный текст', 'w39sq') ?></label>
            <textarea id="response-description" type="text" name="response-description"
                      class="w39sq-field" rows="5"><?php echo wp_kses($w39sq->response->description, W39SQAdmin::FILTER) ?></textarea>
            <div class="d-flex-row">
                <div style="margin: auto 20px;">
                    <label for="response-type-phone"><?php echo esc_html__('Ссылка на телефон', 'w39sq') ?></label>
                    <input id="response-type-phone" type="radio" name="response-type-contact"
                           value="<?php echo esc_html(W39SQResponse::CONTACT_PHONE) ?>" <?php echo esc_html($w39sq->response->isPhone() ? 'checked' : '') ?>>
                </div>
                <div style="margin: auto 20px;">
                    <label for="response-type-email"><?php echo esc_html__('Ссылка на email', 'w39sq') ?></label>
                    <input id="response-type-email" type="radio" name="response-type-contact"
                           value="<?php echo esc_html(W39SQResponse::CONTACT_EMAIL) ?>" <?php echo esc_html($w39sq->response->isEmail() ? 'checked' : '') ?>>
                </div>
                <div style="margin: auto 20px;">
                    <label for="response-type-none"><?php echo esc_html__('Без контактных данных', 'w39sq') ?></label>
                    <input id="response-type-none" type="radio" name="response-type-contact"
                           value="<?php echo esc_html(W39SQResponse::CONTACT_NONE) ?>" <?php echo esc_html($w39sq->response->notContact() ? 'checked' : '') ?>>
                </div>
                <div style="margin: auto 20px;">
                    <label for="response-contact"><?php echo esc_html__('Контактный телефон/email', 'w39sq') ?></label>
                    <input id="response-contact" type="text" name="response-contact"
                           class="w39sq-field" value="<?php echo esc_html($w39sq->response->contact) ?>"/>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function _blockUserStyles(W39SQ $w39sq): string
    {
        ob_start();
        ?>
        <div class="w39sq-block-data-container">
            <h3><?php echo esc_html__('Переопределение стилей', 'w39sq') ?></h3>
            <textarea name="user-style" cols="100" rows="12"><?php echo wp_kses($w39sq->user_style, W39SQAdmin::FILTER) ?></textarea>
        </div>
        <?php
        return ob_get_clean();
    }

}



