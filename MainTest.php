<?php
require_once 'Main.php';

use PHPUnit\Framework\TestCase;

class MainTest extends TestCase
{
    private Yahtzee $sut;
    
    public function setUp() : void {
        $d = new YahtzeeDice();
        $this->sut = new Yahtzee($d);   
    }
    
    /**
    * @covers \Yahtzee::longest_straight_sequence
    */
    public function test_longest_straight_sequence(){
        $result = $this->sut->longest_straight_sequence(array(1,2,3,4,4));
        $this->assertEquals(4, $result);
        
        $result = $this->sut->longest_straight_sequence(array(1,1,2,3,4));
        $this->assertEquals(4, $result);
        
        $result = $this->sut->longest_straight_sequence(array(5,4,3,2,1));
        $this->assertEquals(5, $result);
        
        $result = $this->sut->longest_straight_sequence(array(1,1,1,1,1));
        $this->assertEquals(1, $result);
        
        $result = $this->sut->longest_straight_sequence(array(1,2,3,1,2));
        $this->assertEquals(3, $result);
        
    }
    
    
}

?>