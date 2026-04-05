<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EnvProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

final class RawUrlEncodeEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @param \Closure(string): mixed $getEnv
     */
    public function getEnv(string $prefix, string $name, \Closure $getEnv): string
    {
        $value = $getEnv($name);

        if (!\is_string($value)) {
            throw new \RuntimeException(sprintf('The env var "%s" must be a string.', $name));
        }

        return rawurlencode($value);
    }

    public static function getProvidedTypes(): array
    {
        return ['rawurlencode' => 'string'];
    }
}
