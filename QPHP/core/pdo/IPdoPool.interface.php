<?php


interface IPdoPool
{
    static public function Connect($dbKey='mysql_0',$dbType="mysql");
}
