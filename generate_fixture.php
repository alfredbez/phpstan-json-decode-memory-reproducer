<?php

declare(strict_types=1);

$directory = __DIR__ . '/generated';
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

mt_srand(1);
$rows = [];
for ($i = 0; $i < 2609; $i++) {
    $suffix = '';
    for ($j = 0; $j < 20; $j++) {
        $suffix .= 'abcdefghij'[mt_rand(0, 9)];
    }
    $rows[] = [$i, 'de', "Title $i $suffix"];
}
$json = json_encode($rows, JSON_THROW_ON_ERROR);

$template = <<<'PHP'
<?php

final class CLASS_NAME
{
    public function run(): mixed
    {
        return json_decode(ARGUMENT, true, 512, JSON_THROW_ON_ERROR);
    }

    private static function opaque(string $value): string
    {
        return $value;
    }

    private const ROWS = <<<'JSON'
JSON_CONTENT
JSON;
}
PHP;

foreach (['ConstantInput' => 'self::ROWS', 'OpaqueInput' => 'self::opaque(self::ROWS)'] as $class => $argument) {
    $source = str_replace(['CLASS_NAME', 'ARGUMENT', 'JSON_CONTENT'], [$class, $argument, $json], $template);
    file_put_contents("$directory/$class.php", $source . "\n");
}

echo "Generated two variants with 2609 identical JSON rows.\n";
