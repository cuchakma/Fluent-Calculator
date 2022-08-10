<?php


class FluentCalculator{
    
    public $values                 = array('zero' => 0, 'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'zero' => 0);
    public $operation              = array('plus'=> '+', 'minus' => '-', 'times' => '*', 'dividedBy' => '/');
    public $value_stack            = array();
    public $expression_format      = array();
    public $expression_stack       = array();
    public $expression_string;
    public $number_string;

    
    public static function init() {
        return new FluentCalculator();
    }
    
    public function __get($name)
    {
        if( isset( $this->values[$name] ) && is_int( $this->values[$name] ) ) {
            array_push($this->value_stack, $this->values[$name]);
        } elseif( isset( $this->operation[$name] ) && is_string( $this->operation[$name] )) {
            array_push( $this->value_stack, $this->operation[$name] );
        }

        return $this;
    }

    public function __call($name, $arguments)
    {
        if( isset( $this->values[$name] ) && is_int( $this->values[$name] ) ) {
            array_push($this->value_stack, $this->values[$name]);
        } elseif( isset( $this->operation[$name] ) && is_string( $this->operation[$name] ) ) {
            array_push( $this->value_stack, $this->operation[$name] );
        }

        // Group Numbers & Get Last Sign If Multiple Signs Exists Between Two Numbers Gap
        for( $i = 0; $i < count( $this->value_stack ); $i++ ) {
            if( is_int( $this->value_stack[$i] ) ) {
                $this->number_string = '';
                while( isset( $this->value_stack[$i] ) ) {
                    if( is_int( $this->value_stack[$i] ) ) {
                        $this->number_string .= $this->value_stack[$i];
                    } else {
                        array_push($this->expression_stack, (int) $this->number_string);
                        --$i;
                        break;
                    }
                    $i++;
                }
                if( $i >= count( $this->value_stack ) ) {
                    array_push($this->expression_stack, (int) $this->number_string);
                }
            } else {
                $this->expression_string = '';
                while( isset( $this->value_stack[$i] ) ) {
                    if( is_string( $this->value_stack[$i] ) ) {
                        $this->expression_string = $this->value_stack[$i];
                    } else {
                        array_push($this->expression_stack, (string) $this->expression_string);
                        --$i;
                        break;
                    }
                    $i++;
                }
                if( $i >= count( $this->value_stack ) ) {
                    array_push($this->expression_stack, (string) $this->expression_string);
                }
            }
        }

        /**
         * If the expression stack contains sign(string) at the last element remove it
         */
        if( is_string( $this->expression_stack[count($this->expression_stack) - 1] ) ){
            unset($this->expression_stack[count($this->expression_stack) - 1]);
        }

        /**
         * Evaluate The Following Expression
         */
        $evaluated_data = '';
    
        while( ! empty( $this->expression_stack ) ) {
            $popped_value = array_shift( $this->expression_stack );
            if( is_int( $popped_value ) ) {
                $evaluated_data = $popped_value;
            } else if( $popped_value === '+' ) {
                $evaluated_data += array_shift( $this->expression_stack );
            } elseif( $popped_value === '-' ) {
                $evaluated_data -= array_shift( $this->expression_stack );
            } elseif( $popped_value === '*' ) {
                $evaluated_data *= array_shift( $this->expression_stack );
            } else {
                $evaluated_data /= array_shift( $this->expression_stack );
                if( is_float( $evaluated_data ) ) {
                    $evaluated_data = (int) $evaluated_data;
                }
            }
        }

        return $evaluated_data;
        
    }
}

echo FluentCalculator::init()->two->plus->five->zero->two->eight->six->dividedBy->dividedBy->five->two->minus->plus->four->four->one->seven->nine->four->zero->three->eight->three->one->three->nine->four->one->zero->nine->two();
