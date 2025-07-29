<?php

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PSR2' => true,
        '@PSR12' => true,
        '@PSR1' => true,
        '@Symfony' => true,
        'array_syntax' => true,
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'multiline_whitespace_before_semicolons' => ['strategy'=>'no_multi_line'],
        'no_alias_functions' => true,
        'no_superfluous_phpdoc_tags' => false,
        'not_operator_with_successor_space' => true,
        'ordered_imports' => ['sort_algorithm'=>'length'],
        'phpdoc_no_empty_return' => false,
        'self_accessor' => true,
        'simplified_null_return' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('bootstrap')
        ->exclude('resources')
        ->exclude('vendor')
        ->exclude('storage')
        ->notPath('config/routes.php')
        ->notName('*.blade.php')
        ->in(__DIR__)
    );
