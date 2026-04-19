<?php

declare(strict_types=1);

require __DIR__.'/../src/bootstrap.php';

$pdo = db();

$deletedMessages = $pdo->prepare('DELETE FROM messages WHERE expires_at IS NOT NULL AND expires_at <= NOW()');
$deletedMessages->execute();

$typingDir = base_path('storage/typing');
$removedTyping = 0;
if (is_dir($typingDir)) {
    foreach (glob($typingDir.'/*.json') ?: [] as $file) {
        if (filemtime($file) !== false && filemtime($file) < (time() - 20)) {
            @unlink($file);
            $removedTyping++;
        }
    }
}

echo 'Cleanup done. Messages removed: '.$deletedMessages->rowCount().', typing markers removed: '.$removedTyping.PHP_EOL;
