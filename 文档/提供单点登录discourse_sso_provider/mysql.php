<?php

/*
quick and dirty key value store for mysql
*/

/*
CREATE DATABASE `notes`;

USE `notes`;

CREATE TABLE IF NOT EXISTS `note` (
  `id` int(11) NOT NULL,
  `key` varchar(200) NOT NULL,
  `value` text NOT NULL
);

ALTER TABLE `note`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
  
*/


//conection: 
$link = mysqli_connect("localhost","root","123456","notes") or die("Error " . mysqli_error($link)); 


function set_key($key, $value){
    global $link;
    $value = serialize($value);
    $query = "SELECT `value` FROM note where `key`='$key'; " or die("Error in the consult.." . mysqli_error($link)); 
    $result = $link->query($query);
    $haskey = mysqli_num_rows($result);
    //print "has key: $haskey<br>";

    if($haskey <= 0){
        $query = "INSERT INTO `notes`.`note` (`id`, `key`, `value`) VALUES (NULL, '$key', '$value'); " or die("Error in the consult.." . mysqli_error($link)); 
        $result = $link->query($query); 
        //print "insert<br>";
    } else {
        $query = "UPDATE `notes`.`note` SET `value` = '$value' WHERE `key`='$key'; " or die("Error in the consult.." . mysqli_error($link)); 
        $result = $link->query($query); 
        //print "UPDATE<br>";
    }
}

function get_key($key){
    global $link;
    //mysqli_escape_string(unescaped_string);
    $query = "SELECT `value` FROM note where `key`='$key'; " or die("Error in the consult.." . mysqli_error($link)); 
    $result = $link->query($query); 
    //print mysqli_error($link);
    $haskey = mysqli_num_rows($result);
    if($haskey==0) return false;
    $row = mysqli_fetch_row($result);
    $value = $row[0];
    mysqli_free_result($result);
    return unserialize($value);
}