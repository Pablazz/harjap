<?php
// image_viewer.php modified to accept the image data.
$imageData = isset($_GET['imageData']) ? $_GET['imageData'] : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Viewer</title>
    <style>
        #myImage {
            max-width: 300px;
            max-height: 300px;
            display: block;
            margin: 0 auto 10px;
        }
    </style>
</head>
<body>

<img id="myImage" src="<?php echo $imageData ? $imageData : 'default.jpg'; ?>" alt="My Image">
<button id="changePicButton">Change Picture</button>

<script>
    const changePicButton = document.getElementById('changePicButton');

    changePicButton.addEventListener('click', () => {
        window.location.href = 'popup.php'; // Redirect to the popup page
    });
</script>

</body>
</html>