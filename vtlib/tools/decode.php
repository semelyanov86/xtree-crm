<?php

chdir(__DIR__ . '/../..');

function getFilePathFromUser()
{
    echo 'Please enter the path to the PHP file: ';
    $handle = fopen('php://stdin', 'r');
    $filePath = trim(fgets($handle));
    fclose($handle);

    return $filePath;
}

$filePath = $argc === 2 ? $argv[1] : getFilePathFromUser();

if (!file_exists($filePath)) {
    echo "File not found: {$filePath}\n";
    exit(1);
}

// Получение контента файла
$fileContent = file_get_contents($filePath);

// Регулярное выражение для поиска строки в функции eval
$pattern = '/eval\(\' \?>\' \. \w+\(\'(.*?)\'\)\;/s';

if (!preg_match($pattern, $fileContent, $matches)) {
    echo "Encoded string not found in the file.\n";
    exit(1);
}

// Извлечение закодированной строки
$encodedString = $matches[1];

// Декодирование строки
$decodedString = base64_decode(str_rot13($encodedString));

// Запись декодированного контента обратно в файл, удаляя старое содержимое
if (file_put_contents($filePath, $decodedString) === false) {
    echo "Failed to write decoded content to the file.\n";
    exit(1);
}

echo "Decoded content has been written back to the file.\n";
