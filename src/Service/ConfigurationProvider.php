<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Config;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

class ConfigurationProvider
{
    private Config $config;
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly string $generalConfigPath,
    ){
        if (!$this->has()){
            $success = $this->persist(new Config());
            if (!$success){
                throw new RuntimeException('Failed to create configuration files');
            }
        }
        $this->config = $this->load();
    }

    private function load(): Config{
        $contents = file_get_contents($this->generalConfigPath);
        if (!is_string($contents))
            throw new RuntimeException("Could not read general config file ($this->generalConfigPath)");

        $config = $this->serializer->deserialize($contents, Config::class, 'json');
        if (!$config instanceof Config)
            throw new RuntimeException("Could not deserialize general config ($this->generalConfigPath)");

        return $config;
    }

    private function persist(Config $config): bool{
        $successfulGeneralWrite = file_put_contents(
            filename: $this->generalConfigPath,
            data: $this->serializer->serialize(
                data: $config,
                format: 'json',
            )
        );

        if (!$successfulGeneralWrite)
            return false;

        $this->config = $config;
        return true;
    }

    private function has(): bool{
        return file_exists($this->generalConfigPath);
    }

    public function getConfig(): Config{
        return $this->config;
    }

    public function setConfig(Config $config): void{
        $this->persist($config);
    }
}