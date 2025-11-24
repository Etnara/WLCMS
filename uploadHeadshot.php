<?php

function upload_image($fileInputName = 'image')
{
    $fail = [];
    $fail['headshot'] = NULL;
    $fail['MIME'] = NULL;

    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return $fail;
    }

    $file = $_FILES[$fileInputName];

    // Validate Size (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return $fail;
    }

    // Validate extension
    $allowedExtensions = ['png', 'jpg', 'jpeg'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        return $fail;
    }

    // Validate MIME type
    $allowedMimes = ['image/png', 'image/jpeg'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowedMimes)) {
        return $fail;
    }

    $contents = file_get_contents($file['tmp_name']);
    $fail['headshot'] = $contents;
    $fail['MIME'] = $mime;
    return $fail;
}