<?php

namespace Octopy\DDD\Installer\FileInfo;

use SplFileInfo;

class NewFileInfo extends OriFileInfo
{
    /**
     * @return SplFileInfo
     */
    public function getNewFileInfo() : SplFileInfo
    {
        return new SplFileInfo(
            str($this->file->getPathname())->replace('/Infrastructure/Core', '')
        );
    }
}
