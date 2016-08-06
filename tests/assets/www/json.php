<div class="json"><?php

    $rawpost = file_get_contents('php://input');
    $rawquery = filter_input(INPUT_SERVER, 'QUERY_STRING');
    $rawcookie = filter_input(INPUT_SERVER, 'HTTP_COOKIE');

    echo json_encode(compact('_GET', '_POST', '_COOKIE', 'rawpost', 'rawquery', 'rawcookie'));

?></div>
