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

 require_once('Scanner.php');
 require_once('StatHandler.php');
 require_once('ErrHandler.php');
 require_once('Token.php');
 require_once('CheckArgs.php');


 /**
  * Class made for the sysntaxis analysis and por generating the xml
  * Implemented with singleton pattern
  */
 Class Parser
 {
    //vars for objects of handling errors, stats, stdin and stdout
    private $statHandler;

    private $errHandler;

    private $scanner;

    private $checkArgs;

    private $stdin;

    private $stdout;

    private $instrArray = array("false", "MOVE", "CREATEFRAME", "PUSHFRAME", "POPFRAME", "DEFVAR", "CALL", "RETURN", "PUSHS", "POPS", "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", "INT2CHAR", "STRI2INT", "READ", "WRITE", "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", "TYPE", "LABEL", "JUMP", "JUMPIFEQ", "JUMPIFNEQ", "EXIT", "DPRINT", "BREAK");

    private $instrVal = array( "MOVE" => 1 , "CREATEFRAME" => 2, "PUSHFRAME" => 3, "POPFRAME" => 4, "DEFVAR" => 5, "CALL" => 6, "RETURN" => 7, "PUSHS" => 8, "POPS" => 9, "ADD" => 10, "SUB" => 11, "MUL" => 12, "IDIV" => 13, "LT" => 14, "GT" => 15, "EQ" => 16, "AND" => 17, "OR" => 18, "NOT" => 19, "INT2CHAR" => 20, "STRI2INT" => 21, "READ" => 22, "WRITE" => 23, "CONCAT" => 24, "STRLEN" => 25, "GETCHAR" => 26, "SETCHAR" => 27, "TYPE" => 28, "LABEL" => 29, "JUMP" => 30, "JUMPIFEQ" => 31, "JUMPIFNEQ" => 32, "EXIT" => 33, "DPRINT" => 34, "BREAK" => 35);
    // only instance of Parser class
    private static $instance;

    //private constructor 
    private function __construct($errHandler, $statHandler, $stdin, $stdout)
    {
        $this->errHandler = $errHandler;
        $this->statHandler = $statHandler;
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->scanner = Scanner::instantiate($this->errHandler, $this->statHandler, $this->stdin);
        $this->checkArgs = CheckArgs::instantiate($this->errHandler);
    }

    //applying singleton pattern
    public static function instantiate($errHandler, $statHandler, $stdin, $stdout)
    {
        if(Parser::$instance === null)
        {
            Parser::$instance = new Parser($errHandler, $statHandler, $stdin, $stdout);
        }
        return Parser::$instance;
    }

    /**
     * Main function of the class
     * Performs syntaxic analysis and generates final xml file
     */
    public function parse()
    {
        //presence of header is tested by scanner
        //implicitely creating header of the xml file and supposing that the scanner would exit in case it wasnt there
        $document = new DomDocument("1.0", "UTF-8");
	    $document->formatOutput = true;
        $program = $document->createElement('program');
        $program->setAttribute('language', 'IPPcode23');
        $program = $document->appendChild($program);

        $order = 0; // number of instruction 
        $line = array(); // analyzed line of code
        while(true)
        {
            $line = $this->scanner->getNextLine();
            $token = $line[0];
            //end of the program
            if($token->getType() === TOKEN_EOF)
                break;
            elseif(!($token->getType() === TOKEN_INSTR))    //or Instruction nothing else
                $this->errHandler->printErrorNExit(LEX_SYNTAX_ERROR, "ERROR: At the begining of the line is expected instruction!\n");
        
            //incrementing the instruction number
            $order++;
            //generating of instruction in xml
            $instruction = $document->createElement("instruction");
            $instruction->setAttribute("order", $order);
            $instruction->setAttribute("opcode", $this->instrArray[$token->getValueA()]);

            /**
             * Checking arguments of the given instructions
             * Instructions are divided into groups, depending on how many arguments they accept
             */
            switch ($token->getValueA())
            { //case $this->instrVal[""]:
                case $this->instrVal["MOVE"]: case $this->instrVal["INT2CHAR"]: case $this->instrVal["STRLEN"]: case $this->instrVal["TYPE"]: case $this->instrVal["NOT"]:
                //agr1: var arg2: symb
                $this->checkArgs->number($line, 2);
                $this->checkArgs->type($line, TOKEN_VAR);
                $this->checkArgs->symb($line, 2);

                //xml generation arg1
                $token = $line[1];
                $arg = $document->createElement("arg1", htmlspecialchars($token->getValueA()));
                $arg->setAttribute("type", "var");
                $instruction->appendChild($arg);

                //xml generatioin arg2
                $token = $line[2];
                if($token->getType() == TOKEN_VAR) //either variable or constant, has to be distingushed
                {
                    $arg = $document->createElement("arg2", htmlspecialchars($token->getValueA()));
                    $arg->setAttribute("type", "var");
                }
                else
                {
                    $arg = $document->createElement("arg2", htmlspecialchars($token->getValueB()));
                    $arg->setAttribute("type", $token->getValueA());
                }
                $instruction->appendChild($arg);
                break;


                case $this->instrVal["READ"]: 
                //arg1: var arg2: type
                $this->checkArgs->number($line, 2);
                $this->checkArgs->type($line, TOKEN_VAR);

                //xml generation arg1 & arg2
                $token1 = $line[1];
                $token2 = $line[2];
                if($token2->getType() == TOKEN_TYPE)
                {
                    $arg = $document->createElement("arg1", htmlspecialchars($token1->getValueA()));
                    $arg->setAttribute("type", "var");
                    $instruction->appendChild($arg);

                    $arg = $document->createElement("arg2", htmlspecialchars($token2->getValueA()));
                    $arg->setAttribute("type", "type");
                    $instruction->appendChild($arg);
                } 
                else
                {
                    $this->errHandler->printErrorNExit(LEX_SYNTAX_ERROR, "Invalid argument of TYPE instrustion!\n");
                }
                break;


                case $this->instrVal["ADD"]: case $this->instrVal["SUB"]: case $this->instrVal["MUL"]: case $this->instrVal["IDIV"]: case $this->instrVal["LT"]: case $this->instrVal["GT"]: case $this->instrVal["EQ"]: case $this->instrVal["AND"]: case $this->instrVal["OR"]: case $this->instrVal["STRI2INT"]: case $this->instrVal["CONCAT"]: case $this->instrVal["GETCHAR"]: case $this->instrVal["SETCHAR"]:
                //arg1: var arg2: symb1 arg3: symb2
                $this->checkArgs->number($line, 3);
                $this->checkArgs->type($line, TOKEN_VAR);
                $this->checkArgs->symb($line, 2);
                $this->checkArgs->symb($line, 3);

                //xml generation arg1
                $token = $line[1];
                $arg = $document->createElement("arg1", htmlspecialchars($token->getValueA()));
                $arg->setAttribute("type", "var");
                $instruction->appendChild($arg);

                //xml generation arg2
                $token = $line[2];
                if($token->getType() == TOKEN_VAR) //either variable or constant, has to be distingushed
                {
                    $arg = $document->createElement("arg2", htmlspecialchars($token->getValueA()));
                    $arg->setAttribute("type", "var");
                }
                else
                {
                    $arg = $document->createElement("arg2", htmlspecialchars($token->getValueB()));
                    $arg->setAttribute("type", $token->getValueA());
                }
                $instruction->appendChild($arg);

                //xml generation arg3
                $token = $line[3];
                if($token->getType() == TOKEN_VAR) //either variable or constant, has to be distingushed
                {
                    $arg = $document->createElement("arg3", htmlspecialchars($token->getValueA()));
                    $arg->setAttribute("type", "var");
                }
                else
                {
                    $arg = $document->createElement("arg3", htmlspecialchars($token->getValueB()));
                    $arg->setAttribute("type", $token->getValueA());
                }
                $instruction->appendChild($arg);
                break;


                case $this->instrVal["DEFVAR"]: case $this->instrVal["POPS"]:
                //arg1: var
                $this->checkArgs->number($line, 1);
                $this->checkArgs->type($line, TOKEN_VAR);

                //xml generation
                $token = $line[1];
                $arg = $document->createElement("arg1", htmlspecialchars($token->getValueA()));
                $arg->setAttribute("type", "var");
                $instruction->appendChild($arg);
                break;
                

                case $this->instrVal["PUSHS"]: case $this->instrVal["WRITE"]: case $this->instrVal["EXIT"]: case $this->instrVal["DPRINT"]:
                //arg1: symb
                $this->checkArgs->number($line, 1);
                $this->checkArgs->symb($line, 1);

                //xml generation
                $token = $line[1];
                if($token->getType() == TOKEN_VAR) //either variable or constant, has to be distingushed
                {
                    $arg = $document->createElement("arg1", htmlspecialchars($token->getValueA()));
                    $arg->setAttribute("type", "var");
                }
                else
                {
                    $arg = $document->createElement("arg1", htmlspecialchars($token->getValueB()));
                    $arg->setAttribute("type", $token->getValueA());
                }
                $instruction->appendChild($arg);
                break;


                case $this->instrVal["CALL"]: case $this->instrVal["LABEL"]: case $this->instrVal["JUMP"]:
                //arg1: label
                $this->checkArgs->number($line, 1);
                $this->checkArgs->type($line, TOKEN_LABEL);

                //xml generation
                $token = $line[1];
                $arg = $document->createElement("arg1", htmlspecialchars($token->getValueA()));
                $arg->setAttribute("type", "label");
                $instruction->appendChild($arg);
                break;

                
                case $this->instrVal["JUMPIFEQ"]: case $this->instrVal["JUMPIFNEQ"]:
                //arg1: label arg2: symb1 arg3: symb2
                $this->checkArgs->number($line, 3);
                $this->checkArgs->type($line, TOKEN_LABEL);
                $this->checkArgs->symb($line, 2);
                $this->checkArgs->symb($line, 3);

                //xml generation arg1
                $token = $line[1];
                $arg = $document->createElement("arg1", htmlspecialchars($token->getValueA()));
                $arg->setAttribute("type", "label");
                $instruction->appendChild($arg);

                //xml generation arg2
                $token = $line[2];
                if($token->getType() == TOKEN_VAR) //either variable or constant, has to be distingushed
                {
                    $arg = $document->createElement("arg2", htmlspecialchars($token->getValueA()));
                    $arg->setAttribute("type", "var");
                }
                else
                {
                    $arg = $document->createElement("arg2", htmlspecialchars($token->getValueB()));
                    $arg->setAttribute("type", $token->getValueA());
                }
                $instruction->appendChild($arg);

                //xml generation arg3
                $token = $line[3];
                if($token->getType() == TOKEN_VAR) //either variable or constant, has to be distingushed
                {
                    $arg = $document->createElement("arg3", htmlspecialchars($token->getValueA()));
                    $arg->setAttribute("type", "var");
                }
                else
                {
                    $arg = $document->createElement("arg3", htmlspecialchars($token->getValueB()));
                    $arg->setAttribute("type", $token->getValueA());
                }
                $instruction->appendChild($arg);
                break;


                case $this->instrVal["CREATEFRAME"]: case $this->instrVal["PUSHFRAME"]: case $this->instrVal["POPFRAME"]: case $this->instrVal["BREAK"]: case $this->instrVal["RETURN"]:
                //NO ARG!!!
                $this->checkArgs->number($line, 0);
                break;
            }
            $program->appendChild($instruction);
        }   
        //all the instruction should be loaded in the program var
        $document->appendChild($program);
        $document->save("php://stdout"); //printing xml to stdout
        return SUCCESS;
    }
 }
?>