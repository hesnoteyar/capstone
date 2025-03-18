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
<!-- Include Three.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<!-- fflate (Needed for FBXLoader) -->
<script src="https://cdn.jsdelivr.net/npm/fflate@0.7.4/umd/index.min.js"></script>

<!-- FBXLoader -->
<script src="https://cdn.jsdelivr.net/npm/three/examples/js/loaders/FBXLoader.js"></script>

<!-- OrbitControls for mouse interaction -->
<script src="https://cdn.jsdelivr.net/npm/three/examples/js/controls/OrbitControls.js"></script>


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


    <script>
        // Create Three.js Scene
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.set(0, 2, 6); // Adjust camera to be further

        // Renderer
        const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById("threeCanvas"), antialias: true, alpha: true });
        renderer.setSize(window.innerWidth * 0.8, 500);
        document.body.appendChild(renderer.domElement);

        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
        scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
        directionalLight.position.set(5, 5, 5);
        scene.add(directionalLight);

        // OrbitControls for mouse interaction
        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true; // Smooth movements
        controls.dampingFactor = 0.05;
        controls.rotateSpeed = 0.5;
        controls.zoomSpeed = 0.8;
        controls.target.set(0, 0, 0); // Focus on the center
        controls.update();

        // Load FBX Model
        const loader = new THREE.FBXLoader();
        loader.load('../models/Motor_Battery/Motorcycle_Battery.fbx', function (object) {
            object.scale.set(0.05, 0.05, 0.05); // Adjust size
            object.position.set(0, -1, 0);
            scene.add(object);

            // Animation loop
            function animate() {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
            animate();
        }, undefined, function (error) {
            console.error('Error loading FBX:', error);
        });

        // Handle window resize
        window.addEventListener("resize", () => {
            const width = window.innerWidth * 0.8;
            const height = 500;
            renderer.setSize(width, height);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
        });
    </script>

<?php include '../page/footer.php'; ?>


</body>
</html>
