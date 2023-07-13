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

ini_set('display_errors', 'stderr');

require_once('classes/ErrHandler.php');
require_once('classes/StatHandler.php');
require_once('classes/Parser.php');


//setting streams 
$stdin = fopen('php://stdin', 'r');
$stdout = fopen('php://stdout', 'w');
$stderr = fopen('php://stderr', 'w');


//setting possible arguments that can be encoutered while using this programe
$long_options = ['help', 'stats:', 'loc', 'comments', 'labels', 'jumps', 'fwjumps', 'backjumps', 'badjumps', 'frequent', 'print:', 'eol'];
$short_options = "";



//getting arguments from the stdin
$arguments = getopt($short_options, $long_options);
//instantieting new error handler       
$errHandler = ErrHandler::instantiate($stderr);
//creating new and the only one instance of statHandler. It will be filled with flags in case there will be some
$statHandler = StatHandler::instantiate();

// printing --help message
function printHelp()
{
    $helpMessage = <<<HELP
        Analyzer for IPPcode23
        ----------------------
        Available flags: 
        --help: writes help instructions for the program
        Atention!!! - the --help flag can only be used alone!
        Collecting processed source code statistics in IPPcode23.
        The script will support the parameter --stats=file to specify a file where the statistics in the parameters will be written
        placed after this --stats. Statistics are written to the file line by line according to the order in the parameters with the possibility of repeating them; do not write anything other than what is required on each line
        numeric output and line wrapping; possibly an existing file is overwritten. An additional occurrence of the --stats parameter with a different file name is used to collect groups of statistics into different files
        and followed by other parameters of the new stat group. The --loc parameter prints to statistics
        number of lines with instructions (empty lines or lines containing only a comment or an introductory line are not counted). The --comments parameter lists the number of lines in the statistics on which
        there was a comment. The --labels parameter lists the number of defined labels in the statistics (i.e.
        unique possible jump targets). The --jumps parameter lists the number of all instructions in the statistics
        returns from calls and jump instructions (collectively conditional/unconditional jumps and calls),
        --fwjumps the number of forward jumps
        --backjumps the number of backward jumps
        --badjumps the number of jumps on a non-existent sign.
         If only the --stats parameter is given without specifying the statistics to be listed,
        the output will be an empty file.
         The parameter --frequent writes the names of operatives to the statistics
        of codes (in capital letters, separated by a comma, without spaces) that are most common in the source code
        according to the number of static occurrences. The parameter --print=string prints the string string to the statistics
        and the --eol parameter prints line breaks to the statistics.
        Instruction numbers: ( "MOVE" => 1 , "CREATEFRAME" => 2, "PUSHFRAME" => 3, "POPFRAME" => 4, "DEFVAR" => 5, "CALL" => 6, "RETURN" => 7, "PUSHS" => 8, "POPS" => 9, "ADD" => 10, "SUB" => 11, "MUL" => 12, "IDIV" => 13, "LT" => 14, "GT" => 15, "EQ" => 16, "AND" => 17, "OR" => 18, "NOT" => 19, "INT2CHAR" => 20, "STRI2INT" => 21, "READ" => 22, "WRITE" => 23, "CONCAT" => 24, "STRLEN" => 25, "GETCHAR" => 26, "SETCHAR" => 27, "TYPE" => 28, "LABEL" => 29, "JUMP" => 30, "JUMPIFEQ" => 31, "JUMPIFNEQ" => 32, "EXIT" => 33, "DPRINT" => 34, "BREAK" => 35);\n
        HELP;
        echo $helpMessage;
        exit(SUCCESS);
}

/**
 * @method checArgument() used to check the correct use of flags 
 */
function checkArgumets()
{
    global $arguments;
    global $argv;
    global $errHandler;
    global $statHandler;
    $index = 0;
    #$printIndex = 0; 
    $stats = array(); // array that will contain all the args
    $filename = null;
  
    foreach ($argv as $order => $arg) 
    {
        if($order === 0)
        {
            continue;
        }

        $fileSpecifier = '/^--stats=.*$/';
        $printSpecifier = '/^--print=.*$/';
        if(preg_match($fileSpecifier, $arg) && isset($arguments['stats'])) 
        {

            if(is_array($arguments['stats']))
            {
                $filename = $arguments['stats'][$index];
            }
            else 
            {
                $filename = $arguments['stats'];
            }
            
            //There cannot be two groups of statistics in one file
            if(array_key_exists($filename, $stats)) 
            { 
                $errHandler->printErrorNExit(FILE_ERROR, "Error, use different file for differents groups of stats!!!");
            }
            else 
            {
                $stats[$filename] = array();
                $index++;
            }
        }
        else 
        {

            //File must be set before adding statistics flags
            if($filename === null) 
            {
                $errHandler->printErrorNExit(INVALID_ARG_COMBINATION_ERROR, "Incorrect flag use, the stats file must be specified first, check out --help!\n");
            }
            else 
            {
                if(preg_match($printSpecifier, $arg) && isset($arguments['print'])) 
                {
                    $arg = preg_replace('/^(--)/', '', $arg, limit : 1);
                    $arg = preg_replace('/=\w+/', '', $arg, limit : 1); //cleaning print from flags
                    $text = $arguments['print']; // if someone calls twice print in different groups it will appear in both groups fix!!!!
                    $stats[$filename][$arg] = $text;
                }
                else
                {
                    $arg = preg_replace('/^(--)/', '', $arg, limit : 1);
                    if(isset($arguments[$arg])) 
                    {
                        $stats[$filename][$arg] = 0;
                    
                    }
                }
            }
        }
    }
    $statHandler->set($stats);
}


/**
 * Cycle for cheching wheather all the arguments where inserted correctly, comparing arrays @var arguments and @var argv
 */
foreach ($argv as $key => $arg) 
{
    if($key === 0) 
    {
        continue;
    }
   
    $prefixLess = preg_replace('/^(--)/', '', $arg, limit : 1);
    $paramLess = preg_replace('/(=[^"\']+)$/', ':', $prefixLess, limit : 1);

    if(!in_array($paramLess, $long_options)) 
    {
        $errHandler->printErrorNExit(INVALID_ARG_COMBINATION_ERROR, "Incorrect flag use, check out --help!\n");
    }
}
if(isset($arguments['help']))
{
    if(count($argv) > 2) //--help flag can only be used alone (argv contains at [0] = the program itself)
    {
        $errHandler->printErrorNExit(INVALID_ARG_COMBINATION_ERROR, "--help flag can only be used alone!\n");
    }
    else
    {
        printHelp();
    }
}

checkArgumets();

$parser = Parser::instantiate($errHandler, $statHandler, $stdin, $stdout);
$retVal = $parser->parse();

exit($retVal);

?>