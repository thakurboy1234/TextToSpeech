<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

class FileController extends Controller
{

    public function fetchFile($id)
    {
        $file = File::find($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->json(['file' => $file], 200);
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:txt|max:2048',
        ]);

        $file = $request->file('file');
        $originalFileName = $file->getClientOriginalName();
        $filename = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME); // Exclude the extension
        $fileExtension = $file->getClientOriginalExtension(); // Get the file extension
        $filePath = $file->storeAs('uploads', $filename . '.' . $fileExtension); // Include the extension

        $savedFile = File::create([
            'filename' => $filename,
            'path' => $filePath,
        ]);

        $fileContent = utf8_encode(file_get_contents(storage_path('app/' . $filePath)));
        $maxLength = 5000;

        // Check if the content length is greater than the maximum allowed
        if (strlen($fileContent) > $maxLength) {
            $fileContent = substr($fileContent, 0, $maxLength);
        }
        $textToSpeechClient = new TextToSpeechClient();

        $input = new SynthesisInput();
        $input->setText($fileContent);

        $voice = new VoiceSelectionParams();
        $voice->setLanguageCode('hi-IN');

        $audioConfig = new AudioConfig();
        $audioConfig->setAudioEncoding(AudioEncoding::MP3);

        $resp = $textToSpeechClient->synthesizeSpeech($input, $voice, $audioConfig);
        $audioContent = $resp->getAudioContent();

        $audioFilePath = storage_path('app/public/' . $filename . '.mp3');
        file_put_contents($audioFilePath, $audioContent);

        // Generate the full audio URL using the current project URL
        $audioUrl = url('storage/' . $filename . '.mp3');

        // Update the saved file with the generated audio URL
        $savedFile->update(['audio_url' => 'storage/' . $filename . '.mp3']);

        $textToSpeechClient->close();

        return response()->json([
            'message' => 'File uploaded and audio generated successfully',
            'file' => $savedFile,
            'path' => url($audioUrl), // Include .xls in the path
        ], 201);
    }


    public function fetchRecords()
    {
        $files = File::get();
        foreach ($files as $file) {
            // $audio_url = url($file->audio_url);
            $file->audio_url = url($file->audio_url);
        }

        return response()->json([
            'files' => $files,
        ], 200);
    }
}
