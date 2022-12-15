<?php

require_once('../v1/controller/db.php');

try{
    $conn = DB::connectWriteDB();
    echo "OK";
}
catch(PDOException $e){
    echo "Unable to connect to DB: " . $e->getMessage();
}