<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphqlRequestException;
use App\Helpers\ShoutzorSetting;
use App\Jobs\ProcessUpload;
use App\Models\Upload as UploadModel;
use DanielDeWit\LighthouseSanctum\Traits\HasAuthenticatedUser;
use DanielDeWit\LighthouseSanctum\Traits\HasUserModel;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use JetBrains\PhpStorm\ArrayShape;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Upload
{
    use HasAuthenticatedUser;
    use HasUserModel;

    protected AuthFactory $authFactory;

    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    /**
     * @param ResolveInfo $resolveInfo
     * @return Upload
     */
    #[ArrayShape(['user' => "\App\Models\User"])] public function __invoke($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): UploadModel
    {
        $this->resolveInfo = $resolveInfo;
        $user = $this->getAuthenticatedUser();

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $args['file'];

        //Check if there are any errors with the file upload
        if ($file->isValid() !== true) {
            throw new GraphqlRequestException('The file did not upload correctly');
        }

        //Get the name and extension of the file
        $name = md5($file->getClientOriginalName());
        $ext = $file->extension();

        // Check if the file extension is allowed
        if(!in_array($ext, ShoutzorSetting::uploadAllowedExtensions())) {
            throw new GraphqlRequestException('The uploaded file extension is not allowed');
        }

        //Set the new filename (format: timestamp-md5hash.ext)
        $newName = time() . '-' . $name . '.' . $ext;

        //Move the file to a temporary directory while it's awaiting processing.
        $file->storeAs(UploadModel::STORAGE_PATH, $newName);

        //Store the upload in the database for use in the Job
        $upload = UploadModel::create([
            'original_filename' => strip_tags($file->getClientOriginalName()),
            'filename' => $newName,
            'uploaded_by' => $user->id,
            'status' => UploadModel::STATUS_QUEUED
        ]);

        //Add the Upload as a job to the Queue for processing
        ProcessUpload::dispatch($upload)->onQueue(UploadModel::QUEUE_NAME);

        return $upload;
    }

    protected function getAuthFactory(): AuthFactory
    {
        return $this->authFactory;
    }
}
