<?php
session_start();
include '../authentication/db.php';
include '../page/topnavbar.php';

// Assuming user_id is stored in the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.1.4/dist/full.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <!-- Three.js for 3D Model -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three/examples/js/loaders/FBXLoader.js"></script>

    <title>Purchase History</title>
    <style>
        /* Ensures the footer stays at the bottom */
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        /* Canvas styling */
        #threeCanvas {
            width: 100%;
            height: 500px;
            background-color: #222; /* Dark background */
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">

    <div class="content">
        <h1 class="text-2xl font-bold text-center my-4">3D Motorcycle Battery Model</h1>
        <canvas id="threeCanvas"></canvas>
    </div>

    <?php include '../page/footer.php'; ?>

    <script>
        // Initialize Three.js scene
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById("threeCanvas"), antialias: true, alpha: true });
        renderer.setSize(window.innerWidth * 0.8, 500);
        document.body.appendChild(renderer.domElement);

        // Lighting
        const light = new THREE.AmbientLight(0xffffff, 1);
        scene.add(light);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 2);
        directionalLight.position.set(2, 2, 5).normalize();
        scene.add(directionalLight);

        // Load FBX Model
        const loader = new THREE.FBXLoader();
        loader.load('../models/Motorcycle_Battery_0312023101.fbx', function (object) {
            object.scale.set(0.05, 0.05, 0.05);  // Adjust size
            object.position.set(0, -1, 0);
            scene.add(object);

            // Add rotation animation
            function animate() {
                requestAnimationFrame(animate);
                object.rotation.y += 0.01;  // Rotating animation
                renderer.render(scene, camera);
            }
            animate();
        });

        // Camera positioning
        camera.position.z = 5;

        // Handle window resize
        window.addEventListener("resize", () => {
            const width = window.innerWidth * 0.8;
            const height = 500;
            renderer.setSize(width, height);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
        });
    </script>

</body>
</html>
