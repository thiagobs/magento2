<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Resource\File\Storage;

/**
 * Class File
 */
class File
{
    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Logger $log
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\Logger $log
    ) {
        $this->_logger = $log;
        $this->_filesystem = $filesystem;
    }

    /**
     * Collect files and directories recursively
     *
     * @param string $dir
     * @return array
     */
    public function getStorageData($dir = '')
    {
        $files          = array();
        $directories    = array();

        $directoryInstance = $this->_filesystem->getDirectoryRead(\Magento\Filesystem::MEDIA);
        if ($directoryInstance->isDirectory($dir)) {
            foreach ($directoryInstance->read($dir) as $path) {
                $itemName = basename($path);
                if ($itemName == '.svn' || $itemName == '.htaccess') {
                    continue;
                }

                if ($directoryInstance->isDirectory($path)) {
                    $directories[] = array(
                        'name' => $itemName,
                        'path' => dirname($path)
                    );
                } else {
                    $files[] = $path;
                }
            }
        }

        return array('files' => $files, 'directories' => $directories);
    }

    /**
     * Clear files and directories in storage
     *
     * @param string $dir
     * @return \Magento\Core\Model\Resource\File\Storage\File
     */
    public function clear($dir = '')
    {
        $directoryInstance = $this->_filesystem->getDirectoryWrite(\Magento\Filesystem::MEDIA);
        if ($directoryInstance->isDirectory($dir)) {
            foreach ($directoryInstance->read($dir) as $path) {
                $directoryInstance->delete($path);
            }
        }

        return $this;
    }

    /**
     * Save directory to storage
     *
     * @param array $dir
     * @throws \Magento\Core\Exception
     * @return bool
     */
    public function saveDir($dir)
    {
        if (!isset($dir['name']) || !strlen($dir['name']) || !isset($dir['path'])) {
            return false;
        }

        $path = (strlen($dir['path']))
            ? $dir['path'] . '/' . $dir['name']
            : $dir['name'];

        try {
            $this->_filesystem->getDirectoryWrite(\Magento\Filesystem::MEDIA)->create($path);
        } catch (\Exception $e) {
            $this->_logger->log($e->getMessage());
            throw new \Magento\Core\Exception(
                __('Unable to create directory: %1', \Magento\Filesystem::MEDIA . '/' . $path)
            );
        }

        return true;
    }

    /**
     * Save file to storage
     *
     * @param string $filePath
     * @param string $content
     * @param bool $overwrite
     * @throws \Magento\Core\Exception
     * @return bool
     */
    public function saveFile($filePath, $content, $overwrite = false)
    {
        try {
            $directoryInstance = $this->_filesystem->getDirectoryWrite(\Magento\Filesystem::MEDIA);
            if (!$directoryInstance->isFile($filePath) || ($overwrite && $directoryInstance->delete($filePath))) {
                $directoryInstance->writeFile($filePath, $content);
                return true;
            }
        } catch (\Magento\Filesystem\FilesystemException $e) {
            $this->_logger->log($e->getMessage());
            throw new \Magento\Core\Exception(__('Unable to save file: %1', $filePath));
        }

        return false;
    }
}
