<!DOCTYPE html>
<html>

<head>
    <title>Web page verification.</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Web page verification.</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        error_reporting(0);
                        require __DIR__ . '/vendor/autoload.php';
                        // Función para verificar si la página es en Hindí
                        function isHindi($html)
                        {
                            $crawler = new \Symfony\Component\DomCrawler\Crawler($html);
                            $langAttr = $crawler->filter('html')->attr('lang');
                            return stripos($langAttr, 'hi') !== false;
                        }

                        // Función para obtener el tiempo de carga de la página
                        function getPageLoadTime($url)
                        {
                            $start = microtime(true);
                            $contents = file_get_contents($url);
                            $end = microtime(true);
                            $loadTime = round($end - $start, 2);
                            return $loadTime;
                        }

                        // Función para verificar si hay imágenes pesadas en la página
                        function hasHeavyImages($html)
                        {
                            $crawler = new \Symfony\Component\DomCrawler\Crawler($html);
                            $images = $crawler->filter('img');
                            foreach ($images as $image) {
                                $size = getimagesize($image->getAttribute('src'));
                                if ($size && $size[0] * $size[1] > 500000) {
                                    return true;
                                }
                            }
                            return false;
                        }

                        // Función para verificar si hay errores de JavaScript
                        function hasJavascriptErrors($html)
                        {
                            $crawler = new \Symfony\Component\DomCrawler\Crawler($html);
                            $scripts = $crawler->filter('script');
                            foreach ($scripts as $script) {
                                $jsErrors = preg_match_all('/console\.(log|warn|error)/', $script->textContent);
                                if ($jsErrors) {
                                    return true;
                                }
                            }
                            return false;
                        }

                        // Verificar si se ha enviado una URL por POST
                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['url'])) {
                            $url = $_POST['url'];
                            $contents = file_get_contents($url);

                            $time_limit = 5; // límite de tiempo en segundos

                            // Verificar si el tiempo de carga es mayor a 5 segundos
                            $loadTime = getPageLoadTime($url);
                            if ($loadTime > $time_limit) {
                                echo '<div class="alert alert-danger" role="alert">The page FAILS: the loading time is more than 5 seconds.                                </div>';
                                exit();
                            }

                            // Verificar si hay imágenes pesadas
                            if (hasHeavyImages($contents)) {
                                echo '<div class="alert alert-danger" role="alert">The page FAILS: it contains heavy images.</div>';
                                exit();
                            }


                            // Verificar si hay errores de JavaScript
                            if (hasJavascriptErrors($contents)) {
                                echo '<div class="alert alert-danger" role="alert">The page does NOT PASS: it has JavaScript errors.</div>';
                                exit();
                            }

                            // Verificar si la página está en Hindí
                            if (isHindi($contents)) {
                                echo '<div class="alert alert-success" role="alert">The page PASSES: it is in Hindi.</div>';
                                exit();
                            }

                            // Si no se cumple ninguna condición anterior, la página pasa la verificación
                            echo '<div class="alert alert-success" role="alert">The page PASSES: it has been verified and has no issues.</div>';
                        }
                        ?>
                        <form method="POST">
                            <div class="form-group">
                                <label for="url">Enter the URL of the page you want to verify:</label>
                                <input type="text" name="url" id="url" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>