<?php
declare(strict_types=1);
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQLoadShort.php';

try {
    W39SQLoadShort::init();

} catch (Exception $e) {
    w39sq_echo_thr($e);
}

