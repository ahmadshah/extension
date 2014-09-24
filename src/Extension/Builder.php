<?php namespace Orchestra\Extension;

use Illuminate\Contracts\Filesystem\Filesystem;

class Builder {

    /**
     * @var
     */
    protected $files;

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function make()
    {

    }

}