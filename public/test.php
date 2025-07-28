<?php
file_put_contents('/var/www/html/test.log', "Test PHP OK\n");
echo json_encode(['test' => 'success']);
