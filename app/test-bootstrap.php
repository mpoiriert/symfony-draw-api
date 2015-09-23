<?php

$loader = require_once(__DIR__ . '/bootstrap.php.cache');

$kernelDir = __DIR__;
$evt = 'test';

$commands = array();
$commands[] = sprintf('php %s/console doctrine:database:drop --if-exists --force --env="%s"', $kernelDir, $evt);
$commands[] = sprintf('php %s/console doctrine:database:create --if-not-exists --env="%s"', $kernelDir, $evt);
$commands[] = sprintf('php %s/console cache:clear --env="%s"', $kernelDir, $evt);
//$commands[] = sprintf('php %s/console doctrine:migrations:migrate --no-interaction --env="%s"', $kernelDir, $evt);
$commands[] = sprintf('php %s/console doctrine:schema:update --force --env="%s"', $kernelDir, $evt);

foreach ($commands as $command) {
    __execute_command($command);
}

function __execute_command($command)
{
    $returnValue = null;
    passthru($command, $returnValue);
    if ($returnValue) {
        echo 'Error with command [' . $command . ']';
        exit($returnValue);
    }
}

return $loader;