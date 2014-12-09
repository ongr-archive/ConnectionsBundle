<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Creates directories needed for the import.
 */
class ImportDataDirectory
{
    /**
     * @var string
     */
    protected $appDir;

    /**
     * @var string
     */
    protected $dataDir;

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @param string $appDir
     * @param string $dataDir
     */
    public function __construct($appDir, $dataDir)
    {
        $this->appDir = $appDir;
        $this->dataDir = $dataDir;
    }

    /**
     * @return string
     */
    public function getDataDirPath()
    {
        return $this->appDir . DIRECTORY_SEPARATOR . $this->dataDir;
    }

    /**
     * Creates directory if needed.
     */
    public function create()
    {
        $files = new Filesystem();
        $files->mkdir($this->getDataDirPath());
    }

    /**
     * Creates directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function createDir($path)
    {
        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($path)) {
            $path = $this->getDataDirPath() . DIRECTORY_SEPARATOR . $path;
        }
        $fs->mkdir($path);

        return $path;
    }

    /**
     * @param array $provider
     * @param bool  $unique
     *
     * @return string
     */
    public function getCurrentDir($provider, $unique = false)
    {
        $datePath = date('Y/m/d/');
        if (!empty($unique)) {
            $unique = DIRECTORY_SEPARATOR . date('H_i_s') . '_' . md5(microtime()) . '_' . uniqid();
        } else {
            $unique = '';
        }

        $path = $datePath . $provider . $unique;

        $this->createDir($path);

        return $path;
    }

    /**
     * Cleans up unnecessary files.
     *
     * @param array  $providers
     * @param string $date
     */
    public function cleanUp($providers = null, $date = null)
    {
        $system = new Filesystem();

        $date = $this->dateHelper->convertDate($date);
        /** @var \DateTime $date */

        $blacklist = [];

        $path = $this->getDataDirPath();

        $finder = new Finder();
        $finder->directories()->in($path);
        /** @var $directory SplFileInfo */
        foreach ($finder as $directory) {
            $relPath = ltrim($directory->getPathname(), $path);
            $parts = explode('/', $relPath);
            if (count($parts) == 4) {
                $parts = array_combine(['year', 'month', 'day', 'provider'], $parts);
                $fsTimestamp = strtotime(sprintf('%s-%s-%s', $parts['year'], $parts['month'], $parts['day']));
                if ($fsTimestamp <= $date->getTimestamp()) {
                    if (!empty($providers)) {
                        if (in_array($parts['provider'], $providers)) {
                            $blacklist[] = $relPath;
                        }
                    } else {
                        $blacklist[] = $relPath;
                    }
                }
            }
        }

        $finder->files()->in($path);
        foreach ($finder as $file) {
            $relPath = ltrim($file->getPathname(), $path);
            $parts = explode('/', $relPath);
            if (count($parts) >= 4) {
                $parts = array_splice($parts, 0, 4);
                $interestingPart = implode('/', $parts);
                if (in_array($interestingPart, $blacklist)) {
                    $system->remove($file);
                }
            }
        }
    }

    /**
     * @param DateHelper $dateHelper
     */
    public function setDateHelper($dateHelper)
    {
        $this->dateHelper = $dateHelper;
    }

    /**
     * Returns full path to file.
     *
     * @param string $file
     *
     * @return string
     */
    public function locateFile($file)
    {
        $fs = new Filesystem();

        if ($fs->isAbsolutePath($file)) {
            return $file;
        }

        $attempt = dirname($this->appDir) . DIRECTORY_SEPARATOR . $file;
        if ($fs->exists($attempt)) {
            return $attempt;
        }

        return $this->getDataDirPath() . DIRECTORY_SEPARATOR . $file;
    }
}
