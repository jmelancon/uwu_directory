<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Config;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ConfigurationProvider
{
    private Config $config;
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly string $generalConfigPath,
        private readonly string $styleConfigPath,
        private readonly string $faviconPath,
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

        $stylesheet = file_get_contents($this->styleConfigPath);
        if (!is_string($stylesheet))
            throw new RuntimeException("Could not read stylesheet file ($this->styleConfigPath)");

        if (file_exists($this->faviconPath)){
            $favicon = file_get_contents($this->faviconPath);
            if (!is_string($favicon))
                throw new RuntimeException("Could not read favicon file ($this->faviconPath)");
        } else {
            $favicon = '';
        }

        $config->setCustomScss($stylesheet);
        $config->setFavicon($favicon);

        return $config;
    }

    private function persist(Config $config): bool{
        $successfulGeneralWrite = file_put_contents(
            filename: $this->generalConfigPath,
            data: $this->serializer->serialize(
                data: $config,
                format: 'json',
                context: [
                    AbstractNormalizer::GROUPS => ['generalConfig']
                ]
            )
        );

        if (!$successfulGeneralWrite)
            return false;

        $successfulStyleWrite = file_put_contents(
            filename: $this->styleConfigPath,
            data: $config->getCustomScss(),
        );

        if(!$successfulStyleWrite)
            return false;

        if (!empty($config->getFavicon())){
            $successfulFaviconWrite = file_put_contents(
                filename: $this->faviconPath,
                data: $config->getFavicon()
            );
            if(!$successfulFaviconWrite)
                return false;
        } else {
            exec("/usr/bin/rm -f $this->faviconPath");
        }


        $this->config = $config;
        return true;
    }

    private function has(): bool{
        return file_exists($this->generalConfigPath) && file_exists($this->styleConfigPath);
    }

    public function getConfig(): Config{
        return $this->config;
    }

    public function setConfig(Config $config): void{
        $this->persist($config);
    }
}