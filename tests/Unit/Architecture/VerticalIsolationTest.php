<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class VerticalIsolationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 3);
    }

    public function test_application_does_not_import_infrastructure_models(): void
    {
        $violations = $this->findUseViolations(
            $this->root.'/app/Application',
            '/^use\s+App\\\\Infrastructure\\\\.+\\\\Model\\\\/m',
        );

        $this->assertSame([], $violations, "Application must not import Infrastructure models:\n".implode("\n", $violations));
    }

    public function test_application_handlers_do_not_import_foreign_bc_handlers(): void
    {
        $boundedContexts = [
            'CRM', 'Delivery', 'Equipment', 'Feedback', 'Finance',
            'Identity', 'Inventory', 'Order', 'Pricing', 'Workshop',
        ];

        $violations = [];

        foreach ($boundedContexts as $bc) {
            $path = $this->root.'/app/Application/'.$bc;

            if (! is_dir($path)) {
                continue;
            }

            $foreign = array_values(array_filter(
                $boundedContexts,
                static fn (string $other): bool => $other !== $bc,
            ));

            foreach ($this->phpFiles($path) as $file) {
                $contents = (string) file_get_contents($file);

                foreach ($foreign as $otherBc) {
                    if (preg_match('/^use\s+App\\\\Application\\\\'.$otherBc.'\\\\Command\\\\.+Handler;/m', $contents) === 1) {
                        $violations[] = $this->relative($file).' imports '.$otherBc.' Handler';
                    }

                    if (preg_match('/^use\s+App\\\\Domain\\\\'.$otherBc.'\\\\Repository\\\\/m', $contents) === 1) {
                        $violations[] = $this->relative($file).' imports '.$otherBc.' Repository';
                    }

                    if (preg_match('/^use\s+App\\\\Domain\\\\'.$otherBc.'\\\\Entity\\\\/m', $contents) === 1) {
                        $violations[] = $this->relative($file).' imports '.$otherBc.' Entity';
                    }
                }
            }
        }

        $this->assertSame([], $violations, "Cross-BC Application write-stack imports:\n".implode("\n", $violations));
    }

    public function test_listeners_only_delegate_to_application_commands(): void
    {
        $violations = [];

        foreach ($this->phpFiles($this->root.'/app/Infrastructure') as $file) {
            if (! str_contains($file, DIRECTORY_SEPARATOR.'Listener'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $contents = (string) file_get_contents($file);

            if (preg_match('/^use\s+App\\\\Domain\\\\.+\\\\Repository\\\\/m', $contents) === 1) {
                $violations[] = $this->relative($file).' imports Domain Repository';
            }

            if (preg_match('/^use\s+App\\\\Infrastructure\\\\.+\\\\Model\\\\/m', $contents) === 1) {
                $violations[] = $this->relative($file).' imports Infrastructure Model';
            }
        }

        $this->assertSame([], $violations, "Listeners must stay thin:\n".implode("\n", $violations));
    }

    /**
     * @return list<string>
     */
    private function findUseViolations(string $directory, string $pattern): array
    {
        $violations = [];

        foreach ($this->phpFiles($directory) as $file) {
            $contents = (string) file_get_contents($file);

            if (preg_match($pattern, $contents) === 1) {
                $violations[] = $this->relative($file);
            }
        }

        return $violations;
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function relative(string $absolutePath): string
    {
        return ltrim(str_replace($this->root, '', $absolutePath), DIRECTORY_SEPARATOR);
    }
}
