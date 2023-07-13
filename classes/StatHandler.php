<?php
/**
 * IPPcode23 project - part 1
 * 
 * @author Jan Osuský  
 * 
 *      xosusk00
 * 
 *      BUT FIT
 * 
 *     March 2023
 */

/**
 * Class StatHandler for handling statistics 
 * Implements Singleton pattern
 */
class StatHandler {
//singleton variable
    private static $instance = null;
//stat array
    private $statistics;

// definition for all the var for all the possible flags
// ['loc', 'comments', 'labels', 'jumps', 'fwjumps', 'backjumps', 'badjumps', 'frequent', 'print:', 'eol']
    public $loc = 0;

    public $comments = 0;

    public $labels = 0;

    public $jumps = 0;

    public $fwjumps = 0;

    public $backjumps = 0;

    public $badjumps = 0;

    public $frequent = array();


    //aplying singleton pricipal
    public static function instantiate() 
    {
        if(StatHandler::$instance === null) {
            StatHandler::$instance = new StatHandler();
        }
        return StatHandler::$instance;
    }

    // setting the arrays with name of file and flags
    public function set($statistics)
    {
        $this->statistics = $statistics;
    }
    
}
?>