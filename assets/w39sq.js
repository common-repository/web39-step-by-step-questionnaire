jQuery(document).ready(function () {

    if (w39sq_object === undefined) console.log('Error!! Object w39sq_object not found!');

    if (document.getElementById("w39sq")) {
        let _position = 0;
        let w39sq = jQuery("#w39sq");
        let _max_position = w39sq.data('count') - 1;
        let _withCost = w39sq.data('cost')
        let _slider = jQuery('.w39sq-slider').first()
        let _button_prev = jQuery('.w39sq-swiper-button-prev').first()
        let _button_next = jQuery('.w39sq-swiper-button-next').first()

        //Предзагрузка
        if (document.getElementById("user_phone")) {
            let user_phone = jQuery("#user_phone"); //Устанавливаем маску, если поле телефон было включено
            user_phone.mask(user_phone.data('mask'));
        }
        if (_withCost === 1) {
            let _block_cost = jQuery('#w39sq_cost');
            let currency = _block_cost.data('currency');

            jQuery(document).on('click', '.w39sq-field', function () {
                let min_cost = 0, max_cost = 0;
                jQuery('.w39sq-field').each(function () { //Переобсчет стоимости, проверяем каждый элемент (radio и check)
                    if (jQuery(this).is(':checked')) {
                        min_cost += jQuery(this).data('mincost');
                        max_cost += jQuery(this).data('maxcost');
                    }
                });
                if (min_cost !== 0 && max_cost !== 0) {
                    let locale = _block_cost.data('locale-cost');
                    min_cost = new Intl.NumberFormat(locale).format(min_cost);
                    max_cost = new Intl.NumberFormat(locale).format(max_cost);
                    _block_cost.html(currencyPosition(min_cost,currency) + ' - ' + currencyPosition(max_cost,currency));
                } else {
                    _block_cost.html(currencyPosition(0,  currency));
                }
            })
        } else {
            //Удаляем блок для вывода стоимости
            jQuery('.w39sq-price-block').first().remove();
        }
        widthItemSlider(getWidth());
        pagination(0);

        //События
        jQuery(window).resize(function () {
            widthItemSlider(getWidth());
        });

        _button_prev.addClass('disabled');

        _button_prev.on('click', function () {
            if (_position === 0) return;
            _position--;
            if (_position === 0) _button_prev.addClass('disabled');
            if (_button_next.hasClass('disabled')) _button_next.removeClass('disabled');
            transformSlider(_position);
            pagination(_position);
        });

        _button_next.on('click', function () {
            if (_position === _max_position) return;

            //Проверка на checked для групп радиокнопок
            let _checked = false;
            let slider = jQuery('#slider' + _position);
            slider.find('input').each(function () {
                if ((jQuery(this).attr('name') === 'question' && jQuery(this).data('multi') === 1) || //для checked неактуально
                    jQuery(this).is(':checked')) { //Хотя бы одно поле выбрано
                    _checked = true;
                    return false;
                }
            });
            if (!_checked) {
                slider.addClass('error-check');//Вывод ошибки
                return;
            }

            _position++;
            if (_position === _max_position) _button_next.addClass('disabled');
            _button_prev.removeClass('disabled');
            transformSlider(_position);
            pagination(_position);
        });

        jQuery(document).on('click', '.w39sq-field', function () {
            jQuery('.error-check').each(function (){
                jQuery(this).removeClass('error-check'); //При выборе радиокнопки удаляем со всех класс ошибки
            })
        });

        jQuery(document).on('focus', '.required', function (){
            jQuery(this).removeClass('error'); //Удаляем вывод ошибки для текстовых полей required
            jQuery(this).next().html('');
            /*let span = jQuery(this).prev();
            if (span.hasClass('error-span')){ span.remove();}*/
        });

        jQuery(document).on('click', '#w39sq-submit', function () {
            //Проверяем поля required
            let _not_value = false;
            jQuery('.required').each(function () {
                if (jQuery(this).val() === ''){//Поле required не заполнено, выводим ошибку
                    if (!jQuery(this).hasClass('error')) { //если уже установлен error пропускаем добавление
                        jQuery(this).addClass('error');
                        jQuery(this).next().html('* ' + w39sq_object.required);
                        //jQuery(this).before('<span class="error-span">' + w39sq_object.required + '</span>');
                    }
                    _not_value = true; //Поле не заполнено
                    return false;
                }
            });
            if (_not_value) return;

            let post_data = {questions: [], user: {}};


            jQuery('#w39sq_form .w39sq-slider-item').each(function () { //Проходим по всем слайдам.
                let _q = {question: '', answer: []}; //Заполняем опросник
                let _i = {}; //Заполняем последний экран
                jQuery(this).find('input').each(function () { ///*проходим по всем input в слайде
                    if (jQuery(this).attr('type') === 'hidden' && jQuery(this).attr('name') === 'question') { //Поле с вопросом
                        _q.question = jQuery(this).val();
                    } else if (jQuery(this).attr('type') === 'radio' || jQuery(this).attr('type') === 'checkbox') { //Поля ответов
                        if (jQuery(this).is(':checked')) _q.answer.push(jQuery(this).val());
                    } else if (jQuery(this).attr('type') === 'text' || jQuery(this).attr('type') === 'email') { //Поля последнего слайда
                        _i[jQuery(this).attr('name')] = jQuery(this).val();
                    }
                });
                ///Формируем объект параметров для пост-запроса
                if (_q.question !== '') post_data.questions.push(_q);
                if (!jQuery.isEmptyObject(_i)) post_data.user = _i;
            });

            if (post_data !== {questions: [], user: {}}) {
                jQuery.ajax({
                    type: 'POST',
                    url: w39sq_object.url,
                    data: {
                        action: 'w39sq_request',
                        nonce: w39sq_object.nonce,
                        id: w39sq_object.id,
                        _post: post_data
                    },
                    success: function (data) {
                        jQuery('#w39sq').html(data);
                    },
                    error: function (data) {
                        jQuery('#w39sq-error-block').html(data);
                    }
                });
            }
        });

        //функции
        function transformSlider(position) {
            _slider.css({
                'transition-duration': '300ms',
                'transform': 'translate3d(-' + getWidth() * position + 'px, 0px, 0px)'
            })
        }

        function pagination(position) {
            jQuery('.w39sq-paginator-item').each(function (index) {
                jQuery(this).removeClass('active');
                if (position > index) {
                    jQuery(this).addClass('complete');
                } else {
                    jQuery(this).removeClass('complete');
                }
                if (position === index) {
                    jQuery(this).addClass('active');
                }
            })
        }

        function getWidth() {
            return jQuery('#w39sq').width();
        }

        function widthItemSlider(_width) {//Устанавливаем ширину элемента слайдера
            jQuery('.w39sq-slider-item').each(function (index) {
                jQuery(this).css({width: (_width + 'px')});
            });
        }

        function currencyPosition(value, currency){ //Вывод позиции знака валюты, до суммы или после
            if (w39sq_object.currency === 0) {
                return currency + value;
            } else {
                return value + currency;
            }
        }
    }
});
