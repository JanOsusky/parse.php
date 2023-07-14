# IPP Compiler - parse.php

## Description

The IPP Compiler parse.php is a program designed to convert IPPcode23 instructions into their XML representation. It parses the input code, checks the syntax and semantics, and generates an XML output based on the parsed instructions.

The program follows an object-oriented design, with several classes working together to accomplish the parsing and XML generation. The main file, parse.php, handles the command-line arguments, instantiates the necessary classes, and executes the parsing process.

## Installation

1. Clone the repository.
2. Ensure you have PHP installed on your system.
3. Run the program using the appropriate PHP command.

```bash
php parse.php [arguments]
```
## Usage

The program accepts several command-line arguments, which determine its behavior. The available arguments include:

- `--help`: Display the help message.
- `--stats=file`: Specify the output file for statistics.
- `--loc`: Count the number of instructions (LOC - Lines of Code).
- `--comments`: Count the number of comments in the code.
- `--labels`: Count the number of defined labels.
- `--jumps`: Count the number of jump instructions.
- `--fwjumps`: Count the number of forward jumps.
- `--backjumps`: Count the number of backward jumps.
- `--badjumps`: Count the number of invalid jumps.
- `--frequent`: Display the most frequently used instructions.
- `--print=file`: Specify the output file for XML representation.
- `--eol`: Print end-of-line characters at the end of each instruction.

Examples:

```bash
php parse.php --stats=statistics.txt --loc --comments --labels input.ippcode23
php parse.php --stats=statistics.txt --frequent --print=output.xml input.ippcode23

