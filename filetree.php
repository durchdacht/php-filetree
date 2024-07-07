<?php
if (isset($_GET['action']) && $_GET['action'] == 'get_tree') {
    function getDirContents($dir) {
        $results = array();
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = array("type" => "file", "name" => $value, "path" => $path);
            } else if ($value != "." && $value != "..") {
                $results[] = array("type" => "folder", "name" => $value, "path" => $path, "contents" => getDirContents($path));
            }
        }

        return $results;
    }

    $directory = '.'; // Hauptverzeichnis
    $results = getDirContents($directory);

    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'export') {
    function getDirContents($dir) {
        $results = array();
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = array("type" => "file", "name" => $value, "path" => $path);
            } else if ($value != "." && $value != "..") {
                $results[] = array("type" => "folder", "name" => $value, "path" => $path, "contents" => getDirContents($path));
            }
        }

        return $results;
    }

    $directory = '.'; // Hauptverzeichnis
    $results = getDirContents($directory);

    $subDir = str_replace('/', '_', trim(dirname($_SERVER['SCRIPT_NAME']), '/'));
    $domain = $_SERVER['HTTP_HOST'];
    $filename = $domain . ($subDir ? '-' . $subDir : '') . '-filetree.json';

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Tree</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }
        .dark-mode .file-tree-container {
            background-color: #1e1e1e;
        }
        .folder, .file {
            margin-left: 20px;
            transition: opacity 0.5s, transform 0.5s;
        }
        .file {
            opacity: 0;
            transform: translateY(20px);
        }
        .file.loaded {
            opacity: 1;
            transform: translateY(0);
        }
        .file-tree-container {
            height: 70vh;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 1rem;
            background-color: #f5f5f5;
        }
        .tree-structure {
            position: relative;
            padding: 0;
            list-style: none;
        }
        .tree-structure ul {
            position: relative;
            padding-left: 1.5rem;
        }
        .tree-structure li {
            position: relative;
            padding-left: 1rem;
            line-height: 1.5rem;
        }
        .tree-structure li:before, .tree-structure li:after {
            content: '';
            position: absolute;
            left: 0;
        }
        .tree-structure li:before {
            border-left: 2px solid #000;
            top: 0;
            bottom: 0;
            height: 100%;
            width: 1rem;
        }
        .tree-structure li:after {
            border-top: 2px solid #000;
            top: 1rem;
            width: 1rem;
            height: 1rem;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="dark-mode">
    <div class="container mt-5">
        <div class="logo">FLD.WTF</div>
        <h1 class="mb-4">File Tree</h1>
        <button id="mode-toggle" class="btn btn-light mb-4">Toggle Dark Mode</button>
        <button id="start-btn" class="btn btn-primary mb-4">Start</button>
        <div class="file-tree-container">
            <ul id="file-tree" class="tree-structure"></ul>
        </div>
        <button id="export-btn" class="btn btn-success mt-4">Export</button>
    </div>

    <script>
        async function fetchFileTree() {
            const response = await fetch('?action=get_tree');
            const fileTree = await response.json();
            return fileTree;
        }

        function createFileElement(item) {
            const element = document.createElement('li');
            element.classList.add(item.type);
            element.innerHTML = `<span>${item.name}</span>`;
            return element;
        }

        function renderFileTree(fileTree, container) {
            fileTree.forEach((item, index) => {
                setTimeout(() => {
                    const element = createFileElement(item);
                    container.appendChild(element);

                    if (item.type === 'folder' && item.contents) {
                        const ul = document.createElement('ul');
                        element.appendChild(ul);
                        renderFileTree(item.contents, ul);
                    }

                    if (item.type === 'file') {
                        element.classList.add('loaded');
                    }
                }, index * 100);
            });
        }

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('mode-toggle').addEventListener('click', toggleDarkMode);
            document.getElementById('start-btn').addEventListener('click', async () => {
                const fileTree = await fetchFileTree();
                const treeContainer = document.getElementById('file-tree');
                treeContainer.innerHTML = '';
                renderFileTree(fileTree, treeContainer);
            });
            document.getElementById('export-btn').addEventListener('click', () => {
                window.location.href = '?action=export';
            });
        });
    </script>
</body>
</html>
