<?php

return [
    'php_var' => 'private __REPLACE_VAR_TYPE__ $__REPLACE_VAR_NAME__;',
    'getter' => 'public function get__REPLACE_FUNCTION_NAME__(): __REPLACE_VAR_TYPE__
    {
        return $this->__REPLACE_VAR_NAME__;
    }
    ',

    'setter' => 'public function set__REPLACE_FUNCTION_NAME__(__REPLACE_VAR_TYPE__ $__REPLACE_VAR_NAME__): self
    {
         $this->__REPLACE_VAR_NAME__ = $__REPLACE_VAR_NAME__;
         return $this;
    }
    ',

    2 => [
        'type' => 'string',
        'annotations' => [
            'doctrine' => '/**  */'
        ]
    ]
];