<?php

use PHPUnit\Framework\TestCase;

require 'dbHandle.php';

class dbHandleTest extends TestCase
{

    public function test__construct()
    {
        $db = new dbHandle(1021,'root', '','test');

        $this->assertNotEmpty($db -> getHost());
        $this->assertNotEmpty($db -> getName());
        $this->assertNotEmpty($db -> getDB());
        
        $this->assertEquals(1021,$db -> getHost());
        $this->assertEquals('root',$db -> getName());
        $this->assertEquals('',$db -> getPW());
        $this->assertEquals('test',$db -> getDB());
    }

    public function testOpenDB()
    {
        $db = new dbHandle(1021,'root', '','test');
        $db->openDB();
        $this->assertNotEmpty($db -> getPdo());
        $this->assertInternalType('PDO', $db->getPdo());
    }


    public function testSelectData()
    {

        $sql = "SELECT *FROM dataset WHERE Model REGEXP 'Agfa ePhoto 1280'
                                      AND YEAR(ReleaseDate)>= 1997
                                      AND YEAR(ReleaseDate)<= 1997
                                      AND Price >= 179.0
                                      AND Price <= 179.0";

        $db = new dbHandle(1021,'root', '','test');
        $this->assertNotEmpty($db->selectData($sql));

        $sql = "SELECT *FROM dataset WHERE Model REGEXP 'Agfa ePhoto 1280'
                                      AND YEAR(ReleaseDate)>= 1997
                                      AND YEAR(ReleaseDate)<= 1995
                                      AND Price >= 179.0
                                      AND Price <= 160.0";

        $db = new dbHandle(1021,'root', '','test');
        $this->assertEmpty($db->selectData($sql));
    }

    public function testSelectDataReturnArray()
    {

        $sql = "SELECT *FROM dataset WHERE Model REGEXP 'Agfa ePhoto 1280'
                                      AND YEAR(ReleaseDate)>= 1997
                                      AND YEAR(ReleaseDate)<= 1997
                                      AND Price >= 179.0
                                      AND Price <= 179.0";

        $db = new dbHandle(1021,'root', '','test');
        $this->assertNotEmpty($db->selectDataReturnArray($sql));
        $this->assertEquals('Agfa ePhoto 1280;1997;1024.0;640.0;0.0;38.0;114.0;70.0;40.0;4.0;420.0;95.0;179.0',$db->selectDataReturnArray($sql));
    }
}
