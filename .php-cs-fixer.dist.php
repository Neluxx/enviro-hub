<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
    ->notName('*.blade.php')
    ->exclude('var')
    ->exclude('vendor')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PSR12' => true,

        // Optional: clean code / modern PHP
        'declare_strict_types' => true,
        'combine_consecutive_unsets' => true,
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'phpdoc_to_comment' => true,
        'php_unit_method_casing' => ['case' => 'camel_case'],
        'php_unit_test_class_requires_covers' => false,

        // Imports
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'global_namespace_import' => ['import_classes' => true],
        'single_import_per_statement' => true,

        // Functions & Methods
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'nullable_type_declaration_for_default_null_value' => true,
        'no_unreachable_default_argument_value' => true,

        // Classes & Traits
        'ordered_class_elements' => true,
        'self_accessor' => true,
        'no_null_property_initialization' => true,

        // Arrays
        'array_syntax' => ['syntax' => 'short'],
        'trim_array_spaces' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],

        // Spacing & Formatting
        'binary_operator_spaces' => ['default' => 'single_space'],
        'blank_line_before_statement' => ['statements' => ['return', 'throw', 'try', 'foreach', 'for', 'if']],
        'no_extra_blank_lines' => ['tokens' => ['extra']],
        'yoda_style' => false,

        // PHPDoc
        'phpdoc_order' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_summary' => true,
        'phpdoc_line_span' => ['const' => 'single', 'property' => 'single', 'method' => 'multi'],
    ])
;
