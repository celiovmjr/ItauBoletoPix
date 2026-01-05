<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'storage',
        'bootstrap/cache',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,

        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,

        'declare_strict_types' => true,

        'binary_operator_spaces' => true,
        'unary_operator_spaces' => true,
        'ternary_operator_spaces' => true,
        'not_operator_with_successor_space' => true,

        'standardize_increment' => true,
        'logical_operators' => true,

        'single_quote' => true,
        'trailing_comma_in_multiline' => true,

        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],

        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ],

        'use_arrow_functions' => true,
        'static_lambda' => true,

        'phpdoc_scalar' => true,
        'phpdoc_trim' => true,
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_no_empty_return' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_empty_phpdoc' => true,

        'single_trait_insert_per_statement' => true,
        'void_return' => true,
    ])
    ->setFinder($finder);
