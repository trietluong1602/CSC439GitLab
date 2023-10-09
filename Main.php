<?php
declare(strict_types=1);

require_once "View.php";
require_once "Controller.php";

class YahtzeeException extends Exception {
}

class YahtzeeDice {
    public function roll(int $n) : array {
        //you do not need to test this method.
        $roll_array = array();
        for($x = 0; $x < $n; $x++){
            $roll_array[] = rand(1, 6);   
        }
        return $roll_array;
    }
}

class Yahtzee {
    
    private YahtzeeDice $dice;
    private array $scorecard;
    private int $turn;
    private array $last_roll;
    private array $kept_dice;
    
    public function __construct(YahtzeeDice $dice) {
        $this->dice = $dice;
        $this->scorecard = array(
            "ones" => NULL,
            "twos" => NULL,
            "threes" => NULL,
            "fours" => NULL,
            "fives" => NULL,
            "sixes" => NULL,
            "three_of_a_kind" => NULL,
            "four_of_a_kind" => NULL,
            "full_house" => NULL,
            "small_straight" => NULL,
            "large_straight" => NULL,
            "chance" => NULL,
            "yahtzee" => NULL
            );
        $this->turn = 1;
        $this->last_roll = array();
        $this->kept_dice = array();
    }
    
    public function get_turn() : int {
        return $this->turn;   
    }
    
    public function get_kept_dice() : array { 
        return $this->kept_dice;
    }
    
    public function get_last_roll() : array { 
        return $this->last_roll;
    }
    
    public function get_scorecard() : array {
        return $this->scorecard;
    }
    
    public function calculate_total_score() : int {
        $sum = array_sum($this->get_scorecard());
        if($this->is_bonus()){
            $sum += 35;
        }
        return $sum;
    }
    
    public function is_bonus() : bool {
        $sum = 0;
        $sum += $this->scorecard["ones"];
        $sum += $this->scorecard["twos"];
        $sum += $this->scorecard["threes"];
        $sum += $this->scorecard["fours"];
        $sum += $this->scorecard["fives"];
        $sum += $this->scorecard["sixes"];
        if($sum >= 63){
            return TRUE;
        } else { 
            return FALSE;
        }
    }
    
    public function increment_turn() : int {
        $this->turn++;
        return $this->turn;
    }
    
    public function roll(int $n) : array { 
        $this->last_roll = $this->dice->roll($n);
        return $this->last_roll;
    }
    
    public function calculate_number(array $roll, int $num) : int {
        $total = 0;
        foreach($roll as $die){
            if($die == $num){
                $total += $die;
            }
        }
        return $total;
    }
    
    public function calculate_n_of_a_kind(array $roll, int $n) : int {
        $frequencies = array_count_values($roll);
        foreach($frequencies as $value => $amount){
            if($amount >= $n){
                return array_sum($roll);
            }
        }
        return 0;
    }
    
    public function calculate_yahtzee(array $roll) : int {
        $frequencies = array_count_values($roll);
        if(in_array(5, $frequencies, TRUE)){
            return 50;
        } else {
            return 0;
        }
    }
    
    public function calculate_full_house(array $roll) : int {
        $frequencies = array_count_values($roll);
        $pair = in_array(2, $frequencies, TRUE);
        $three_of_a_kind = in_array(3, $frequencies, TRUE);
        if($three_of_a_kind === TRUE && $pair === TRUE){
            return 25;
        } else {
            return 0;
        }   
    }
    
    public function longest_straight_sequence(array $roll) : int {
        $sorted_array = $roll; // copy array
        sort($sorted_array);  //sort array
        $sorted_array = array_values(array_unique($sorted_array)); //get unique values and reindex
        //echo "\n\n-------\n";
        //print_r($sorted_array);
        $longest_sequence = 1;
        $current_sequence = 1;
        for($x = 0; $x < count($sorted_array) - 1; $x++){
            if($sorted_array[$x+1] > $sorted_array[$x]){
                $current_sequence++;
                //echo $sorted_array[$x+1] . " > " . $sorted_array[$x] . " .. increase curr_seq to $current_sequence\n";
            } else {  
                $current_sequence = 1;
                //echo $sorted_array[$x+1] . " <= " . $sorted_array[$x] . " .. reset curr_seq to $current_sequence\n";
            }
            if($current_sequence > $longest_sequence){
                $longest_sequence = $current_sequence;
            }
            //echo "max seq: $longest_sequence\n";
        }
        return $longest_sequence;
    }
    
    public function calculate_small_straight(array $roll) : int {
        if($this->longest_straight_sequence($roll) >= 4){
            return 30;
        } else {
            return 0;
        }
    }
    
    public function calculate_large_straight(array $roll): int {
        if($this->longest_straight_sequence($roll) >= 5){
            return 40;
        } else {
            return 0;
        }
    }
    
    public function calculate_chance(array $roll){
        return array_sum($roll);
    }
    
