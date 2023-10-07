<?php declare(strict_types=1);

namespace PetrKnap\SpaydQr\Test;

use PHPUnit\Framework\TestCase;

class ExamplesTest extends TestCase
{
    /** @dataProvider dataReadmeExampleWorks */
    public function testReadmeExampleWorks(string $code, string $pathToExpectedOutput)
    {
        ob_start();
        eval($code);
        $output = ob_get_clean();

        $this->assertEquals(
            file_get_contents($pathToExpectedOutput),
            $output . PHP_EOL
        );
    }

    public function dataReadmeExampleWorks()
    {
        $readme = file_get_contents(__DIR__ . '/../README.md');
        $examples = explode('```', $readme);
        for ($i = 1; $i < count($examples); $i += 2) {
            list($language, $example) = explode(PHP_EOL, $examples[$i], 2);
            if ($language === 'php') {
                yield [$example, __DIR__ . "/ExamplesTest/readme_{$i}.html"];
            }
        }
    }
}
