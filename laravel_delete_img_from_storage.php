<?php

use Illuminate\Support\Facades\Storage;

$path = 'path/to/image.jpg';

if (Storage::disk('public')->exists($path)) {
    Storage::disk('public')->delete($path);
    // Image deleted successfully
} else {
    // Image not found
}