    public function calculate(string $category, array $roll) : int {
        switch($category){
        case "ones":
            return $this->calculate_number($roll, 1);
        case "twos":
            return $this->calculate_number($roll, 2);
        case "threes":
            return $this->calculate_number($roll, 3);
        case "fours":
            return $this->calculate_number($roll, 4);
        case "fives":
            return $this->calculate_number($roll, 5);
        case "sixes":
            return $this->calculate_number($roll, 6);
        case "three_of_a_kind":
            return $this->calculate_n_of_a_kind($roll, 3);
        case "four_of_a_kind":
            return $this->calculate_n_of_a_kind($roll, 4);
        case "full_house":
            return $this->calculate_full_house($roll);
        case "small_straight": 
            return $this->calculate_small_straight($roll);
        case "large_straight":
            return $this->calculate_large_straight($roll);
        case "chance":
            return $this->calculate_chance($roll);
        case "yahtzee":
            return $this->calculate_yahtzee($roll);
        default:
            throw new YahtzeeException("Unknown category: " . $category);
            
        }
    }
    
    public function update_scorecard(string $category, int $value) : void {
        if(!array_key_exists($category, $this->scorecard)){
            throw new YahtzeeException("Unknown category: " . $category);   
        } else {
            $this->scorecard[$category] = $value;   
        }
    }
    
    public function keep_by_index(string $idx_string) : int {
        $idxs = explode(" ", $idx_string);
        foreach($idxs as $idx){
            if(is_numeric($idx) == FALSE){
                throw new YahtzeeException("Invalid indexes.");
            }
        }
        if(count($idxs) + count($this->kept_dice) > 5){
            throw new YahtzeeException("Tried to keep too many dice.");  
        } else {
            foreach($idxs as $idx){
                $this->kept_dice[] = $this->last_roll[(int)$idx];   
            }
        }
        $remaining = 5 - count($this->kept_dice);
        return $remaining;
    }
    
    public function combine_dice() : void {
        if(count($this->kept_dice) < 5){
            $this->kept_dice = array_merge($this->kept_dice, $this->last_roll);
        }
    }
    
    public function clear_kept_dice() : void {
        $this->kept_dice = array();
    }
    
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

function cli_input(string $prompt) : string {
    //You do not need to test this function.
    echo $prompt;
    $stdin = fopen("php://stdin", "r");
    $line = fgets($stdin);
    fclose($stdin);
    return trim($line);
}

if( isset($argv) && $argv[0] && realpath($argv[0]) == __FILE__){
    /*
    this block of code will only run if it is the main file run, if it is
    imported, then it will not be run.
    */
    
    /* OLD CODE FOR REFERENCE
    
    $d = new YahtzeeDice();
    $y = new Yahtzee($d);
    while($y->get_turn() < 14){
        $turn = $y->get_turn();
        echo "======== TURN $turn/13 ========\n";
        $remaining = 5;
        for($x = 0; $x < 2; $x++){
            echo "Rolled: ";
            echo array_to_string($y->roll($remaining)) . "\n";
            echo "Your dice: ";
            echo array_to_string($y->get_kept_dice()) . "\n";
            $line = cli_input("Keep? ");
            if($line == "exit" || $line == "q"){
                exit(0);
            } else if ($line == "all"){
                $y->combine_dice();
                $remaining = 0;
            }
            else if ($line == "none" || $line == "pass" || $line == "") {
                //remaining stays the same
            }
            else {
                try {
                    $remaining = $y->keep_by_index($line);
                } catch(YahtzeeException $e) {
                    echo $e->getMessage() . "\n";
                    //remaining stays the same   
                }
            }
            if($remaining == 0){
                break;
            }
        }
        if(count($y->get_kept_dice()) < 5){
            echo "Rolled: ";
            echo array_to_string($y->roll($remaining)) . "\n";
            $y->combine_dice();
        }
        
        
        echo "Your final dice: ";
        echo array_to_string($y->get_kept_dice()) . "\n";
        
        echo "Your dice qualify for the following categories:\n";
        foreach($y->get_scorecard() as $category=>$score){
            if($score === NULL){
                $new_score = $y->calculate($category, $y->get_kept_dice());
                printf("%-18s%d\n",$category,$new_score);
            }
        }
        
        //NOTE: in the new version, it just asks once, and if it is invalid, it 
        //will choose the first available category and score it.
        while(TRUE){
            try{
                $line = cli_input("Category? ");
                $value = $y->calculate($line, $y->get_kept_dice());
                $y->update_scorecard($line, $value);
                break;
            } catch(YahtzeeException $e){
                echo $e->getMessage() . "\n";  
            }
        }
        
        foreach($y->get_scorecard() as $category=>$score){
            printf("%-18s%d\n",$category,$score);
        }
        echo "Bonus: " . ($y->is_bonus() ? "YES, +35" : "NO") . "\n";
        echo "Total score: " . $y->calculate_total_score() . "\n";
        
        $y->clear_kept_dice();
        $y->increment_turn();
    }
    */
    $d = new YahtzeeDice();
    $model = new Yahtzee($d);
    $view = new YahtzeeView($model);
    $controller = new YahtzeeController($model, $view);
    return $controller->main_loop();
}

?>