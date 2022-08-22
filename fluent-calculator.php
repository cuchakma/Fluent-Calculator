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
        } else {
           throw new InvalidInputException();
        }

        return $this;
    }

    public function __call($name, $arguments)
    {
        if( isset( $this->values[$name] ) && is_int( $this->values[$name] ) ) {
            array_push($this->value_stack, $this->values[$name]);
        } elseif( isset( $this->operation[$name] ) && is_string( $this->operation[$name] ) ) {
            array_push( $this->value_stack, $this->operation[$name] );
        }else {
           throw new InvalidInputException();
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
         * Evaluate The Following Expression
         */
        $evaluated_data = '';
    
        while( ! empty( $this->expression_stack ) ) {
            $popped_value = array_shift( $this->expression_stack );
            if( is_int( $popped_value ) ) {
                if( strlen( $popped_value ) > 9 ) {
                    throw new DigitCountOverflowException();
                }
                $evaluated_data = $popped_value;
            } else if( $popped_value === '+' ) {
               if( $evaluated_data == '' && is_string( $popped_value ) ) {
                    $evaluated_data = 0;
                }
              
                $popped_value    = array_shift( $this->expression_stack );
              
                if( $popped_value == '' && ! is_int( $popped_value ) ) {
                    continue;
                }

                if( strlen( $popped_value ) > 9 ) {
                    throw new DigitCountOverflowException();
                }

                $evaluated_data +=  $popped_value;
            } elseif( $popped_value === '-' ) {
               if( $evaluated_data == '' && is_string( $popped_value ) ) {
                    $evaluated_data = 0;
                }
              
                $popped_value    = array_shift( $this->expression_stack );
              
                if( $popped_value == '' && ! is_int( $popped_value ) ) {
                    continue;
                }

                if( strlen( $popped_value ) > 9 ) {
                    throw new DigitCountOverflowException();
                }

                $evaluated_data -= $popped_value;
            } elseif( $popped_value === '*' ) {
               if( $evaluated_data == '' && is_string( $popped_value ) ) {
                    $evaluated_data = 0;
                }
              
                $popped_value    = array_shift( $this->expression_stack );
              
                if( $popped_value == '' && ! is_int( $popped_value ) ) {
                    continue;
                }

                if( strlen( $popped_value ) > 9 ) {
                    throw new DigitCountOverflowException();
                }

                $evaluated_data *= $popped_value;
            } else {
               if( $evaluated_data == '' && is_string( $popped_value ) ) {
                    $evaluated_data = 0;
                } 
              
                $popped_value = array_shift( $this->expression_stack );
              
                if( $popped_value == '' && ! is_int( $popped_value ) ) {
                    continue;
                }

                if( strlen( $popped_value ) > 9 ) {
                    throw new DigitCountOverflowException();
                }

                if( $popped_value === 0 ) {
                  throw new DivisionByZeroException();
                }

                $evaluated_data /= $popped_value;
                if( is_float( $evaluated_data ) ) {
                    $evaluated_data = (int) $evaluated_data;
                }
            }
            
            
            if( strlen( $evaluated_data ) > 9 ) {
              $evaluated_data_2 = abs( $evaluated_data );
               if( strlen( $evaluated_data_2 ) > 9 ) {
                    throw new DigitCountOverflowException();
               } else {
                    continue;
               }
            }
        }
        return empty( $evaluated_data ) ? 0 : $evaluated_data;
        
    }
}
FluentCalculator::init();