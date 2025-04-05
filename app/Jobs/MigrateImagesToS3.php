<?php

namespace App\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MigrateImagesToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     protected $model;
     protected $photoField;
     protected $baseUrl = 'https://res.cloudinary.com/iamdevmaniac/';
    // protected $baseUrl = "https://schoolcompasse.s3.us-east-1.amazonaws.com/";

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
        // Log::info($modelClass);
         foreach ($records as $record) {
             $photoUrl = $record->{$this->photoField};

            //  Log::info($photoUrl);
             if (!$photoUrl) {
                 continue;
             }

             // Add base URL if not present
             if (!str_starts_with($photoUrl, 'https')) {
                 $photoUrl = $this->baseUrl . $photoUrl;
             }

             Log::info($photoUrl);
             try {
                 // Download image
                 $response = Http::get($photoUrl);
                 Log::info($response);
                 if (!$response->successful()) {
                     continue;
                 }
  // Generate unique filename
  $extension = pathinfo($photoUrl, PATHINFO_EXTENSION) ?: 'jpg';
//   Log::info($extension);
  $filename = uniqid() . '.' . $extension;
//   Log::info($filename);
  // Upload to S3
  Storage::disk('s3')->put($filename, $response->body());
// Log::info($filename);
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
