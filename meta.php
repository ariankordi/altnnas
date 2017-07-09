<?php

function nn_compatible_passwd(int $pid, string $password) {
return hash('sha256', (pack('I*', $pid)) . hex2bin('02654346') . mb_convert_encoding($password, 'ascii'));
}

