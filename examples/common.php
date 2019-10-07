<?php
    class Base64Url {
        public static $rules = array (
            "+" => "-", 
            "/" => "_", 
            "=" => "." 
        );
        public static function Encode ( $source_str ) {
            return str_replace( 
                array_keys( self::$rules ), 
                self::$rules, 
                base64_encode( $source_str )
            );
        }
        public static function Decode ( $source_str ) {
            return base64_decode(str_replace( 
                self::$rules, 
                array_keys( self::$rules ), 
                $source_str 
            ));
        }
    }