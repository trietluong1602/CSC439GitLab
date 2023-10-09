<?php
declare(strict_types=1);

class YahtzeeView {
    private Yahtzee $model;
    
    public function __construct(Yahtzee $model){
        $this->model = $model;
    }
    
    function array_to_string(array $a) : string {
        $output = "";
        foreach($a as $value){
            $output .= "$value, ";
        }
        $output = substr($output, 0, -2);
        $output = "[" . $output;
        $output .= "]";
        return $output;
    }
    
    function get_user_input(string $prompt) : string {
        //You do not need to test this function.
        echo $prompt;
        $stdin = fopen("php://stdin", "r");
        $line = fgets($stdin);
        fclose($stdin);
        return trim($line);
    }
    
    public function output_turn(){
        $turn = $this->model->get_turn();
        echo "======== TURN $turn/13 ========\n"; 
    }
    
    public function output_last_roll() {
        echo "Rolled: " . $this->array_to_string($this->model->get_last_roll()) . "\n";
    }
    
    public function output_kept_dice() {
        echo "Your dice: " . $this->array_to_string($this->model->get_kept_dice()) . "\n";
    }
    
    public function output_array(array $array) {
        foreach($array as $key=>$value){
            printf("%-18s%d\n",$key,$value);
        }   
    }
    
    public function output_score() {
        foreach($this->model->get_scorecard() as $category=>$score){
            printf("%-18s%d\n",$category,$score);
        }
        echo "Bonus: " . ($this->model->is_bonus() ? "YES, +35" : "NO") . "\n";
        echo "Total score: " . $this->model->calculate_total_score() . "\n";   
    }
    
    public function output(string $msg) {
        echo $msg . "\n";
    }
}

?>
