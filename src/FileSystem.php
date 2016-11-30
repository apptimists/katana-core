<?php
namespace Katana;

use FilesystemIterator;
use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;
class Filesystem extends BaseFilesystem
{
    public function cleanDirectoryIgnoreDirectory($directory, $ignoreDirectory)
    {
      if (! $this->isDirectory($directory)) {
            return false;
        }
        $items = new FilesystemIterator($directory);
        foreach ($items as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                if ($item->getFilename() == $ignoreDirectory) {
                    continue;
                }
                $this->deleteDirectory($item->getPathname());
            } else {
                $this->delete($item->getPathname());
            }
        }
        return true;
    }
}
