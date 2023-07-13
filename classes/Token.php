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

/**\
 * Class token to represent token for lex analysis 
*/

 Class Token
 {
    //token type
    private $type;

    //value of token
    private $valueA;
    //because of consts  and var contain two parts
    private $valueB;

    //setting values in constructor
    public function __construct($type, $valueA, $valueB)
    {
        $this->type = $type;
        $this->valueA = $valueA;
        $this->valueB = $valueB;
    }
    //getter
    public function getType()
    {
        return $this->type;
    }
    //getter
    public function getValueA()
    {
        return $this->valueA;
    }

    public function getValueB()
    {
        return $this->valueB;
    }
 }
?>