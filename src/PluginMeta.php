<?php
namespace Trs;


class PluginMeta
{
    public function __construct($entryFile)
    {
        $this->entryFile = self::normalizePath($entryFile);
        $this->dir = dirname($this->entryFile);
    }

    public function getEntryFile()
    {
        return $this->entryFile;
    }

    public function getPath($relativePath = null)
    {
        return $this->makePath(null, $relativePath);
    }

    public function getLibsPath($relativePath = null)
    {
        return $this->makePath('vendor', $relativePath);
    }

    public function getSrcPath($relativePath = null)
    {
        return $this->makePath('src', $relativePath);
    }

    public function getMigrationsPath($relativePath = null)
    {
        return $this->makePath('migrations', $relativePath);
    }
    
    public function getAssetsPath($relativePath = null)
    {
        return $this->makePath('assets', $relativePath);
    }

    public function getAssetUrl($asset = null)
    {
        return plugins_url("/assets/{$asset}", $this->getEntryFile());
    }

    public function getApiUpdatesEndpoint()
    {
        return $this->getApiEndpoint('updates');
    }

    public function getApiStatsEndpoint()
    {
        return $this->getApiEndpoint('stats');
    }

    public function getLicense()
    {
        if ($this->license === false) {
            if (file_exists($file = $this->getPath('license.key'))) {
                $this->license = file_get_contents($file) ?: null;
            } else {
                $this->license = null;
            }
        }

        return $this->license;
    }

    public function getVersion()
    {
        if ($this->version === false) {
            $pluginFileAttributes = get_file_data($this->entryFile, array('Version' => 'Version'));
            $this->version = $pluginFileAttributes['Version'] ?: null;
        }

        return $this->version;
    }

    public function getPluginBasename()
    {
        return plugin_basename($this->getEntryFile());
    }

    private $entryFile;
    private $dir;
    private $license = false;
    private $version = false;
    private $apiEndpoint = false;

    private function makePath($location = null, $path = null)
    {
        if (!isset($location) && !isset($path)) {
            return $this->dir;
        }

        $parts = array();

        $parts[] = $this->dir;

        if (isset($location)) {
            $parts[] = $location;
        }

        if (isset($path)) {
            $parts[] = $path;
        }

        return join('/', $parts);
    }

    private function getApiEndpoint($service)
    {
        if ($this->apiEndpoint === false) {

            if (file_exists($config = $this->getPath('.config.php'))) {

                /** @noinspection PhpIncludeInspection */
                $config = require($config);

                if (isset($config['apiEndpoint'])) {
                    $this->apiEndpoint = $config['apiEndpoint'];
                }
            }

            if ($this->apiEndpoint === false) {
                $this->apiEndpoint = 'https://tablerateshipping.com/api';
            }
        }

        return "{$this->apiEndpoint}/{$service}";
    }

    static private function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);

        $path = preg_replace('|/+|', '/', $path);

        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }

        return $path;
    }
}