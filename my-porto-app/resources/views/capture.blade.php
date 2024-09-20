<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capture Location and Photo</title>
    <style>
        canvas {
            display: none;
        }
        img {
            display: block;
            margin: 20px auto;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Capture User Location and Photo</h2>

    <!-- Display location -->
    <p id="lok">Location: </p>

    <!-- Hidden video stream -->
    <video id="video" style="display: none;" autoplay></video>

    <!-- Hidden canvas for capturing photo -->
    <canvas id="canvas"></canvas>

    <!-- Display captured photo -->
    <img id="photo" alt="Your Photo">

    <script>
        const canvas = document.getElementById('canvas');
        const photo = document.getElementById('photo');
        const video = document.getElementById('video');

        // Get user geolocation
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        // Show geolocation coordinates on the page
        function showPosition(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            document.getElementById("lok").innerHTML = "Latitude: " + lat + " " + lon;
        }

        function showError(error) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    alert("User denied the request for Geolocation.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Location information is unavailable.");
                    break;
                case error.TIMEOUT:
                    alert("The request to get user location timed out.");
                    break;
                case error.UNKNOWN_ERROR:
                    alert("An unknown error occurred.");
                    break;
            }
        }

        // Access the device camera and capture a photo
        function capturePhoto() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then((stream) => {
                    video.srcObject = stream;

                    // Ensure video is ready
                    video.onloadedmetadata = () => {
                        video.play();

                        // Add a delay to ensure video frame is ready
                        setTimeout(() => {
                            console.log('Capturing photo after delay.');
                            const context = canvas.getContext('2d');
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            context.drawImage(video, 0, 0, canvas.width, canvas.height);

                            // Convert the canvas to a data URL (base64 encoded image)
                            const dataUrl = canvas.toDataURL('image/png');

                            // Display the photo in the img element
                            photo.src = dataUrl;

                            // Stop the video stream once the photo is captured
                            stream.getTracks().forEach(track => track.stop());
                        }, 1000); // 1 second delay
                    };

                    video.onerror = (err) => {
                        console.error('Error with video stream:', err);
                    };
                })
                .catch((err) => {
                    console.error("Error accessing camera: " + err);
                });
        }

        // Start capturing geolocation and camera when the page loads
        window.onload = () => {
            getLocation();  // Capture location first
            capturePhoto();  // Capture photo with video stream hidden
        };
    </script>
</body>
</html>
