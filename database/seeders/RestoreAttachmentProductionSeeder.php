<?php

namespace Database\Seeders;

use League\Csv\Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use App\Models\Attachments\Attachment;

class RestoreAttachmentProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/data/restore-attachment/');
        $path = storage_path('app/attachments');
        if (! file_exists($path)) {
            File::makeDirectory($path);
        }

        foreach ($finder as $file) {
            $this->command->info('Copying from attachment : '.$file->getRealPath());

            try {
                File::copy($file->getRealPath(), $path.'/'.$file->getFilename());
            } catch (Exception $e) {
                $this->command->alert('Failed to copy attachment : '.$file->getRealPath());
                dd($e->getMessage());
            }
        }
        $this->command->info('Copied '.count($finder).' attachments');

        $this->command->info('Renaming Attachment Path...');
        $attachments = Attachment::query()->get();
        foreach ($attachments as $attachment) {
            if (null !== $attachment->path) {
                $exploding = explode('/', $attachment->path);
                $fileName = $exploding[count($exploding) - 1];
                $attachment->path = 'attachments/'.$fileName;
                $attachment->save();
            }
        }
    }
}
