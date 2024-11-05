<?php


namespace PHPSQLParser\builders;
use PHPSQLParser\utils\ExpressionType;

/**
 * This class implements the builder for the index comment of CREATE INDEX statement. 
 * You can overwrite all functions to achieve another handling.
 *
 * @author  André Rothe <andre.rothe@phosco.info>
 * @license http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 *  
 */
class CommentBuilder implements Builder {

   

    protected function buildConstant($parsed) {
        $builder = new ConstantBuilder();
        return $builder->build($parsed);
    }

    public function build(array $parsed) {
        if ($parsed['expr_type'] !== ExpressionType::COMMENT) {
            return isset($parsed['value'])?$parsed['value']:'';
        }
        $sql = ' ';
        return $sql;
    }
}
?>
