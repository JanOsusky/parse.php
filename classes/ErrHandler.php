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

define('SUCCESS', 0);
define('INVALID_ARG_COMBINATION_ERROR', 10);
define('OPENING_FILE_ERROR', 11);
define('FILE_ERROR', 12);
define('HEADER_ERROR', 21);
define('OPCODE_ERROR', 22);
define('LEX_SYNTAX_ERROR', 23);
define('INTERN_ERROR', 99);
//define('SEMANTIC_ERROR', 52);
//define('WRONG_OP_TYPE_ERROR', 53);
//define('NON_EXISTING_VAR_ERROR', 54);  
//define('MISSING_FRAME_ERROR', 55);
//define('MISSING_VALUE_ERROR',56); 
//define('WRONG_OP_VALUE_ERROR', 57);
//define('WRONG_STRING_ERROR', 58);


/**
 * Class defined with singleton pattern, used to handle errors in the program
 */
class ErrHandler 
{
    /**
     * var instance for saving the ony instance of the class
     */
    private static $instance = null;

    //Stores stderr path
    private $errorOutput;

    private function __construct($errorOutput)
    {
        $this->errorOutput = $errorOutput;
    }
    
    /**
     * Implementation of the signleton model
     * Only one Instance can be created
     */
    public static function instantiate($errorOutput) {
        if(ErrHandler::$instance === null) 
        {
            ErrHandler::$instance = new ErrHandler($errorOutput);
        }

        return ErrHandler::$instance;
    }

    /**
     * @method mixed printErrorNExit() used to handle to print the error message and exit the program
     */
    public function printErrorNExit($type, $errorMessage)
    {
        fwrite($this->errorOutput, "{$type} - {$errorMessage}");
        fclose($this->errorOutput);
        exit($type);
    }

    /**
     * method mixed printErrorNRet() used to return Error value to upper structures of program
     */
   // public function printErrorNRet($type, $errorMessage)
   // {
   //     fwrite($this->errorOutput, "{$type} - {$errorMessage}");
   //     return $type;
   // }
}
?>
