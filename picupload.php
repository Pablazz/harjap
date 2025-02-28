<?php
// popup.php (Popup page)
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Picture Popup</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .popup-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        #fileInput {
            display: none;
        }
    </style>
</head>
<body>

<div class="popup-container">
    <div class="popup-content">
        <input type="file" id="fileInput" accept="image/*">
        <label for="fileInput">Choose new image</label>
        <br>
        <button id="closePopup">Close</button>
    </div>
</div>

<script>
    const closePopup = document.getElementById('closePopup');
    const fileInput = document.getElementById('fileInput');

    closePopup.addEventListener('click', () => {
        window.location.href = 'image_viewer.php'; // Redirect back to the viewer
    });

    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                // Send the image data to the image_viewer.php via query parameter.
                window.location.href = 'image_viewer.php?imageData=' + encodeURIComponent(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    //Prevent going back with the back button.
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function () {
        window.history.go(1);
    };

</script>

</body>
</html>