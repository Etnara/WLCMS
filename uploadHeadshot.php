<?php

function upload_image($fileInputName = 'image')
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$fileInputName];

    // Validate Size (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }

    // Validate extension
    $allowedExtensions = ['png', 'jpg', 'jpeg'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        return null;
    }

    // Validate MIME type
    $allowedMimes = ['image/png', 'image/jpeg'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowedMimes)) {
        return null;
    }

    // Create uploads directory if missing
    $uploadDir = 'headshots/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Create unique filename
    $newFilename = uniqid('img_', true) . '.' . $extension;
    $destination = $uploadDir . $newFilename;

    // Move file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination;    // SUCCESS
    }

    return null; // FAIL
}