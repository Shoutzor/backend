<?php

namespace App\Jobs;

use App\MediaSource\Base\ProcessorPipeline;
use App\MediaSource\Base\Processors\ProcessorError;
use App\MediaSource\File\Processors\FileExistsProcessor;
use App\MediaSource\File\Processors\ID3GetTitleProcessor;
use App\MediaSource\File\Processors\MediaDurationProcessor;
use App\MediaSource\File\Processors\MediaFileHashProcessor;
use App\MediaSource\ProcessorItem;
use App\Models\Media;
use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Upload $upload;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 20;

    /**
     * Create a new job instance.
     *
     * @param Upload $upload
     */
    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("Updating upload status to: processing",[$this->upload]);

        //Update the status
        $this->upload->status = Upload::STATUS_PROCESSING;
        $this->upload->save();

        Log::debug("Start processing upload");

        (new ProcessorPipeline(app()))
            ->send(new ProcessorItem(
                $this->upload,
                $this->createBaseMediaObject($this->upload)
            ))
            ->through([
                FileExistsProcessor::class,
                MediaFileHashProcessor::class,
                MediaDurationProcessor::class,
                ID3GetTitleProcessor::class
            ])
            // Error caught while processing
            ->onError(function (ProcessorError $error) {
                $exception = $error->getException();

                if ($exception !== null) {
                    Log::critical("An exception occured while processing the job: " . $exception->getMessage());
                    error_log("An exception occured while processing the job: " . $exception->getMessage());
                } else {
                    Log::debug("An error returned while processing, error: " . $error->error);

                    // No exception set, set the exception with the error returned from processing
                    // This exception is used when marking the job as failed.
                    $exception = new \Exception($error->error);
                }

                // Upload Exists Exception has been thrown. Stop further processing of this job.
                if ($error->rejected) {
                    Log::debug("Upload is rejected, will not retry processing. Deleting upload");
                    // Delete the upload object
                    $this->upload->delete();
                } // If the # of attempts has exceeded the allowed # of tries, Stop further processing of this job.
                elseif ($this->attempts() >= $this->tries) {
                    Log::debug("Number of processing tries exceeded. Marking upload as failed (final)");
                    $this->upload->status = Upload::STATUS_FAILED_FINAL;
                }
                // Update the status of the upload to failed_retry to indicate to the frontend that it has failed
                // but will be re-attempted
                else {
                    Log::debug("Marking upload as failed (with retry)");
                    $this->upload->status = Upload::STATUS_FAILED_RETRY;
                }

                if(!$error->rejected) {
                    // Save the status
                    $this->upload->save();
                    $this->release($this->backoff);
                }
            })
            ->then(function (ProcessorItem $item) {
                $upload = $item->getUpload();
                $media = $item->getMedia();

                // Check if the file needs to be moved once finished
                if ($item->moveFileOnFinish()) {
                    Storage::move(
                        Upload::STORAGE_PATH . $upload->filename,
                        Media::STORAGE_PATH . $media->filename
                    );
                }

                // Save the media instance to the database
                $media->save();

                // Delete the upload from the database
                $upload->delete();

                Log::debug("Processing successfully finished");
            });
    }

    /**
     * Create the base media object for the uploaded file
     * This method only sets some default properties and does not do any saniziting or processing yet.
     * @param Upload $upload
     * @return Media
     */
    private function createBaseMediaObject(Upload $upload): Media
    {
        return new Media([
            'title' => '',
            'filename' => $upload->filename,
            'duration' => 0,
            'is_video' => false
        ]);
    }
}
