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
 require_once('StatHandler.php');

 define("TOKEN_EOF", 1);
 define("TOKEN_CONST", 2);
 define("TOKEN_VAR", 3);
 define("TOKEN_LABEL", 4);
 define("TOKEN_INSTR", 5);
 define("TOKEN_TYPE", 6);


/**
 * Implementation of class scanner for the lexical analysis
 * Use of signleton pattern
 * Implements Factory pattern for creating Tokens
 */
class Scanner 
{
    //vars for objects of handling errors, stats and stdin
    private $errHandler;

    private $statHandler; //maybe not writing the information to the vars in StatHandler.php

    private $stdin;
    //only one instance of Scanner
    private static $instance;
    // "false" is in array to shift the instruction MOVE to position 1
    private $instrArray = array("false", "MOVE", "CREATEFRAME", "PUSHFRAME", "POPFRAME", "DEFVAR", "CALL", "RETURN", "PUSHS", "POPS", "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", "INT2CHAR", "STRI2INT", "READ", "WRITE", "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", "TYPE", "LABEL", "JUMP", "JUMPIFEQ", "JUMPIFNEQ", "EXIT", "DPRINT", "BREAK");
    private $lineCount = 1;

    //private constructor 
    private function __construct($errHandler, $statHandler, $stdin)
    {
        $this->errHandler = $errHandler;
        $this->statHandler = $statHandler;
        $this->stdin = $stdin;
    }

    //applying singleton pattern
    public static function instantiate($errHandler, $statHandler, $stdin)
    {
        if(Scanner::$instance === null)
        {
            Scanner::$instance = new Scanner($errHandler, $statHandler, $stdin);
        }
        return Scanner::$instance;
    }
    
    public function getNextLine()
    {
        //array for storing tokens in the line
        $newLine = array();

        //indicates wheater we should search for instruction
        $instruction = true;
        
        //used only with the first line
        if($this->lineCount === 1)
        {
            if(!$line = fgets($this->stdin))
                $this->errHandler->printErrorNExit(OPENING_FILE_ERROR, "ERROR: No input!");
            $comments = 0;
            $line = strtolower(trim(preg_replace("/#.*$/", "", $line, -1, $comments)));	//remove commentary and white spaces
            if($line != ".ippcode23") //header presence solved by lex analysis 
                $this->errHandler->printErrorNExit(HEADER_ERROR, "ERROR: Invalid header!");
            $this->statHandler->comments += $comments;
            $this->lineCount++; 
        }

        while(true)
        {
            //checking whether there is not the end of file
            if(($line = fgets($this->stdin)) == false)
            {
                array_push($newLine, $token = new Token(TOKEN_EOF, 0, 0));
                return $newLine;
            }

            //ignoring comments and updating stats
            if(preg_match('/^\s*#/', $line))
            {
                $this->statHandler->comments++;
                $this->lineCount++;
                continue;
            }

            //ingoring white spaces and updating line counter
            if(preg_match('/^\s*$/', $line))
            {
                $this->lineCount++;
                continue;
            }
            
            $seperateComments = explode('#', $line); //seperating possible comments behind the instructions
           # if($seperateComments[1] !== null) //MAYBE need to use a different comparison
           # {
           #     $this->statHandler->comments++;
           # }
            $wordLine = preg_split('/\s+/', $seperateComments[0]);
            
            //removing empty strings from the end and begining of the array
            if (end($wordLine) == "") 
            {
                array_pop($wordLine);
            }
            if ($wordLine[0] == "") {
                array_shift($wordLine);
            }
            break;
        }

        //iterating over all the words contained in one line
        foreach($wordLine as $palabra)
        {
            //checking whether $palabra is a constant or variable
            //palabra == string/word in spanish
            if(preg_match('/@/', $palabra))
                {
                    //checking constant
                    if(preg_match('/^(int|nil|string|bool)/', $palabra))
                    {
                        //checks the part after type@
                        if(preg_match('/^int@[+-]?[0-9]+$/', $palabra) || preg_match('/^nil@nil$/', $palabra) || preg_match('/^bool@(true|false)$/', $palabra) || preg_match('/^string@$/', $palabra) || (preg_match('/^string@/', $palabra) && !preg_match('/(\\\\($|\p{S}|\p{P}\p{Z}|\p{M}|\p{L}|\p{C})|(\\\\[0-9]{0,2}($|\p{S}|\p{P}\p{Z}|\p{M}|\p{L}|\p{C}))| |#)/', $palabra)))
                        {
                            $splitConst = explode('@', $palabra, 2);
                            array_push($newLine , new Token(TOKEN_CONST, $splitConst[0], $splitConst[1]));
                        }
                        else
                        {
                            $this->errHandler->printErrorNExit(LEX_SYNTAX_ERROR, "At line {$this->lineCount} is lexical error in {$palabra}!\n");
                        }
                    }
                    // checking variable 
                    else
                    {
                        if(preg_match('/^(GF|TF|LF)@[a-zA-Z_\-$&%*!?]*$/', $palabra))
                        {
                            array_push($newLine, new Token(TOKEN_VAR, $palabra, 0));
                        }
                        else
                        {
                            $this->errHandler->printErrorNExit(LEX_SYNTAX_ERROR, "At line {$this->lineCount} is lexical error in {$palabra}!\n");
                        }
                    }
                }
                elseif (preg_match('/^(int|bool|string|nil)$/', $palabra))
                {   
                    //only the type
                    array_push($newLine, new Token(TOKEN_TYPE, $palabra, 0));
                }
                //checking whether it is not an instruction
                elseif($instruction && $instNumber = array_search(strtolower($palabra), array_map('strtolower', $this->instrArray)))
                { 
                    array_push($newLine, new Token(TOKEN_INSTR, $instNumber, 0));
                }
                else
                {
                    // if it not instruction it can only be label or bad code
                    if(!preg_match('/^[a-zA-Z_\-$&%*!?][a-zA-Z0-9_\-$&%*!?]*$/', $palabra))
                        $this->errHandler->printErrorNExit(OPCODE_ERROR, "At line {$this->lineCount} is not recognizable opcode {$palabra}!\n");
                    
                        array_push($newLine, new Token(TOKEN_LABEL, $palabra, 0));
                }
           $instruction = false;  // we are no longer expecting instruction in this line
        }
        $this->lineCount++;
        $this->statHandler->loc++;
        return $newLine; //array of tokens of the line
    }
}
?>