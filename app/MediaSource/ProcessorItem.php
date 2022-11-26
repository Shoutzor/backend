<?php

namespace App\MediaSource;

use App\Models\Media;
use App\Models\Upload;

class ProcessorItem
{
    public function __construct(
        private readonly Upload $upload,
        private readonly Media  $media,
        private bool $moveFileOnFinish = true
    ) {}

    /**
     * Will return the Upload model instance of the file currently being processed
     * you should not modify any properties of this instance
     * @return Upload
     */
    public function getUpload() : Upload
    {
        return $this->upload;
    }

    /**
     * Contains an instance for the Media model which hasn't been saved to the database yet
     * but will be once processing finishes
     *
     * You can modify properties of this instance during processing
     * @return Media
     */
    public function getMedia() : Media
    {
        return $this->media;
    }

    /**
     * Whether the Uploaded file will need to be moved from
     * the temp upload directory to the media storage directory
     * once processing completes.
     * @return bool
     */
    public function moveFileOnFinish() : bool
    {
        return $this->moveFileOnFinish;
    }

    /**
     * Here you can set moveFileOnFinished to false when you have
     * for example a mediasource that downloads its file from an
     * external source straight to the Media storage directory.
     * @param bool $needed
     * @return void
     */
    public function setMoveFileOnFinish(bool $needed): void
    {
        $this->moveFileOnFinish = $needed;
    }
}