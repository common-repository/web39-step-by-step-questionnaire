<?php
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQ.php';

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class W39SQTableList extends WP_List_Table
{

    private string $paramsSQL = '';

    public function __construct($args = array())
    {
        //Параметры для других функций
        parent::__construct( array(
            'singular' => 'id', //используем в поле cb для именования параметра массива выбранных значений
            'plural' => 'posts',
            'ajax' => false,
        ) );

        $this->_args['plural'] = 'w39sq';
    }

    public function config($arg = [])
    {
        //Извлекаем GET параметры и формируем условия для отображения в $paramSQL
        if (isset($arg['orderby']) && isset($arg['order'])) { //2 параметр проверяется на случай нестандартной ошибки
            //при обычной работе если есть orderby, то и существует order
            $this->paramsSQL = 'ORDER BY '. $arg['orderby'] . ' ' . strtoupper($arg['order']);
        } else {
            $this->paramsSQL = 'ORDER BY updated_at DESC';
        }
    }

    function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'name' => __('Название', 'w39sq'),
            'shortcode' => __('Шорткод', 'w39sq'),
            'title' => __('Заголовок', 'w39sq'),
            'date'=> __('Дата', 'w39sq'),
        ];
        return $columns;
    }

    function prepare_items()
    {

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns(); //Сортировка
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = W39SQ::findAll($this->paramsSQL);
    }

    protected function get_sortable_columns() {
        $columns = [
            'name' =>  ['name', true ],
            'title' => array( 'title', false ),
            'date' => array( 'created_at', false ),
            ];

        return $columns;
    }

    /**Действия над записью при наведении на ячейку*/
    protected function handle_row_actions($item, $column_name, $primary)
    {
        if ($column_name !== $primary) return '';
        //Добавляем GET-параметры к menu
        $edit_link = add_query_arg(
            ['id' => absint($item['id']), 'action' => 'edit',],
            menu_page_url('w39sq', false)
        );
        $copy_link = add_query_arg(
            ['id' => absint($item['id']), 'action' => 'copy',],
            menu_page_url('w39sq', false)
        );
        $actions = [
            'edit' => $this->_getTag($edit_link, __('Изменить', 'w39sq')), //'<a href="' . $edit_link . '">' . __('Изменить', 'w39sq') . '</a>',
            'copy' => $this->_getTag($copy_link, __('Дублировать', 'w39sq')), //'<a href="' . $copy_link . '">' . __('Дублировать', 'w39sq') . '</a>',
        ];

        return $this->row_actions($actions);
    }

    //Действия над выделенными записями
    protected function get_bulk_actions() {
        return [
            'delete' => __( 'Удалить', 'w39sq' ),
        ];
    }
    //*** ОПРЕДЕЛЕНИЕ СТОЛБЦОВ
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'], //name значение определяется в конструкторе
            $item['id'] //value
        );
    }

    function column_name($item)
    {
        //Формируем ссылку на страницу с параметрами. page берем из меню w39sq
        $edit_link = add_query_arg(
            ['id' => absint($item['id']), 'action' => 'edit'],
            menu_page_url('w39sq', false));

        $output = sprintf(
            '<a class="row-title" href="%1$s" aria-label="%2$s">%3$s</a>',
            esc_url($edit_link),
            //добавляем кавычки и между ними вставляем поле name
            esc_attr(sprintf(__('Изменить &#8220;%s&#8221;', 'w39sq'), $item['name'])),
            esc_html($item['name'])
        );
        return $output;
    }

    function column_title($item)
    {
        return $item['title'];
    }

    function column_shortcode($item)
    {
        $shortcode = '[w39sq id="' . $item['id'] . '"]';
        return '<span class="shortcode"><input type="text"'
            . ' onfocus="this.select();" readonly="readonly"'
            . ' value="' . esc_attr($shortcode) . '"'
            . ' class="large-text code" /></span>';
    }

    function column_date($item)
    {
        return date('d-m-Y H:m', $item['created_at']);
    }

    function column_default($item, $column_name)
    {
        return '';
    }

//*** Вспомогательные функции
    private function _getTag($link, $caption) {
        return '<a href="' . $link . '">' . __($caption, 'w39sq') . '</a>';
    }
}