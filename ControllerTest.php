<?php
require_once 'Main.php';

use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    private $model;
    private $view;
    private $sut;

    public function setUp() : void {
        $d = new YahtzeeDice();
        $this->model = new Yahtzee($d);
        $this->view = $this->createStub(YahtzeeView::class);
        $this->sut = new YahtzeeController($this->model, $this->view);
    }

    public function test_get_model() {
        $result = $this->sut->get_model();
        $this->assertNotNull($result);
    }

    public function test_get_view() {
        $result = $this->sut->get_view();
        $this->assertNotNull($result);
    }

    public function test_get_possible_categories() {
        //test when the score card has no value in it
        $scard = $this->model->get_scorecard();
        $result = $this->sut->get_possible_categories();
        $this->assertEquals($scard, $result);

        //test when there are some categories already have value in it
        $this->model->update_scorecard("ones", 6);
        $this->model->update_scorecard("three_of_a_kind", 9);
        unset($scard["ones"]);
        unset($scard["three_of_a_kind"]);
        $result = $this->sut->get_possible_categories();
        $this->assertEquals($scard, $result);

        //every categories have a value
        foreach($scard as $category => $value) {
            $this->model->update_scorecard($category, 2);
        }
        $result = $this->sut->get_possible_categories();
        $this->assertEmpty($result);
    }

    public function test_process_score_input() {
        //user input is "exit" or "q"
        $result = $this->sut->process_score_input("exit");
        $this->assertEquals($result, -1);
       
        //user input is valid
        $scard = $this->model->get_scorecard();
        $this->assertNull($scard["twos"]);
        $this->model->roll(5);
        $this->model->combine_dice();
        $result = $this->sut->process_score_input("twos");
        $scard = $this->model->get_scorecard();
        $this->assertNotNull($scard["twos"]);
        $this->assertEquals($result, 0);

        //user input is invalid
        $result = $this->sut->process_score_input("ones");
        $scard = $this->model->get_scorecard();
        $this->assertNotNull($scard["ones"]);
        $this->assertNull($scard["threes"]);
        $result = $this->sut->process_score_input("zeros");
        $scard = $this->model->get_scorecard();
        $this->assertEquals($result, -2);
        $this->assertNotNull($scard["threes"]);
    }

    public function test_process_keep_input() {
        //user input is "exit" or "q"
        $result = $this->sut->process_keep_input("q");
        $this->assertEquals($result, -1);

        //user input is all
        $this->model->clear_kept_dice();
        $dice = $this->model->get_kept_dice();
        $this->assertEmpty($dice);
        $rolled = $this->model->roll(5);
        $result = $this->sut->process_keep_input("all");
        $dice = $this->model->get_kept_dice();
        $this->assertEquals($dice, $rolled);
        $this->assertEquals($result, 0);

        //user input is "none", "pass" or ""
        $this->model->clear_kept_dice();
        $dice = $this->model->get_kept_dice();
        $this->assertEmpty($dice);
        $result = $this->sut->process_keep_input("pass");
        $dice = $this->model->get_kept_dice();
        $this->assertEmpty($dice);
        $this->assertEquals($result, -2);

        //user input is valid
        $this->model->clear_kept_dice();
        $dice = $this->model->get_kept_dice();
        $this->assertEmpty($dice);
        $rolled = $this->model->roll(5);
        $kept_rolled = array($rolled[0], $rolled[1], $rolled[2]);
        $result = $this->sut->process_keep_input("0 1 2");
        $dice = $this->model->get_kept_dice();
        $this->assertEquals($dice, $kept_rolled);
        $this->assertEquals($result, 2);

        //user input is invalid
        $rolled = $this->model->roll(2);
        $result = $this->sut->process_keep_input("0 1 2");
        $this->assertEquals($result, -2);
    }
}

?>