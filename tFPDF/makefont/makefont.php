<?php
/*
 * tFPDF Font Generator
 * This script generates font metric files (.z and .ctg.z) for use with tFPDF.
 * 
 * Usage:
 * Place this script in the `makefont/` directory of your tFPDF installation.
 * Run it via the browser or command line:
 * php makefont.php
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include tFPDF
require('../tFPDF/tfpdf.php');

// Function to generate font files
function makeFont($fontFile, $enc = 'cp1252', $embed = true) {
    require('../tFPDF/makefont/makefont.php');
    MakeFont($fontFile, $enc, $embed);
}

// Example: Generate NotoSansGujarati font files
$fontPath = '../tFPDF/font/NotoSansGujarati.ttf'; // Update the path if needed

if (file_exists($fontPath)) {
    makeFont($fontPath, 'UTF-8');
    echo "Font files for 'NotoSansGujarati.ttf' have been successfully generated.";
} else {
    echo "Error: Font file not found at '$fontPath'. Please verify the path.";
}
?>
