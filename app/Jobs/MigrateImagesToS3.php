<?php

namespace App\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MigrateImagesToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     protected $model;
     protected $photoField;
     protected $baseUrl = 'https://res.cloudinary.com/iamdevmaniac/client_cat/';

    /**
     * Create a new job instance.
     */
    public function __construct($model, $photoField)
    {
        $this->model = $model;
        $this->photoField = $photoField;
    }

    /**
     * Execute the job.
     */
    public function handle()
     {
         $count = 0;
         $modelClass = $this->model;
         $records = $modelClass::whereNotNull($this->photoField)->get();

         foreach ($records as $record) {
             $photoUrl = $record->{$this->photoField};

             if (!$photoUrl) {
                 continue;
             }

             // Add base URL if not present
             if (!str_starts_with($photoUrl, 'https')) {
                 $photoUrl = $this->baseUrl . $photoUrl;
             }

             try {
                 // Download image
                 $response = Http::get($photoUrl);
                 if (!$response->successful()) {
                     continue;
                 }
  // Generate unique filename
  $extension = pathinfo($photoUrl, PATHINFO_EXTENSION) ?: 'jpg';
  $filename = uniqid() . '.' . $extension;

  // Upload to S3
  Storage::disk('s3')->put($filename, $response->body());

  // Update record with new S3 URL
  $record->update([
      $this->photoField => $filename
  ]);

  $count++;
} catch (\Exception $e) {
  continue;
}
}
  // Send notification
  Notification::make()
  ->title('Image Migration Complete')
  ->body("Successfully migrated {$count} images to S3")
  ->success()
  ->send();
}
}
