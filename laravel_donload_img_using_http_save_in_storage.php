<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    # downlaod img
    $response = Http::get($img_url);

    if ($response->ok()) {
           
    $imageData = $response->body();
    $imageFileName = $img_name;
    
    Storage::disk('public')->put('asset_images/' . $imageFileName, $imageData);

    }
}