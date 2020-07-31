<?php

require_once __DIR__. '/../../../autoload.php';

(new \Raketman\DatabasePartitionProcessor\Command\CreateCommand())->run($argv);

?>
