<?php

namespace Luhmm1\ViaRouter\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    private string $path;

    /**
     * @var string[]
     */
    private array $methods = ['GET'];

    /**
     * @param string[] $methods
     */
    public function __construct(string $path, array $methods = ['GET'])
    {
        $this->path = $path;
        $this->methods = $methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
