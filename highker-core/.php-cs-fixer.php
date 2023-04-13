<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

$header = <<<'EOF'
     User: dirty
     Email: shijie1991@gmail.com
    EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('tests/Fixtures')
    ->in(__DIR__)
    ->append([
        __DIR__.'/dev-tools/doc.php',
        __DIR__.'/php-cs-fixer',
    ])
;

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer'               => true,
        '@PSR2'                     => true,
        '@PHP74Migration'           => true,
        'yoda_style'                => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'header_comment'            => ['header' => $header],
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'list_syntax'               => ['syntax' => 'short'],
        'binary_operator_spaces'    => [
            'operators' => ['=>' => 'align_single_space_minimal'],
        ],
        'concat_space'               => ['spacing' => 'none'],
        'ordered_class_elements'     => ['order' => ['use_trait']],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => false, 'remove_inheritdoc' => false, 'allow_unused_params' => false],
    ])
    ->setFinder($finder)
;

// special handling of fabbot.io service if it's using too old PHP CS Fixer version
if (getenv('FABBOT_IO') !== false) {
    try {
        PhpCsFixer\FixerFactory::create()
            ->registerBuiltInFixers()
            ->registerCustomFixers($config->getCustomFixers())
            ->useRuleSet(new PhpCsFixer\RuleSet($config->getRules()))
        ;
    } catch (PhpCsFixer\ConfigurationException\InvalidConfigurationException $e) {
        $config->setRules([]);
    } catch (UnexpectedValueException $e) {
        $config->setRules([]);
    } catch (InvalidArgumentException $e) {
        $config->setRules([]);
    }
}

return $config;
