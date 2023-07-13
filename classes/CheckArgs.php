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

require_once('Token.php');
require_once('ErrHandler.php');
require_once('Scanner.php');

 /**
  * Class only with methods made for checking the valididy of args
  * Uses syngleton pattern
  */
 class CheckArgs 
 {
    private static $instance;

    private $errHandler;

    private function __construct($errHandler) {
        $this->errHandler = $errHandler;
    }

    public static function instantiate($errHandler)
    {
        if(CheckArgs::$instance === null)
        {
            CheckArgs::$instance = new CheckArgs($errHandler);
        }
        return CheckArgs::$instance;
    }

    /**
     * checking number of allowed arguments
     * @param line of code
     * @param number of arguments
     */
    public function number($line, $number)
    {
        if(count($line) != ($number + 1)) // arguments + the token of the instruction
        {
            $token = $line[0];
            $this->errHandler->printErrorNExit(LEX_SYNTAX_ERROR, "Instruction number {$token->GetValueA()} requires this $number of args!\n");
        }
    }
    /**
     * checking whether is used the right type of args
     * @param line of code
     * @param type of token
     */
    public function type($line, $type)
    {
        $token = $line[1];
        $tokenMother = $line[0];
        if($token->getType() != $type)
            $this->errHandler->printErrorNExit(LEX_SYNTAX_ERROR, "instruction number{$tokenMother->GetValueA()} has wrong type of arg!\n");
    }
    //whatever args
    public function symb($line, $argNum)
    {
        $tokenMother = $line[0];
        $token = $line[$argNum];
        if($token->getType() != TOKEN_VAR && $token->getType() != TOKEN_CONST)
            $this->errHandler->printErrorNExit(LEX_SYNTAX_ERROR, "instruction number{$tokenMother->GetValueA()} has wrong type of arg!\n");
    }
 }
?>