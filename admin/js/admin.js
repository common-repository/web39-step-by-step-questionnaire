jQuery(document).ready(function () {

    let ajax_url = jQuery('#ajax_url').data('url');
    //Сворачивание/разворачивание блоков вопросов
    jQuery(document).on('click', '.collaps-question', function (e) {
        let current_num = jQuery(this).data('num');
        let div_collaps = jQuery('#collaps-' + current_num);
        if (div_collaps.is(':visible')) {
            div_collaps.hide();
            jQuery(this).removeClass('dashicons-arrow-down-alt2');
            jQuery(this).addClass('dashicons-arrow-up-alt2');
        } else {
            div_collaps.show();
            jQuery(this).removeClass('dashicons-arrow-up-alt2');
            jQuery(this).addClass('dashicons-arrow-down-alt2');
        }
    });

    //Загрузка картинок
    jQuery(document).on('click', '.add-image', function (e) {
        let current_num = jQuery(this).data('num');
        e.preventDefault();
        let image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open().on('select', function (e) {
            let uploaded_image = image.state().get('selection').first();
            let image_url = uploaded_image.toJSON().url;
            if (current_num != -1) {/// num != -1 Для вопросов, если data-num == -1, то картинка для последнего экрана
                jQuery('#id-ii' + current_num).val(image_url);
                jQuery('#img-ii' + current_num).attr('src', image_url);
            } else {
                jQuery('#last-id-ii').val(image_url);
                jQuery('#last-img-ii').attr('src', image_url);
            }
        });
    });

    //Загрузка пустого блока "Вопрос"
    jQuery('#add_question').click(function (e) {
        let count_question = jQuery('#count_questions').val();
        let last_max = jQuery('#count_questions').attr('data-lastmax');
        jQuery('#add_question').prop('disabled', true);
        jQuery.ajax({
            type : 'POST',
            url : ajax_url,
            data : {
                action: 'create_question',
                count: count_question,
                lastmax: last_max
            },
            success : function(data){
                jQuery('#block_questions').append(data);
                count_question++;
                last_max++;
                jQuery('#count_questions').val(count_question);
                jQuery('#count_questions').attr('data-lastmax', last_max);
                numericBlockQuestion();
                jQuery('#add_question').prop('disabled', false);
            }
        });
    });
    //Удаляем Вопрос
    jQuery(document).on('click', '.delete-question', function (e) {
        let _id = jQuery(this).data('num');
        jQuery('#bq' + _id).remove();
        let count_question = jQuery('#count_questions').val();
        count_question--;
        jQuery('#count_questions').val(count_question);
    });

    //Удаляем Answer. Объект динамический, поэтому событие on()
    jQuery(document).on('click', '.delete-answer', function (e) {
        let id_block = jQuery(this).data('block');
        jQuery('#' + id_block).remove();
    });
    //Добавляем Answer
    jQuery(document).on('click', '.add-answer', function (e) {
        let id_block = jQuery(this).data('block');
        id_block = '#block-answers-' + id_block;
        let count_answers = jQuery(id_block).data('count');
        let num_question = jQuery(id_block).data('question'); //id блока вопроса, для уникальности id для строки answer
        let num_position = jQuery('#p-bq' + num_question).attr('data-current'); //Номер позиции для атрибута name
        //Отправляем пост запрос на генерацию кода, отправляем count_answers ответ грузим в =>
        jQuery.ajax({
            type : 'POST',
            url : ajax_url,
            data : {
                action: 'create_answer',
                answer: count_answers,
                question: num_question,
                num_position: num_position
            },
            success : function(data){
                jQuery(id_block).append(data);
                count_answers++;
                jQuery(id_block).data('count', count_answers);
            }
        });
    });

    //Движение блоков Вопросы
    jQuery(document).on('click', '.up-question', function (e) {
        let current_num = jQuery(this).data('num');
        changeBlocQuestion(current_num, true);
    });
    //
    jQuery(document).on('click', '.down-question', function (e) {
        let current_num = jQuery(this).data('num');
        changeBlocQuestion(current_num, false);
    });

    //Меняем блок info для вопроса
    jQuery(document).on('click', '.check-info-block', function () {
        let current_num = jQuery(this).data('num');
        if (jQuery(this).val() == 1) {
            jQuery('#info-image-' + current_num).show();
            jQuery('#info-txt-' + current_num).hide();
        } else {
            jQuery('#info-image-' + current_num).hide();
            jQuery('#info-txt-' + current_num).show();

        }
    });
    //Меняем блок info для последнего экрана
    jQuery(document).on('click', '.check-last-info-block', function () {
        if (jQuery(this).val() == 1) {
            jQuery('#last-info-image').show();
            jQuery('#last-info-txt').hide();
        } else {
            jQuery('#last-info-image').hide();
            jQuery('#last-info-txt').show();

        }
    });
    //Меняем местами блоки
    function changeBlocQuestion(_id_, _up_){
        let el = undefined;
        jQuery('.block-question').each(function () {

            if (jQuery(this).attr('id') === 'bq' + _id_) {
                if (_up_ && jQuery(this).prev().length !== 0) {
                    el = jQuery(this).prev();

                    let copy_from = jQuery(this).clone(true);
                    jQuery(el).replaceWith(copy_from);
                    let copy_to = jQuery(el).clone(true);
                    jQuery(this).replaceWith(copy_to);

                }
                if (!_up_ && jQuery(this).next().length !== 0) {
                    el = jQuery(this).next();

                    let copy_from = jQuery(this).clone(true);
                    jQuery(el).replaceWith(copy_from);
                    let copy_to = jQuery(el).clone(true);
                    jQuery(this).replaceWith(copy_to);
                }
            }
        });

        if (el === undefined) return false;
        numericBlockQuestion();

        update_num(_id_);
        update_num(el.data('id'));

        function update_num(id_block) {
            const listName = [
                'question',
                'type-question',
                'info-image',
                'answer-text',
                'answer-mincost',
                'answer-maxcost',
                'info-input-image',
                'info-input-txt',
            ];
            //Получаем порядковый номер элемента
            let _num = jQuery('#p-bq' + id_block).attr('data-current');

            //Обходим все поля где необходимо изменить поле name field-name-change => _FNC
            jQuery('#bq' + id_block +' ._FNC').each(function () {
                let _name = jQuery(this).attr('name');
                let result = '';
                listName.forEach(function (_item) {
                    if (_name.indexOf(_item) === 0){
                        const reg = new RegExp(_item + '\\[\\d+\\]', 'g');
                        result = _name.replace(reg, _item + '[' + _num +']');
                        return true;
                    }
                })
                jQuery(this).attr('name', result);
            });
        }
    }

    function numericBlockQuestion() {
        let _num = 0;
        let el_p;
        jQuery('.block-question').each(function () {
            let _id = jQuery(this).data('id');
            el_p = jQuery('#p-bq' + _id);
            el_p.attr('data-current', _num++);
            el_p.html(_num);
        });
    }
    //Радиокнопки
    jQuery(document).on('click', '#with-cost-w39sq', function () {
        if (jQuery(this).is(':checked')) {
            jQuery('#currency-w39sq').prop('disabled', false);
        } else {
            jQuery('#currency-w39sq').prop('disabled', true);
        }
    });

    jQuery(document).on('click', '#last-phone', function () {
        if (jQuery(this).is(':checked')) {
            jQuery('#last-mask-phone').prop('disabled', false);
        } else {
            jQuery('#last-mask-phone').prop('disabled', true);
        }
    });

    jQuery(document).on('click', '#last-other', function () {
        if (jQuery(this).is(':checked')) {
            jQuery('#last-other-text').prop('disabled', false);
        } else {
            jQuery('#last-other-text').prop('disabled', true);
        }
    });
    //Плагин Iris
    jQuery(document).ready( function(){
        jQuery('input[class*="color"]').wpColorPicker();
    });
});