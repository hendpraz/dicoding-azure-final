<!DOCTYPE html>
<html lang="id">
<head>
    <title>ImageAnalyst - Analyze Your Image</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aplikasi yang menganalisis konten yang ada pada berkas gambar">
    <meta http-equiv="content-language" content="id-id">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <style>
        .error {
            color : red;
        }

        .message {
            color : green;
        }

        .container {
            margin-top : 3%;
        }

        form {
            margin-top : 5%;
        }

        img {
            max-width : 60vw;
        }

        #caption {
            font-size : x-large;
        }
    </style>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center">ImageAnalyst</h1>

    <?php
        require_once 'vendor/autoload.php';

        use MicrosoftAzure\Storage\Blob\BlobRestProxy;
        use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
        use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
        use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
        use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        processFileUpload();

        function processFileUpload(){
            // Process File Upload
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["fileToUpload"]["name"]);
            $uploadOk = false;
            $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
            
            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                if($check !== false) {
                    $uploadOk = true;
                } else {
                    echo "<p class='error'>InvalidFile : File is not an image.<br /></p>";
                    $uploadOk = false;
                }

                if (file_exists($targetFile)) {
                    echo "<p class='error'>Sorry, file already exists.<br /></p>";
                    $uploadOk = false;
                }
            } else {
                echo "<p class='error'>No file submitted<br /></p>";
            }

            if($uploadOk) {
                if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)){
                    echo "<p class='message'>Success! " .$targetFile . "</p>";
                    echo "<br />";
                    echo "<img src='" . $targetFile . "' alt='img'></img>";
                    echo "<br />";
                    processBlobClient($targetFile);
                } else {
                    echo "<p class='error'>There was an error in uploading file<br /></p>";
                }
            } else {
                echo "<p class='error'>Your file is not uploaded<br /></p>";
            }
        }

        function processBlobClient($fileToUpload) {
            // Process creating blob client
            $connectionString = "DefaultEndpointsProtocol=https;AccountName=" . getenv('ACCOUNT_NAME') . ";AccountKey=" . getenv('ACCOUNT_KEY');

            $blobClient = BlobRestProxy::createBlobService($connectionString);

            $containerName = "blockblobsbzgbgh";
            
            uploadBlob($blobClient, $containerName, $fileToUpload);
            analyzeImage($containerName, $fileToUpload);
        }

        function uploadBlob($blobClient, $containerName, $fileToUpload) {
            // Upload blob to the defined container

            // Upload file as BlockBlob
            echo "<p class='message'>Uploading BlockBlob: " . PHP_EOL . $fileToUpload . "<br /></p>";
            
            $content = fopen($fileToUpload, "r");
            
            // Upload blob
            $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
        }

        function analyzeImage($containerName, $fileToUpload){
            $subscriptionKey = getenv('SUBSCRIPTION_KEY');
            $imageUrl = getenv('BLOB_CONTAINER_URL');
            $imageUrl = $imageUrl . $containerName . '/';
            $imageUrl = $imageUrl . $fileToUpload;
            
            $data = array(
                "url" => $imageUrl,
            );

            $url = "https://centralus.api.cognitive.microsoft.com/";
            $url = $url . "vision/v2.0/analyze?visualFeatures=Description&language=en";

            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/json\r\n"
                         . 'Ocp-Apim-Subscription-Key: '. $subscriptionKey . "\r\n"
                         . 'host: centralus.api.cognitive.microsoft.com' ,
                    'method'  => 'POST',
                    'content' => json_encode( $data ),
                )
            );

            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            
            if ($result === FALSE) {
                echo "<p class='error'>There was an error analyzing image<br /></p>";
            } else {
                $array = json_decode($result, true); 
                echo "<br />";
                echo "<br />";
                $temp = $array["description"]["captions"]["0"]["text"];
                $temp = '<b>' . $temp . '</b>';
                echo "<div id='caption' class='shadow p-4 mb-4 bg-white'> Caption: " . $temp . "</div>";
            }
        }
    ?>

            <br />
            <a class="btn btn-primary" href="index.php">Home</a>
            </div>
        </div>
    </div>


    <!-- Footer scripts  -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>