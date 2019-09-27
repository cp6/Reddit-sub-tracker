<?php
require_once('rdt-track.php');

$call = new tracker();
$call->track('nba', 25);//NBA sub, latest 25 posts
$call->insertData(1);//Insert new data and if post already exists do an update of upvotes/comments