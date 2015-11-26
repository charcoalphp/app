<?php

namespace Charcoal\App\Script;

use \InvalidArgumentException;

use \League\CLImate\CLImate;

// Local namespace dependencies
use \Charcoal\App\Script\ScriptInterface;

/**
 *
 */
abstract class AbstractScript implements ScriptInterface
{
    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var array $arguments
     */
    private $arguments;


    /**
     * @var CLImate $climate
     */
    private $climate;


    /**
     * @var boolean $verbose
     */
    private $verbose;

    /**
     * @return CLImate
     */
    public function climate()
    {
        if ($this->climate === null) {
            $this->climate = new CLImate();
        }
        return $this->climate;
    }

    /**
     * @return array
     */
    public function default_arguments()
    {
        return [
            'help' => [
                'longPrefix'   => 'help',
                'description'  => 'Prints a usage statement',
                'noValue'      => true
            ],
            'quiet' => [
                'prefix'       => 'q',
                'longPrefix'   => 'quiet',
                'description'  => 'Disable Output additional information on operations',
                'noValue'      => false
            ]
        ];
    }

    /**
     * @param string $ident The script identifier string.
     * @throws InvalidArgumentException If the ident argument is not a string.
     * @return ScriptInterface Chainable
     */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Ident must be a string'
            );
        }
        $this->ident = $ident;
        return $this;
    }
    
    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param string $description The script description.
     * @throws InvalidArgumentException If the deescription parameter is not a string.
     * @return ScriptInterface Chainable
     */
    public function set_description($description)
    {
        if (!is_string($description)) {
            throw new InvalidArgumentException(
                'Description must be a string'
            );
        }
        $this->description = $description;
        $this->climate()->description($description);
        return $this;
    }

    /**
     * @return string
     */
    public function description()
    {
        return $this->description;
    }
    
    /**
     * @param array $arguments The scripts argument array, as [key=>value].
     * @return ScriptInterface Chainable
     */
    public function set_arguments(array $arguments)
    {
        $this->arguments = [];
        foreach ($arguments as $argument_ident => $argument) {
            $this->add_argument($argument_ident, $argument);
        }

        return $this;
    }

    /**
     * @param string $argument_ident The argument identifier.
     * @param array  $argument       The argument options.
     * @throws InvalidArgumentException If the argument ident is not a string.
     * @return ScriptInterface Chainable
     */
    public function add_argument($argument_ident, array $argument)
    {
        if (!is_string($argument_ident)) {
            throw new InvalidArgumentException(
                'Argument ident must be a string.'
            );
        }
        $this->arguments[$argument_ident] = $argument;
        $this->climate()->arguments->add([$argument_ident=>$argument]);
        return $this;
    }

    /**
     * @return array $arguments
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * @param string $argument_ident The argument identifier to retrieve options from.
     * @return array|null The argument options, or null if it does not exist.
     */
    public function argument($argument_ident)
    {
        if (!isset($this->arguments[$argument_ident])) {
            return null;
        }
        return $this->arguments[$argument_ident];
    }

    /**
     * Get an argument either from argument list (if set) or else from an input prompt.
     *
     * @param string $arg_name The argument identifier to read from list or input.
     * @return string The argument value or prompt value
     */
    public function arg_or_input($arg_name)
    {
        $climate = $this->climate();
        $arg = $climate->arguments->get($arg_name);

        if ($arg) {
            return $arg;
        } else {
            $arguments = $this->arguments();
            if (isset($arguments[$arg_name])) {
                $a = $arguments[$arg_name];
                $arg_desc = $a['description'];
                $input_type = isset($a['inputType']) ? $a['inputType'] : 'text';
                $choices = isset($a['choices']) ? $a['choices'] : null;
            
            } else {
                $arg_desc = $arg_name;
                $input_type = 'text';
                $choices = null;
            }
            if ($input_type == 'checkbox') {
                $input = $climate->checkboxes(sprintf('Select %s', $arg_desc), $choices);
            } else {
                $input = $climate->input(sprintf('Enter %s:', $arg_desc));
                if ($choices) {
                    $input->accept(array_keys($choices), true);
                }
            }
            $arg = $input->prompt();
            return $arg;
        }
    }

    /**
     * @param boolean $verbose The verbose flag.
     * @throws InvalidArgumentException If the parameter is not a boolean.
     * @return ScriptInterface Chainable
     */
    public function set_verbose($verbose)
    {
        if (!is_bool($verbose)) {
            throw new InvalidArgumentException(
                'Verbose flag must be a boolean'
            );
        }
        $this->verbose = $verbose;
        return $this;
    }

    /**
     * @return boolean
     */
    public function verbose()
    {
        return $this->verbose;
    }

    /**
     * @return string
     */
    public function help()
    {
        return $this->climate()->usage();
    }
}