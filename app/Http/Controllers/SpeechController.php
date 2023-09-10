<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

class SpeechController extends Controller
{
    public function convertTextToSpeech(Request $request)
    {
        $text = $request->input('text');

        $textToSpeechClient = new TextToSpeechClient();

        $input = new SynthesisInput();
        $input->setText('Japan\'s national soccer team won against Colombia!');
        $voice = new VoiceSelectionParams();
        // return 12;
        $voice->setLanguageCode('en-US');
        $audioConfig = new AudioConfig();
        $audioConfig->setAudioEncoding(AudioEncoding::MP3);

        $resp = $textToSpeechClient->synthesizeSpeech($input, $voice, $audioConfig);
        // file_put_contents('test.mp3', $resp->getAudioContent());
        $filePath = storage_path('app/public/converted_audio.mp3');
        file_put_contents($filePath, $resp->getAudioContent());


        return response()->json(['message' => 'Audio saved successfully', 'path' => $filePath]);
    }

    public function convertFileToSpeech(Request $request)
    {
        $filePath = storage_path('app/uploads/1693493418_sample.txt'); // Update with your file path

        // Read the content of the file
        $fileContent = file_get_contents($filePath);

        // Initialize the TextToSpeechClient
        $client = new TextToSpeechClient();

        // Set up synthesis input
        $synthesisInput = (new SynthesisInput())->setText($fileContent);

        // Set up audio config
        $audioConfig = (new AudioConfig())->setAudioEncoding(AudioEncoding::MP3);

        // Perform the text-to-speech conversion
        $response = $client->synthesizeSpeech($synthesisInput, $audioConfig);
        $audioContent = $response->getAudioContent();

        $client->close();

        // Save audio to a file
        $audioFilePath = storage_path('app/public/converted_audio.mp3');
        file_put_contents($audioFilePath, $audioContent);

        return response()->json(['message' => 'Audio saved successfully']);
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
        $config = [
            'credentials' => '\C:\Users\inclu\Downloads\fair-catbird-397610-2935f8d9fe75.json'
        ];
        $textToSpeechClient = new TextToSpeechClient($config);

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
}
